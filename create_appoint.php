<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Appointment</title>
    <link rel="stylesheet" href="Style/header.css">
    <link rel="stylesheet" href="Style/addappoint.css">
    <link rel="stylesheet" href="Style/appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                if ($_GET['status'] == 'success') {
                    echo '<div class="status-message status-success">Appointment successfully created!</div>';
                } else if ($_GET['status'] == 'error') {
                    echo '<div class="status-message status-error">Error creating appointment. Please try again.</div>';
                }
            }
            ?>

            <form action="save_appoint.php" method="post" id="appointment-form">
                <div class="form-group">
                    <label for="program">Program:</label>
                    <select id="program" name="program" required>
                        <option value="">Select a program</option>
                        <option value="Medical Checkup">Medical Checkup</option>
                        <option value="Dental Cleaning">Dental Cleaning</option>
                        <option value="Physical Therapy">Physical Therapy</option>
                        <option value="Nutrition Consultation">Nutrition Consultation</option>
                        <option value="Mental Health Session">Mental Health Session</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" required>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Appointment Date and Time:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required>
                </div>

                <button type="submit" class="btn-submit">Create Appointment</button>
            </form>

            <a href="appointment.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </main>
</body>

</html>