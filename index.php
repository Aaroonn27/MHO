<?php
$services = [
    [
        "name" => "Medical Consultations",
        "content" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam vel semper metus. Donec efficitur, nibh eget facilisis volutpat, magna nisl dictum mauris, vitae fringilla arcu eros et nulla."
    ],
    [
        "name" => "Immunization Programs",
        "content" => "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."
    ],
    [
        "name" => "Maternal & Child Care",
        "content" => "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
    ],
    [
        "name" => "Dental Services",
        "content" => "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident."
    ],
    [
        "name" => "Health Education",
        "content" => "Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio."
    ]
];
$programs = [
    [
        "name" => "Dengue Prevention Campaign",
        "content" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras quis nulla ac nunc mollis aliquam. Proin vitae velit vitae leo tincidunt ultrices. Integer vel rutrum dui, at tempus metus."
    ],
    [
        "name" => "Nutrition Month Activities",
        "content" => "Nulla facilisi. Vivamus venenatis, turpis at pulvinar euismod, nisi tortor eleifend elit, non finibus sem nisi sed turpis. Donec eu interdum erat, vel convallis lacus."
    ],
    [
        "name" => "Community Wellness Program",
        "content" => "Sed vitae tincidunt dolor. Aliquam erat volutpat. Integer sed eros at dolor vehicula vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae."
    ],
    [
        "name" => "Health Screening Days",
        "content" => "Fusce id molestie libero. Duis venenatis, neque id gravida tincidunt, metus lacus pretium nisi, ac tincidunt odio justo in arcu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas."
    ],
    [
        "name" => "Senior Health Initiative",
        "content" => "Morbi consequat accumsan leo, eu dictum urna tempor ut. In hac habitasse platea dictumst. Praesent vehicula, nisi in cursus dapibus, tellus tellus accumsan quam, vel facilisis metus enim vel tortor."
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Style/homee.css">
    <link rel="stylesheet" href="Style/header.css">
</head>

<body>
    
    <?php include 'includes/header.php'; ?>

    <div class="banner">
        <h1>CITY HEALTH OFFICE OF SAN PABLO</h1>
        <p>Providing quality healthcare services to our community</p>
    </div>

    <main>
        <div class="column">
            <h2>Services</h2>
            <div class="content-box">
                <?php foreach ($services as $index => $service): ?>
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span><?php echo $service['name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p><?php echo $service['content']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="column">
            <h2>Programs</h2>
            <div class="content-box">
                <?php foreach ($programs as $index => $program): ?>
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span><?php echo $program['name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p><?php echo $program['content']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <h2>About Us</h2>
        <p>The City Health Office of San Pablo is dedicated to providing quality healthcare services to the residents of San Pablo City. Our mission is to promote health, prevent disease, and protect the well-being of our community through accessible and responsive healthcare programs.</p>
    </footer>

    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                const header = item.querySelector('.dropdown-header');
                
                header.addEventListener('click', function() {
                    // Close all other dropdowns
                    dropdownItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current dropdown
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>