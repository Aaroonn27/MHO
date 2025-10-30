<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);

// Include database connection
include 'db_conn.php';

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
        // Validate and format phone number (Philippine mobile numbers)
        $original_contact = $contact;
        $contact = preg_replace('/\D/', '', $contact); // Remove non-digits
        
        // Add +63 prefix if it starts with 09
        if (substr($contact, 0, 2) == '09') {
            $contact = '+63' . substr($contact, 1);
        }
        // Add +63 prefix if it starts with 9 and has 10 digits
        elseif (substr($contact, 0, 1) == '9' && strlen($contact) == 10) {
            $contact = '+63' . $contact;
        }
        // If it doesn't start with +63, add it (for 11-digit numbers starting with 09)
        elseif (substr($contact, 0, 3) != '+63' && strlen($contact) == 11) {
            $contact = '+63' . substr($contact, 1);
        }
        
        // Validate appointment date is not in the past
        $appointment_datetime = new DateTime($appointment_date);
        $current_datetime = new DateTime();
        
        if ($appointment_datetime <= $current_datetime) {
            $error_message = "Appointment date must be in the future";
        } else {
            $conn = connect_db();
            
            // Prepare SQL statement - SMS status set to 'pending' by default
            $stmt = $conn->prepare("INSERT INTO appointments (program, name, address, contact, appointment_date, sms_status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("sssss", $program, $name, $address, $contact, $appointment_date);
            
            // Execute the statement
            if ($stmt->execute()) {
                $appointment_id = $conn->insert_id;
                $stmt->close();
                $conn->close();
                
                // Redirect with success message - NO SMS SENT HERE
                header("Location: create_appoint.php?status=success&message=Appointment created successfully! SMS will be sent automatically before the appointment date.");
                exit();
            } else {
                $error_message = "Database Error: " . $stmt->error;
                $stmt->close();
                $conn->close();
            }
        }
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
        <title>Error - City Health Office</title>
        <link rel="stylesheet" href="Style/appoint.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .error-container {
                max-width: 600px;
                padding: 40px;
                background-color: #fff;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
            }
            
            .error-icon {
                font-size: 64px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            
            .error-container h2 {
                color: #343a40;
                margin-bottom: 20px;
                font-size: 2em;
            }
            
            .error-message {
                color: #dc3545;
                margin-bottom: 30px;
                padding: 15px;
                background: #f8d7da;
                border: 1px solid #f1aeb5;
                border-radius: 8px;
                font-size: 16px;
            }
            
            .btn-back {
                display: inline-block;
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                margin: 10px;
            }
            
            .btn-back:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
                text-decoration: none;
                color: white;
            }
            
            .btn-appointments {
                background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            }
            
            .btn-appointments:hover {
                box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Oops! Something went wrong</h2>
            <div class="error-message">
                <i class="fas fa-info-circle"></i> <?php echo $error_message; ?>
            </div>
            <div>
                <a href="create_appoint.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
                <a href="appointment.php" class="btn-back btn-appointments">
                    <i class="fas fa-calendar-alt"></i> View Appointments
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>