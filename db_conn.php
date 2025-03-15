<?php
// Database connection
$servername = "localhost";
$username = "";
$password = "";
$dbname = "mhodb";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
?>