<?php

function get_db_connection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'mhodb'; 

    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Get inventory statistics
function get_inventory_stats() {
    $conn = get_db_connection();
    
    $stats = [];
    
    // Total batches
    $result = $conn->query("SELECT COUNT(*) as total FROM animal_bite_inventory WHERE status = 'active'");
    $stats['total_batches'] = $result->fetch_assoc()['total'];
    
    // Total available vials
    $result = $conn->query("SELECT SUM(current_quantity) as total FROM animal_bite_inventory WHERE status = 'active'");
    $stats['total_vials'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Expiring soon (within 90 days)
    $result = $conn->query("SELECT COUNT(*) as total FROM animal_bite_inventory WHERE status = 'active' AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)");
    $stats['expiring_soon'] = $result->fetch_assoc()['total'];
    
    // Low stock (less than or equal to 10 vials)
    $result = $conn->query("SELECT COUNT(*) as total FROM animal_bite_inventory WHERE status = 'active' AND current_quantity <= 10");
    $stats['low_stock'] = $result->fetch_assoc()['total'];
    
    $conn->close();
    return $stats;
}

// Get all inventory items with expiration warnings
function get_inventory_items($filters = []) {
    $conn = get_db_connection();
    
    $query = "SELECT *, 
              DATEDIFF(expiry_date, CURDATE()) as days_until_expiry,
              CASE 
                  WHEN current_quantity <= 5 THEN 'critical'
                  WHEN current_quantity <= 10 THEN 'low'
                  ELSE 'normal'
              END as stock_level,
              CASE 
                  WHEN expiry_date <= CURDATE() THEN 'expired'
                  WHEN DATEDIFF(expiry_date, CURDATE()) <= 30 THEN 'near_expiry'
                  WHEN DATEDIFF(expiry_date, CURDATE()) <= 90 THEN 'warning'
                  ELSE 'normal'
              END as expiry_status
              FROM animal_bite_inventory 
              WHERE status = 'active'";
    
    $params = [];
    $types = '';
    
    // Add filters
    if (!empty($filters['vaccine_name'])) {
        $query .= " AND vaccine_name LIKE ?";
        $params[] = "%" . $filters['vaccine_name'] . "%";
        $types .= 's';
    }
    
    if (!empty($filters['vaccine_type'])) {
        $query .= " AND vaccine_type = ?";
        $params[] = $filters['vaccine_type'];
        $types .= 's';
    }
    
    if (!empty($filters['batch_id'])) {
        $query .= " AND batch_id LIKE ?";
        $params[] = "%" . $filters['batch_id'] . "%";
        $types .= 's';
    }
    
    if (!empty($filters['expiry_status'])) {
        if ($filters['expiry_status'] === 'expiring_soon') {
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
        }
    }
    
    if (!empty($filters['stock_level'])) {
        if ($filters['stock_level'] === 'low') {
            $query .= " AND current_quantity <= 10";
        } elseif ($filters['stock_level'] === 'critical') {
            $query .= " AND current_quantity <= 5";
        }
    }
    
    $query .= " ORDER BY expiry_date ASC, current_quantity ASC";
    
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
    
    $stmt = $conn->prepare("SELECT *, DATEDIFF(expiry_date, CURDATE()) as days_until_expiry FROM animal_bite_inventory WHERE id = ?");
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
        $stmt = $conn->prepare("SELECT current_quantity FROM animal_bite_inventory WHERE batch_id = ?");
        $stmt->bind_param("s", $batch_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc();
        
        if (!$current || $current['current_quantity'] < $quantity) {
            throw new Exception("Insufficient vials in batch");
        }
        
        // Update inventory
        $stmt = $conn->prepare("UPDATE animal_bite_inventory SET current_quantity = current_quantity - ?, used_quantity = used_quantity + ? WHERE batch_id = ?");
        $stmt->bind_param("iis", $quantity, $quantity, $batch_id);
        $stmt->execute();
        
        // Record usage
        $stmt = $conn->prepare("INSERT INTO vial_usage (batch_id, patient_id, used_by, quantity_used, purpose, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $batch_id, $patient_id, $used_by, $quantity, $purpose, $notes);
        $stmt->execute();
        
        $conn->commit();
        $stmt->close();
        $conn->close();
        
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return false;
    }
}

// Add new batch
function add_inventory_batch($data) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("INSERT INTO animal_bite_inventory 
        (vaccine_name, vaccine_type, batch_id, manufacturer, original_quantity, current_quantity, 
         expiry_date, storage_location, storage_temperature, date_received, supplier, unit_cost, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssiisssssos", 
        $data['vaccine_name'], 
        $data['vaccine_type'], 
        $data['batch_id'],
        $data['manufacturer'],
        $data['original_quantity'],
        $data['original_quantity'], // current_quantity starts same as original
        $data['expiry_date'],
        $data['storage_location'],
        $data['storage_temperature'],
        $data['date_received'],
        $data['supplier'],
        $data['unit_cost'],
        $data['notes']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Update inventory batch
function update_inventory_batch($id, $data) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("UPDATE animal_bite_inventory SET 
        vaccine_name = ?, vaccine_type = ?, batch_id = ?, manufacturer = ?, 
        current_quantity = ?, expiry_date = ?, storage_location = ?, 
        storage_temperature = ?, supplier = ?, unit_cost = ?, notes = ?
        WHERE id = ?");
    
    $stmt->bind_param("ssssississsi", 
        $data['vaccine_name'], 
        $data['vaccine_type'], 
        $data['batch_id'],
        $data['manufacturer'],
        $data['current_quantity'],
        $data['expiry_date'],
        $data['storage_location'],
        $data['storage_temperature'],
        $data['supplier'],
        $data['unit_cost'],
        $data['notes'],
        $id
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Delete inventory batch
function delete_inventory_batch($id) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("UPDATE animal_bite_inventory SET status = 'depleted' WHERE id = ?");
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

// Display inventory cards for the new interface
function display_inventory_cards() {
    $items = get_inventory_items();
    $vaccine_types = get_vaccine_types();
    
    foreach ($items as $item) {
        $stock_class = '';
        $stock_icon = 'fas fa-check-circle';
        $stock_color = '#00b894';
        $warning_html = '';
        
        // Determine stock level styling
        if ($item['stock_level'] === 'critical') {
            $stock_class = 'critical-stock';
            $stock_icon = 'fas fa-exclamation-triangle';
            $stock_color = '#c0392b';
            $warning_html = '<div class="expiry-warning warning-critical">
                <i class="fas fa-exclamation-triangle"></i>
                CRITICAL: Very low stock - Immediate reorder required!
            </div>';
        } elseif ($item['stock_level'] === 'low') {
            $stock_class = 'low-stock';
            $stock_icon = 'fas fa-exclamation-triangle';
            $stock_color = '#e67e22';
            $warning_html = '<div class="expiry-warning warning-near">
                <i class="fas fa-exclamation-triangle"></i>
                Low stock alert - Consider reordering soon
            </div>';
        }
        
        // Determine expiry styling
        $expiry_class = '';
        $expiry_color = '#00b894';
        if ($item['expiry_status'] === 'expired') {
            $expiry_class = 'expired';
            $expiry_color = '#991b1b';
        } elseif ($item['expiry_status'] === 'near_expiry') {
            $expiry_class = 'near-expiry';
            $expiry_color = '#d68910';
        }
        
        $vaccine_full_name = $vaccine_types[$item['vaccine_type']] ?? $item['vaccine_type'];
        
        echo '
        <div class="inventory-card">
            <div class="card-header">
                <div class="item-number">
                    <i class="fas fa-vial"></i>
                    ' . htmlspecialchars($item['vaccine_name']) . ' (' . htmlspecialchars($item['vaccine_type']) . ')
                    <span class="batch-id">Batch: ' . htmlspecialchars($item['batch_id']) . '</span>
                </div>
                <div class="batch-info">' . htmlspecialchars($vaccine_full_name) . '</div>
            </div>

            <div class="stock-section">
                <div class="stock-label">
                    <i class="fas fa-box"></i>
                    Available Vials
                </div>
                <div class="stock-value ' . $stock_class . '">
                    <span>' . $item['current_quantity'] . ' vials</span>
                    <i class="' . $stock_icon . '" style="color: ' . $stock_color . ';"></i>
                </div>
            </div>

            <div class="stock-section">
                <div class="stock-label">
                    <i class="fas fa-minus-circle"></i>
                    Used Vials
                </div>
                <div class="stock-value">
                    <span>' . $item['used_quantity'] . ' vials</span>
                    <small style="color: #666;">from original ' . $item['original_quantity'] . '</small>
                </div>
            </div>

            <div class="expiry-info">
                <div class="stock-label">
                    <i class="fas fa-calendar-alt"></i>
                    Expiry Date
                </div>
                <div class="expiry-date ' . $expiry_class . '">
                    <span>' . date('F j, Y', strtotime($item['expiry_date'])) . '</span>
                    <span style="font-size: 0.8rem; color: ' . $expiry_color . ';">' . $item['days_until_expiry'] . ' days left</span>
                </div>
                ' . $warning_html . '
            </div>

            <div class="card-actions">
                <button class="action-btn edit-btn" onclick="editBatch(' . $item['id'] . ')">
                    <i class="fas fa-edit"></i>
                    Edit
                </button>
                <button class="action-btn use-btn" onclick="useVial(\'' . $item['batch_id'] . '\')">
                    <i class="fas fa-syringe"></i>
                    Use Vial
                </button>
            </div>
        </div>';
    }
}

// API endpoint for AJAX requests
function handle_ajax_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'use_vial':
            $batch_id = $_POST['batch_id'] ?? '';
            $used_by = $_POST['used_by'] ?? 'System';
            $patient_id = $_POST['patient_id'] ?? null;
            $purpose = $_POST['purpose'] ?? 'Post-exposure prophylaxis';
            
            $result = use_vial($batch_id, 1, $used_by, $patient_id, $purpose);
            echo json_encode(['success' => $result]);
            break;
            
        case 'get_stats':
            $stats = get_inventory_stats();
            echo json_encode($stats);
            break;
            
        case 'get_items':
            $filters = $_POST['filters'] ?? [];
            $items = get_inventory_items($filters);
            echo json_encode($items);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

// Check if this is an AJAX request
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    handle_ajax_request();
    exit;
}
?>