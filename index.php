<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="Style/home.css">
    <link rel="stylesheet" href="Style/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

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
    </script>
</body>

</html>