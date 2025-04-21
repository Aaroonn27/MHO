<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Page</title>
    <link rel="stylesheet" href="Style/appoint.css">
    <link rel="stylesheet" href="Style/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

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
            const sortBtn = document.querySelector('.sort-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (sortBtn) {
                sortBtn.addEventListener('click', function() {
                    dropdownContent.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                window.addEventListener('click', function(e) {
                    if (!e.target.matches('.sort-btn') && !e.target.matches('.sort-btn *')) {
                        if (dropdownContent.classList.contains('show')) {
                            dropdownContent.classList.remove('show');
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>