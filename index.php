<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="Style/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="logo-container">
            <h1>LOGO</h1>
        </div>
        <nav>
            <ul>
                <li class="active">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="dropdown">
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

    <div class="banner">
        <!-- Banner area - you can add banner content here -->
    </div>

    <main>
        <div class="column">
            <h2>Services</h2>
            <div class="content-box">
                <?php
                // Array of services
                $services = [
                    "Service 1",
                    "Service 2",
                    "Service 3",
                    "Service 4",
                    "Service 5"
                ];

                // Display each service
                foreach ($services as $service) {
                    echo '<div class="dropdown-item">';
                    echo '<span>' . $service . '</span>';
                    echo '<i class="fas fa-chevron-down"></i>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <div class="column">
            <h2>Programs</h2>
            <div class="content-box">
                <?php
                // Array of programs
                $programs = [
                    "Program 1",
                    "Program 2",
                    "Program 3",
                    "Program 4",
                    "Program 5"
                ];

                // Display each program
                foreach ($programs as $program) {
                    echo '<div class="dropdown-item">';
                    echo '<span>' . $program . '</span>';
                    echo '<i class="fas fa-chevron-down"></i>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </main>

    <footer>
        <h2>About Us</h2>
        <!-- Footer content goes here -->
    </footer>

    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Toggle active class for dropdown content
                    this.classList.toggle('active');

                    // You can add more functionality here for actual dropdown content
                });
            });
        });

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