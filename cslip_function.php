<?php
// Include database connection
include_once 'db_conn.php';

// Function to save charge slip
function save_charge_slip() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
        $conn = connect_db();
        
        // Get form data and sanitize inputs
        $fname = $conn->real_escape_string(trim($_POST['fname']));
        $mname = $conn->real_escape_string(trim($_POST['mname'] ?? ''));
        $lname = $conn->real_escape_string(trim($_POST['lname']));
        $quantity = intval($_POST['quantity'] ?? 1);
        
        // Handle "Others" service option
        if (isset($_POST['services']) && empty($_POST['services']) && !empty($_POST['others_input'])) {
            // If "Others" is selected, get the custom service name
            $services = $conn->real_escape_string(trim($_POST['others_input']));
        } else {
            $services = $conn->real_escape_string(trim($_POST['services']));
        }
        
        // Validate required fields
        if (empty($fname) || empty($lname) || empty($services)) {
            $_SESSION['error_message'] = "Please fill in all required fields!";
            $conn->close();
            header("Location: charge_slip.php");
            exit();
        }
        
        // Get service price
        $amount = get_service_price($services);
        
        // Calculate total
        $total = $amount * $quantity;
        
        // Set discount to 0 (no longer used)
        $discount = 0;
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO chargeslip (services, fname, mname, lname, discount, quantity, amount, total, timeanddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if (!$stmt) {
            $_SESSION['error_message'] = "Database error: " . $conn->error;
            $conn->close();
            header("Location: charge_slip.php");
            exit();
        }
        
        $stmt->bind_param("ssssiidd", $services, $fname, $mname, $lname, $discount, $quantity, $amount, $total);
        
        // Execute the statement
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $_SESSION['success_message'] = "Charge slip successfully created!";
            $stmt->close();
            $conn->close();
            // Redirect with success message and the ID for printing
            header("Location: charge_slip.php?id=$last_id");
            exit();
        } else {
            $_SESSION['error_message'] = "Error creating charge slip: " . $stmt->error;
            $stmt->close();
            $conn->close();
            header("Location: charge_slip.php");
            exit();
        }
    }
}

// Function to get charge slip history
function get_charge_slip_history() {
    $conn = connect_db();
    
    // SQL query to fetch recent charge slips (limit to 10 most recent)
    $sql = "SELECT id, services, CONCAT(fname, ' ', mname, ' ', lname) AS full_name, 
            quantity, amount, total, timeanddate FROM chargeslip ORDER BY timeanddate DESC LIMIT 10";
    $result = $conn->query($sql);
    
    $history = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    
    $conn->close();
    return $history;
}

// Function to get a specific charge slip by ID
function get_charge_slip($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("SELECT * FROM chargeslip WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $charge_slip = $result->fetch_assoc();
    } else {
        $charge_slip = null;
    }
    
    $stmt->close();
    $conn->close();
    
    return $charge_slip;
}

// Function to get service price
function get_service_price($service) {
    // Define prices for each service
    $prices = [
        'Health Certificate for Workers' => 150.00,
        'Medical Certificate for Employment' => 200.00,
        'Tricycle Driver Medical Certificate' => 180.00,
        'Medical Certificate for Leave' => 150.00,
        'PWD Medical Certificate' => 100.00,
        'Others' => 0.00 // Will be set by user input
    ];
    
    // Return the price or a default value if service not found
    return isset($prices[$service]) ? $prices[$service] : 0.00;
}

?>