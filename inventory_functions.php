<?php
// Database connection
function get_db_connection() {
    // Update these with your actual database credentials
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'mhodb'; // Replace with your actual database name

    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Function to display inventory items
function display_inventory() {
    $conn = get_db_connection();
    
    // Start with a base query
    $query = "SELECT * FROM inventory WHERE 1=1";
    $params = [];
    
    // Add filters if they exist in GET parameters
    if (!empty($_GET['name'])) {
        $query .= " AND name LIKE ?";
        $params[] = "%" . $_GET['name'] . "%";
    }
    
    if (!empty($_GET['type'])) {
        $query .= " AND type LIKE ?";
        $params[] = "%" . $_GET['type'] . "%";
    }
    
    if (!empty($_GET['serial'])) {
        $query .= " AND serial_no LIKE ?";
        $params[] = "%" . $_GET['serial'] . "%";
    }
    
    if (!empty($_GET['expiry'])) {
        $query .= " AND expiry_date = ?";
        $params[] = $_GET['expiry'];
    }
    
    if (!empty($_GET['quantity'])) {
        $query .= " AND quantity = ?";
        $params[] = $_GET['quantity'];
    }
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types = str_repeat("s", count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['serial_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['expiry_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
            echo "<td class='actions'>";
            echo "<a href='edit_inventory.php?id=" . $row['id'] . "' class='edit-btn'><i class='fas fa-edit'></i></a>";
            echo "<a href='delete_inventory.php?id=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this item?\")'><i class='fas fa-trash'></i></a>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No inventory items found</td></tr>";
    }
    
    $stmt->close();
    $conn->close();
}
?>
