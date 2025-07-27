<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Appointment</title>
    <link rel="stylesheet" href="Style/headerstyles.css">
    <link rel="stylesheet" href="Style/addappoint.css">
    <link rel="stylesheet" href="Style/appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .phone-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .sms-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .sms-preview h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .sms-preview .message {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="appointment-header">
            <h1>CREATE APPOINTMENT</h1>
        </div>

        <div class="form-container">
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
                    <label for="program">Program:</label>
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
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required onchange="updateSMSPreview()">
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" required placeholder="09123456789 or +639123456789">
                    <div class="phone-info">
                        <i class="fas fa-info-circle"></i> Enter Philippine mobile number (e.g., 09123456789). SMS confirmation will be sent automatically.
                    </div>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Appointment Date and Time:</label>
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