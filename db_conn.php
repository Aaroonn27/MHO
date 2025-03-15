<?php
// Database connection variables
$servername = "localhost";
$username = "";
$password = "";
$dbname = "mhodb";

// Create connection function
function connect_db() {
    global $servername, $username, $password, $dbname;
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Function to fetch and display appointments
function fetch_appointments() {
    $conn = connect_db();
    
    // Get sort parameter
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
    
    // Determine sort order
    switch ($sort) {
        case 'date_asc':
            $order_by = "appointment_date ASC";
            break;
        case 'date_desc':
            $order_by = "appointment_date DESC";
            break;
        case 'name_asc':
            $order_by = "name ASC";
            break;
        case 'name_desc':
            $order_by = "name DESC";
            break;
        default:
            $order_by = "appointment_date DESC";
    }
    
    // SQL query to fetch appointments
    $sql = "SELECT id, program, name, address, contact, appointment_date FROM appointments ORDER BY $order_by";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo '<div class="program-card">';
            echo '<div class="program-title">Program: ' . htmlspecialchars($row["program"]) . '</div>';
            echo '<div class="program-info">';
            echo '<div class="info-row"><span>Name:</span> ' . htmlspecialchars($row["name"]) . '</div>';
            echo '<div class="info-row"><span>Address:</span> ' . htmlspecialchars($row["address"]) . '</div>';
            echo '<div class="info-row"><span>Contact:</span> ' . htmlspecialchars($row["contact"]) . '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-appointments">No appointments found</div>';
    }
    
    $conn->close();
}

// Function to save a new appointment
function save_appointment() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = connect_db();
        
        // Get form data and sanitize inputs
        $program = $conn->real_escape_string($_POST['program']);
        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO appointments (program, name, address, contact, appointment_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $program, $name, $address, $contact, $appointment_date);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back to appointment list with success message
            header("Location: appointment.php?success=1");
            exit();
        } else {
            // Redirect back to appointment list with error message
            header("Location: appointment.php?error=1");
            exit();
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Function to get a single appointment by ID
function get_appointment($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("SELECT id, program, name, address, contact, appointment_date FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
    } else {
        $appointment = null;
    }
    
    $stmt->close();
    $conn->close();
    
    return $appointment;
}

// Function to update an existing appointment
function update_appointment() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $conn = connect_db();
        
        $id = $conn->real_escape_string($_POST['id']);
        $program = $conn->real_escape_string($_POST['program']);
        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
        
        $stmt = $conn->prepare("UPDATE appointments SET program = ?, name = ?, address = ?, contact = ?, appointment_date = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $program, $name, $address, $contact, $appointment_date, $id);
        
        if ($stmt->execute()) {
            header("Location: appointment.php?updated=1");
            exit();
        } else {
            header("Location: appointment.php?error=2");
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Function to delete an appointment
function delete_appointment($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: appointment.php?deleted=1");
        exit();
    } else {
        header("Location: appointment.php?error=3");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}

// Display success/error messages
function display_messages() {
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Appointment successfully created!</div>';
    }
    
    if (isset($_GET['updated'])) {
        echo '<div class="alert alert-success">Appointment successfully updated!</div>';
    }
    
    if (isset($_GET['deleted'])) {
        echo '<div class="alert alert-success">Appointment successfully deleted!</div>';
    }
    
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 1:
                echo '<div class="alert alert-danger">Error creating appointment!</div>';
                break;
            case 2:
                echo '<div class="alert alert-danger">Error updating appointment!</div>';
                break;
            case 3:
                echo '<div class="alert alert-danger">Error deleting appointment!</div>';
                break;
            default:
                echo '<div class="alert alert-danger">An unknown error occurred!</div>';
        }
    }
}
?>