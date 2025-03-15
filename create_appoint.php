<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Appointment</title>
    <link rel="stylesheet" href="Style/appoint.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background-color: #0069d9;
        }

        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <h1>LOGO</h1>
        </div>
        <nav>
            <ul>
                <li>
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="dropdown active">
                    <a href="#">
                        <i class="fas fa-calendar"></i>
                        <span>Appointment</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="appointment.php"><i class="fas fa-list"></i>View Appointments</a>
                        <a href="create_appoint.php"><i class="fas fa-plus"></i>Create Appointment</a>
                    </div>
                </li>
                <li>
                    <a href="announcement.php">
                        <i class="fas fa-info-circle"></i>
                        <span>Announcement</span>
                    </a>
                </li>
                <li>
                    <a href="message.php">
                        <i class="fas fa-envelope"></i>
                        <span>Message</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="appointment-header">
            <h1>CREATE APPOINTMENT</h1>
        </div>

        <div class="form-container">
            <form action="save_appointment.php" method="post">
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