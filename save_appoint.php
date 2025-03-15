<?php
// Database connection parameters
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

// Initialize variables with default empty values
$program = $name = $address = $contact = $appointment_date = "";
$error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $program = trim(htmlspecialchars($_POST['program']));
    $name = trim(htmlspecialchars($_POST['name']));
    $address = trim(htmlspecialchars($_POST['address']));
    $contact = trim(htmlspecialchars($_POST['contact']));
    $appointment_date = trim($_POST['appointment_date']);
    
    // Basic validation
    if (empty($program) || empty($name) || empty($address) || empty($contact) || empty($appointment_date)) {
        $error_message = "All fields are required";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO appointments (program, name, address, contact, appointment_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $program, $name, $address, $contact, $appointment_date);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to appointments page on success
            header("Location: appointment.php?status=success");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();

// If there was an error, display it
if (!empty($error_message)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <link rel="stylesheet" href="Style/appoint.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .error-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 20px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                text-align: center;
            }
            
            .error-message {
                color: #d9534f;
                margin-bottom: 20px;
            }
            
            .btn-back {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
            }
            
            .btn-back:hover {
                background-color: #0069d9;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h2>Error</h2>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
            <a href="create_appoint.php" class="btn-back">Back to Form</a>
        </div>
    </body>
    </html>
    <?php
}
?>