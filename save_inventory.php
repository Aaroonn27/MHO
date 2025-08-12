<?php
// save_inventory.php - Handle saving inventory batches

require_once 'inventory_functions.php';

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_inventory.php?error=1&message=Invalid request method');
    exit;
}

try {
    // Validate required fields
    $required_fields = ['vaccine_name', 'vaccine_type', 'batch_id', 'original_quantity', 'expiry_date', 'date_received'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Additional validations
    if (!empty($_POST['original_quantity']) && (int)$_POST['original_quantity'] <= 0) {
        $errors[] = 'Original quantity must be greater than 0';
    }
    
    if (!empty($_POST['expiry_date']) && !empty($_POST['date_received'])) {
        $expiry_date = new DateTime($_POST['expiry_date']);
        $received_date = new DateTime($_POST['date_received']);
        
        if ($expiry_date <= $received_date) {
            $errors[] = 'Expiry date must be after received date';
        }
    }
    
    // Check if batch ID already exists
    if (!empty($_POST['batch_id'])) {
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id FROM animal_bite_inventory WHERE batch_id = ?");
        $stmt->bind_param("s", $_POST['batch_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Batch ID already exists. Please use a different batch ID.';
        }
        
        $stmt->close();
        $conn->close();
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        header('Location: add_inventory.php?error=1&message=' . urlencode($error_message));
        exit;
    }
    
    // Prepare data for insertion
    $data = [
        'vaccine_name' => trim($_POST['vaccine_name']),
        'vaccine_type' => trim($_POST['vaccine_type']),
        'batch_id' => trim($_POST['batch_id']),
        'manufacturer' => !empty($_POST['manufacturer']) ? trim($_POST['manufacturer']) : null,
        'original_quantity' => (int)$_POST['original_quantity'],
        'expiry_date' => $_POST['expiry_date'],
        'storage_location' => !empty($_POST['storage_location']) ? trim($_POST['storage_location']) : null,
        'storage_temperature' => !empty($_POST['storage_temperature']) ? trim($_POST['storage_temperature']) : null,
        'date_received' => $_POST['date_received'],
        'supplier' => !empty($_POST['supplier']) ? trim($_POST['supplier']) : null,
        'unit_cost' => !empty($_POST['unit_cost']) ? (float)$_POST['unit_cost'] : null,
        'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
    ];
    
    // Add the batch to the database
    if (add_inventory_batch($data)) {
        // Log the addition
        $log_entry = [
            'action' => 'batch_added',
            'batch_id' => $data['batch_id'],
            'vaccine_name' => $data['vaccine_name'],
            'quantity' => $data['original_quantity'],
            'user' => 'System', // You can modify this to get actual user
            'timestamp' => date('Y-m-d H:i:s'),
            'notes' => "New batch added to inventory"
        ];
        
        log_inventory_action($log_entry);
        
        $success_message = "Vaccine batch '{$data['batch_id']}' has been successfully added to inventory with {$data['original_quantity']} vials.";
        header('Location: add_inventory.php?success=1&message=' . urlencode($success_message));
        exit;
    } else {
        throw new Exception('Failed to add batch to database');
    }
    
} catch (Exception $e) {
    error_log("Error adding inventory batch: " . $e->getMessage());
    $error_message = "An error occurred while adding the batch. Please try again.";
    header('Location: add_inventory.php?error=1&message=' . urlencode($error_message));
    exit;
}

// Function to log inventory actions
function log_inventory_action($log_data) {
    try {
        $conn = get_db_connection();
        
        // Create inventory log table if it doesn't exist
        $create_log_table = "
            CREATE TABLE IF NOT EXISTS inventory_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                action VARCHAR(50) NOT NULL,
                batch_id VARCHAR(50),
                vaccine_name VARCHAR(255),
                quantity INT,
                user VARCHAR(255),
                timestamp DATETIME,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $conn->query($create_log_table);
        
        // Insert log entry
        $stmt = $conn->prepare("INSERT INTO inventory_log (action, batch_id, vaccine_name, quantity, user, timestamp, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisss", 
            $log_data['action'],
            $log_data['batch_id'],
            $log_data['vaccine_name'],
            $log_data['quantity'],
            $log_data['user'],
            $log_data['timestamp'],
            $log_data['notes']
        );
        
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Error logging inventory action: " . $e->getMessage());
    }
}

// Enhanced add_inventory_batch function with better error handling
function add_inventory_batch_enhanced($data) {
    $conn = get_db_connection();
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("INSERT INTO animal_bite_inventory 
            (vaccine_name, vaccine_type, batch_id, manufacturer, original_quantity, current_quantity, 
             expiry_date, storage_location, storage_temperature, date_received, supplier, unit_cost, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        
        $stmt->bind_param("ssssiissssods", 
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
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert batch: " . $stmt->error);
        }
        
        $batch_inserted_id = $conn->insert_id;
        
        $conn->commit();
        $stmt->close();
        $conn->close();
        
        return $batch_inserted_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        $stmt->close();
        $conn->close();
        throw $e;
    }
}
?>