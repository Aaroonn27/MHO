<?php
// Include database connection
include_once 'db_conn.php';

// Function to save charge slip
function save_charge_slip() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
        $conn = connect_db();
        
        // Get form data and sanitize inputs
        $services = $conn->real_escape_string($_POST['services']);
        $fname = $conn->real_escape_string($_POST['fname']);
        $mname = $conn->real_escape_string($_POST['mname']);
        $lname = $conn->real_escape_string($_POST['lname']);
        
        // Calculate discount value (0 if none selected)
        $discount = 0;
        if (isset($_POST['discount'])) {
            switch ($_POST['discount']) {
                case 'senior':
                    $discount = 20; // 20% discount for senior citizens
                    break;
                case 'pwd':
                    $discount = 15; // 15% discount for PWD
                    break;
                case 'others':
                    $discount = 10; // 10% discount for others
                    break;
                default:
                    $discount = 0;
            }
        }
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO chargeslip (services, fname, mname, lname, discount, timeanddate) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssi", $services, $fname, $mname, $lname, $discount);
        
        // Execute the statement
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            // Redirect with success message and the ID for potential printing
            header("Location: charge_slip.php?success=1&id=$last_id");
            exit();
        } else {
            // Redirect with error message
            header("Location: charge_slip.php?error=1");
            exit();
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Function to get charge slip history
function get_charge_slip_history() {
    $conn = connect_db();
    
    // SQL query to fetch recent charge slips (limit to 10 most recent)
    $sql = "SELECT id, services, CONCAT(fname, ' ', mname, ' ', lname) AS full_name, 
            discount, timeanddate FROM chargeslip ORDER BY timeanddate DESC LIMIT 10";
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

// Display success/error messages
function display_status_messages() {
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Charge slip successfully created!</div>';
    }
    
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger">Error creating charge slip!</div>';
    }
}

// Get service options
function get_service_options() {
    // You could expand this to pull from a services table
    // For now, returning static options based on your image
    return [
        'Health Certificate',
        'Medical Certificate',
        'Other Certificate'
    ];
}
?>