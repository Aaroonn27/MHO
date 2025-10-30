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