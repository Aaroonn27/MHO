<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Appointment - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles */
        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.15);
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-container h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
            align-items: center;
        }

        nav ul li a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        nav ul li a i {
            font-size: 22px;
            margin-bottom: 6px;
        }

        nav ul li a span {
            font-size: 13px;
            font-weight: 600;
        }

        /* Main Content */
        main {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-title p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-header h2 {
            font-size: 1.8rem;
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 1rem;
        }

        /* Status Messages */
        .status-message {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .status-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        
        .status-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .phone-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* SMS Preview */
        .sms-preview {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            border: 2px solid #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .sms-preview h4 {
            color: #495057;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sms-preview .message {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 15px;
            border-radius: 8px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            border-left: 4px solid #2196f3;
            color: #1565c0;
            line-height: 1.5;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 0;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            main {
                padding: 30px 20px;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container h1 {
                font-size: 1.6rem;
                text-align: center;
            }

            .form-container {
                padding: 25px;
                margin: 0 15px;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            nav ul {
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }

            nav ul li a {
                padding: 8px 12px;
            }

            nav ul li a i {
                font-size: 18px;
            }

            nav ul li a span {
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 20px;
            }

            .btn-submit {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <?php echo generate_navigation(); ?>
        </nav>
    </header>

    <main>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-calendar-plus"></i> Create New Appointment</h1>
                <p>Schedule a new patient appointment with SMS confirmation</p>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>Appointment Details</h2>
                <p>Fill in the information below to schedule a new appointment</p>
            </div>

            <?php
            // Display status message if available
            if (isset($_GET['status'])) {
                $message = $_GET['message'] ?? '';
                if ($_GET['status'] == 'success') {
                    echo '<div class="status-message status-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '</div>';
                } else if ($_GET['status'] == 'error') {
                    echo '<div class="status-message status-error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($message) . '</div>';
                } else if ($_GET['status'] == 'warning') {
                    echo '<div class="status-message status-warning"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($message) . '</div>';
                }
            }
            ?>

            <form action="save_appoint.php" method="post" id="appointment-form">
                <div class="form-group">
                    <label for="program"><i class="fas fa-stethoscope"></i> Program:</label>
                    <select id="program" name="program" required onchange="updateSMSPreview()">
                        <option value="">Select a program</option>
                        <option value="Medical Checkup">Medical Checkup</option>
                        <option value="Dental Cleaning">Dental Cleaning</option>
                        <option value="Physical Therapy">Physical Therapy</option>
                        <option value="Nutrition Consultation">Nutrition Consultation</option>
                        <option value="Mental Health Session">Mental Health Session</option>
                        <option value="COVID-19 Vaccination">COVID-19 Vaccination</option>
                        <option value="Blood Pressure Monitoring">Blood Pressure Monitoring</option>
                        <option value="Diabetes Consultation">Diabetes Consultation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Patient Name:</label>
                    <input type="text" id="name" name="name" required onchange="updateSMSPreview()" placeholder="Enter full name">
                </div>

                <div class="form-group">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address:</label>
                    <textarea id="address" name="address" required placeholder="Enter complete address"></textarea>
                </div>

                <div class="form-group">
                    <label for="contact"><i class="fas fa-phone"></i> Contact Number:</label>
                    <input type="text" id="contact" name="contact" required placeholder="09123456789">
                    <div class="phone-info">
                        <i class="fas fa-info-circle"></i> Enter Philippine mobile number (e.g., 09123456789). SMS confirmation will be sent automatically.
                    </div>
                </div>

                <div class="form-group">
                    <label for="appointment_date"><i class="fas fa-calendar-alt"></i> Appointment Date and Time:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required onchange="updateSMSPreview()">
                </div>

                <!-- SMS Preview -->
                <div class="sms-preview" id="smsPreview" style="display: none;">
                    <h4><i class="fas fa-sms"></i> SMS Confirmation Preview:</h4>
                    <div class="message" id="smsMessage">
                        Select program, enter name, and choose date to see SMS preview...
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-calendar-plus"></i> Create Appointment & Send SMS
                </button>
            </form>

            <a href="appointment.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </main>

    <script>
        // Set minimum date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('appointment_date');
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            // Format date for datetime-local input
            const year = tomorrow.getFullYear();
            const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
            const day = String(tomorrow.getDate()).padStart(2, '0');
            const hours = '09'; // Default to 9 AM
            const minutes = '00';
            
            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            dateInput.min = minDateTime;
            
            // Validate form before submission
            document.getElementById('appointment-form').addEventListener('submit', function(e) {
                const contact = document.getElementById('contact').value.trim();
                const appointmentDate = new Date(document.getElementById('appointment_date').value);
                const now = new Date();
                
                // Validate phone number
                if (!isValidPhoneNumber(contact)) {
                    e.preventDefault();
                    alert('Please enter a valid Philippine mobile number (e.g., 09123456789)');
                    return;
                }
                
                // Validate appointment date
                if (appointmentDate <= now) {
                    e.preventDefault();
                    alert('Appointment date must be in the future');
                    return;
                }
            });
        });

        function isValidPhoneNumber(phone) {
            // Remove all non-digits
            const digits = phone.replace(/\D/g, '');
            
            // Check if it's a valid Philippine mobile number
            if (digits.length === 11 && digits.startsWith('09')) {
                return true;
            }
            if (digits.length === 12 && digits.startsWith('639')) {
                return true;
            }
            if (digits.length === 10 && digits.startsWith('9')) {
                return true;
            }
            
            return false;
        }

        function updateSMSPreview() {
            const name = document.getElementById('name').value;
            const program = document.getElementById('program').value;
            const appointmentDate = document.getElementById('appointment_date').value;
            
            if (name && program && appointmentDate) {
                const date = new Date(appointmentDate);
                const formattedDate = date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const formattedTime = date.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                const message = `Dear ${name}, your appointment for ${program} is confirmed on ${formattedDate} at ${formattedTime}. Please arrive 15 minutes early. - City Health Office of San Pablo`;
                
                document.getElementById('smsMessage').textContent = message;
                document.getElementById('smsPreview').style.display = 'block';
            } else {
                document.getElementById('smsPreview').style.display = 'none';
            }
        }
    </script>
</body>

</html>