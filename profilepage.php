<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="Style/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li class="active">
                    <a href="profilepage.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>

    </header>

    <main>
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-left">
                    <div class="profile-avatar">
                        <i class="far fa-user"></i>
                    </div>
                </div>
                <div class="profile-right">
                    <?php
                    // Example user data - in a real app, this would come from database
                    $user = [
                        'name' => 'John Doe',
                        'address' => '123 Main Street, City, Country',
                        'contact_no' => '+1 234 567 8901',
                        'birthdate' => '01/01/1990',
                        'employee_no' => 'EMP-12345',
                        'position' => 'Software Engineer'
                    ];
                    ?>

                    <div class="profile-info">
                        <p><span class="label">Name :</span> <?php echo $user['name']; ?></p>
                        <p><span class="label">Address :</span> <?php echo $user['address']; ?></p>
                        <p><span class="label">Contact No. :</span> <?php echo $user['contact_no']; ?></p>
                        <p><span class="label">Birthdate :</span> <?php echo $user['birthdate']; ?></p>
                        <p><span class="label">Employee No. :</span> <?php echo $user['employee_no']; ?></p>
                        <p><span class="label">Position :</span> <?php echo $user['position']; ?></p>
                    </div>

                    <div class="description-box">
                        <p class="label">Description :</p>
                        <textarea id="description" placeholder="Add your description here..."></textarea>
                    </div>
                </div>
            </div>
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