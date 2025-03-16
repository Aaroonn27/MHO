<?php
// Include database connection
require_once 'inventory_functions.php';

// Function to save new inventory item
function save_inventory_item() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get database connection
        $conn = get_db_connection();
        
        // Get form data and sanitize inputs
        $name = $conn->real_escape_string($_POST['name']);
        $type = $conn->real_escape_string($_POST['type']);
        $serial_no = $conn->real_escape_string($_POST['serial_no']);
        $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
        $quantity = intval($_POST['quantity']);
        
        // Check if serial number already exists
        $check_stmt = $conn->prepare("SELECT id FROM inventory WHERE serial_no = ?");
        $check_stmt->bind_param("s", $serial_no);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Serial number already exists
            $check_stmt->close();
            $conn->close();
            return [
                'success' => false,
                'message' => 'An item with this Serial Number already exists'
            ];
        }
        $check_stmt->close();
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO inventory (name, type, serial_no, expiry_date, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $type, $serial_no, $expiry_date, $quantity);
        
        // Execute the statement
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return [
                'success' => true,
                'message' => 'Inventory item successfully added'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return [
                'success' => false,
                'message' => 'Error adding inventory item: ' . $error
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Invalid request method'
    ];
}

// Handle the inventory save request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = save_inventory_item();
    
    // Redirect based on result
    if ($result['success']) {
        header("Location: inventory.php?success=1&message=" . urlencode($result['message']));
    } else {
        header("Location: inventory.php?error=1&message=" . urlencode($result['message']));
    }
    exit();
}
?>