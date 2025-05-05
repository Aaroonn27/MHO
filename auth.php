<?php
session_start();
require_once 'db_conn.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate inputs (basic validation)
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required";
        header("Location: login.php");
        exit();
    }
    
    // Connect to database
    $conn = connect_db();
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Update last login time
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect based on role
            switch ($user['role']) {
                case 'cho_admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'cho_healthcare':
                    header("Location: healthcare/dashboard.php");
                    break;
                case 'abtc_admin':
                    header("Location: abtc/admin/dashboard.php");
                    break;
                case 'abtc_healthcare':
                    header("Location: abtc/healthcare/dashboard.php");
                    break;
                default:
                    header("Location: dashboard.php");
            }
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid username or password";
            header("Location: login.php");
            exit();
        }
    } else {
        // User does not exist
        $_SESSION['error'] = "Invalid username or password";
        header("Location: login.php");
        exit();
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If not submitted via POST, redirect to login page
    header("Location: login.php");
    exit();
}
?>