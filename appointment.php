<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Page - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="Style/appointment.css">
    <link rel="stylesheet" href="Style/headerstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="appointment-container">
            <div class="appointment-header">
                <h1>APPOINTMENT</h1>
                <div class="header-actions">
                    <button id="addapp" onclick="window.location.href='create_appoint.php'">
                        <i class="fas fa-plus"></i> Add Appointment
                    </button>
                    <div class="sort-dropdown">
                        <button class="sort-btn">
                            Sort By <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="?sort=date_asc">Date (Ascending)</a>
                            <a href="?sort=date_desc">Date (Descending)</a>
                            <a href="?sort=name_asc">Name (A-Z)</a>
                            <a href="?sort=name_desc">Name (Z-A)</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="appointment-table">
                <div class="table-header">
                    <div>Name</div>
                    <div>Date</div>
                    <div>Contact</div>
                    <div>Program</div>
                </div>
                <div class="table-body">
                    <?php
                    include 'db_conn.php';
                    fetch_appointments();
                    ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get dropdown elements
            const sortBtn = document.querySelector('.sort-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (sortBtn) {
                sortBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownContent.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                window.addEventListener('click', function() {
                    if (dropdownContent.classList.contains('show')) {
                        dropdownContent.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>

</html>