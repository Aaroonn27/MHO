<?php

function get_db_connection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'mhodb'; 

    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    
    return $conn;
}

// Get inventory statistics
function get_inventory_stats() {
    $conn = get_db_connection();
    
    $stats = [
        'total_batches' => 0,
        'total_vials' => 0,
        'expiring_soon' => 0,
        'low_stock' => 0
    ];
    
    // Total batches
    $result = $conn->query("SELECT COUNT(*) as total FROM inventory WHERE quantity > 0");
    if ($result) {
        $stats['total_batches'] = $result->fetch_assoc()['total'];
    }
    
    // Total available vials
    $result = $conn->query("SELECT SUM(quantity) as total FROM inventory WHERE quantity > 0");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_vials'] = $row['total'] ?? 0;
    }
    
    // Expiring soon (within 90 days)
    $result = $conn->query("SELECT COUNT(*) as total FROM inventory 
        WHERE quantity > 0 
        AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)");
    if ($result) {
        $stats['expiring_soon'] = $result->fetch_assoc()['total'];
    }
    
    // Low stock (less than or equal to 10 vials)
    $result = $conn->query("SELECT COUNT(*) as total FROM inventory WHERE quantity > 0 AND quantity <= 10");
    if ($result) {
        $stats['low_stock'] = $result->fetch_assoc()['total'];
    }
    
    $conn->close();
    return $stats;
}

// Get all inventory items with expiration warnings
function get_inventory_items($filters = []) {
    $conn = get_db_connection();
    
    $query = "SELECT 
              id,
              name as vaccine_name,
              type as vaccine_type,
              serial_no as batch_id,
              quantity as current_quantity,
              0 as used_quantity,
              quantity as original_quantity,
              expiry_date,
              DATEDIFF(expiry_date, CURDATE()) as days_until_expiry,
              CASE 
                  WHEN quantity <= 5 THEN 'critical'
                  WHEN quantity <= 10 THEN 'low'
                  ELSE 'normal'
              END as stock_level,
              CASE 
                  WHEN expiry_date <= CURDATE() THEN 'expired'
                  WHEN DATEDIFF(expiry_date, CURDATE()) <= 30 THEN 'near_expiry'
                  WHEN DATEDIFF(expiry_date, CURDATE()) <= 90 THEN 'warning'
                  ELSE 'good'
              END as expiry_status
              FROM inventory 
              WHERE quantity > 0";
    
    $params = [];
    $types = '';
    
    // Parse filters if they come as JSON string
    if (is_string($filters) && !empty($filters)) {
        $filters = json_decode($filters, true) ?? [];
    }
    
    // Add filters
    if (!empty($filters['vaccine_name'])) {
        $query .= " AND name LIKE ?";
        $params[] = "%" . $filters['vaccine_name'] . "%";
        $types .= 's';
    }
    
    if (!empty($filters['vaccine_type'])) {
        $query .= " AND type = ?";
        $params[] = $filters['vaccine_type'];
        $types .= 's';
    }
    
    if (!empty($filters['batch_id'])) {
        $query .= " AND serial_no LIKE ?";
        $params[] = "%" . $filters['batch_id'] . "%";
        $types .= 's';
    }
    
    if (!empty($filters['expiry_status'])) {
        if ($filters['expiry_status'] === 'expiring_soon') {
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
        } elseif ($filters['expiry_status'] === 'expired') {
            $query .= " AND expiry_date < CURDATE()";
        }
    }
    
    if (!empty($filters['stock_level'])) {
        if ($filters['stock_level'] === 'low') {
            $query .= " AND quantity <= 10 AND quantity > 5";
        } elseif ($filters['stock_level'] === 'critical') {
            $query .= " AND quantity <= 5";
        }
    }
    
    $query .= " ORDER BY expiry_date ASC, quantity ASC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $items;
}

// Get single inventory item by ID
function get_inventory_item($id) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("SELECT 
        id,
        name as vaccine_name,
        type as vaccine_type,
        serial_no as batch_id,
        quantity as current_quantity,
        expiry_date,
        DATEDIFF(expiry_date, CURDATE()) as days_until_expiry 
        FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $item = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $item;
}

// Use vial(s) from a batch
function use_vial($batch_id, $quantity = 1, $used_by, $patient_id = null, $purpose = null, $notes = null) {
    $conn = get_db_connection();
    
    try {
        $conn->begin_transaction();
        
        // Check current quantity
        $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE serial_no = ?");
        $stmt->bind_param("s", $batch_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc();
        
        if (!$current || $current['quantity'] < $quantity) {
            throw new Exception("Insufficient vials in batch");
        }
        
        // Update inventory
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE serial_no = ?");
        $stmt->bind_param("is", $quantity, $batch_id);
        $stmt->execute();
        
        // Record usage
        $stmt = $conn->prepare("INSERT INTO vial_usage (batch_id, patient_id, used_by, quantity_used, purpose, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $batch_id, $patient_id, $used_by, $quantity, $purpose, $notes);
        $stmt->execute();
        
        $conn->commit();
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Vial used successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Add new batch
function add_inventory_batch($data) {
    $conn = get_db_connection();
    
    try {
        $stmt = $conn->prepare("INSERT INTO inventory 
            (name, type, serial_no, expiry_date, quantity) 
            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssi", 
            $data['vaccine_name'], 
            $data['vaccine_type'], 
            $data['batch_id'],
            $data['expiry_date'],
            $data['original_quantity']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        $conn->close();
        error_log("Error adding inventory: " . $e->getMessage());
        return false;
    }
}

// Update inventory batch
function update_inventory_batch($id, $data) {
    $conn = get_db_connection();
    
    try {
        $stmt = $conn->prepare("UPDATE inventory SET 
            name = ?, 
            type = ?, 
            serial_no = ?, 
            expiry_date = ?, 
            quantity = ?
            WHERE id = ?");
        
        $stmt->bind_param("ssssii", 
            $data['vaccine_name'], 
            $data['vaccine_type'], 
            $data['batch_id'],
            $data['expiry_date'],
            $data['current_quantity'],
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        $conn->close();
        error_log("Error updating inventory: " . $e->getMessage());
        return false;
    }
}

// Delete inventory batch (set quantity to 0)
function delete_inventory_batch($id) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("UPDATE inventory SET quantity = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Get usage history for a batch
function get_batch_usage_history($batch_id) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("SELECT * FROM vial_usage WHERE batch_id = ? ORDER BY usage_date DESC");
    $stmt->bind_param("s", $batch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $usage = [];
    while ($row = $result->fetch_assoc()) {
        $usage[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $usage;
}

// Get vaccine types for dropdown
function get_vaccine_types() {
    return [
        'HDCV' => 'Human Diploid Cell Vaccine',
        'HRIG' => 'Human Rabies Immunoglobulin', 
        'PCECV' => 'Purified Chick Embryo Cell Vaccine',
        'TT' => 'Tetanus Toxoid',
        'RIG' => 'Rabies Immunoglobulin',
        'ERIG' => 'Equine Rabies Immunoglobulin'
    ];
}

// API endpoint for AJAX requests
function handle_ajax_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'use_vial':
                $batch_id = $_POST['batch_id'] ?? '';
                $used_by = $_POST['used_by'] ?? '';
                $patient_id = !empty($_POST['patient_id']) ? $_POST['patient_id'] : null;
                $purpose = $_POST['purpose'] ?? 'Post-exposure prophylaxis';
                $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;
                
                if (empty($batch_id) || empty($used_by)) {
                    echo json_encode(['success' => false, 'message' => 'Batch ID and user name are required']);
                    return;
                }
                
                $result = use_vial($batch_id, 1, $used_by, $patient_id, $purpose, $notes);
                echo json_encode($result);
                break;
                
            case 'get_stats':
                $stats = get_inventory_stats();
                echo json_encode($stats);
                break;
                
            case 'get_items':
                $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
                $items = get_inventory_items($filters);
                echo json_encode($items);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
}

// Check if this is an AJAX request
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    handle_ajax_request();
    exit;
}
?>