<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Page</title>
    <link rel="stylesheet" href="Style/appoint.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="profilepage.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>

    </header>

    <main>
        <div class="appointment-header">
            <h1>APPOINTMENT</h1>
            <div class="sort-dropdown">
                <button class="sort-btn">
                    Sort by
                    <i class="fas fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <a href="?sort=date_asc">Date (Ascending)</a>
                    <a href="?sort=date_desc">Date (Descending)</a>
                    <a href="?sort=name_asc">Name (A-Z)</a>
                    <a href="?sort=name_desc">Name (Z-A)</a>
                </div>
            </div>
        </div>

        <div class="appointment-grid">
            <?php
            include 'db_conn.php';
            fetch_appointments();
            ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all dropdown navigation items
            const dropdowns = document.querySelectorAll('nav ul li.dropdown');

            dropdowns.forEach(dropdown => {
                // For mobile: handle click events
                dropdown.addEventListener('click', function(e) {
                    // Get the dropdown menu element
                    const dropdownMenu = this.querySelector('.dropdown-menu');

                    // Check if we're on mobile
                    if (window.innerWidth <= 768) {
                        // Toggle the display of the dropdown menu
                        if (dropdownMenu.style.display === 'block') {
                            dropdownMenu.style.display = 'none';
                        } else {
                            // First, close all other dropdowns
                            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                                menu.style.display = 'none';
                            });

                            // Then open this one
                            dropdownMenu.style.display = 'block';
                        }

                        // Prevent the default action (following the link)
                        e.preventDefault();
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                }
            });
        });
    </script>
</body>

</html>