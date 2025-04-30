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
    <style>
        /* Additional styles for the new design */
        body {
            background-color: #e6f2ff;
            color: #333;
        }

        header {
            background-color: #4d94ff;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        nav ul li a {
            color: white;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            background-color: white;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .banner {
            padding: 30px;
            background-color: #b3d7ff;
            text-align: center;
            color: #333;
        }

        .banner h1 {
            font-size: 32px;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #335c99;
            border-bottom: 2px solid #b3d7ff;
            padding-bottom: 10px;
        }

        .content-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .dropdown-item {
            margin-bottom: 15px;
        }

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f2f8ff;
            padding: 15px;
            border-radius: 6px;
            cursor: pointer;
            border-left: 4px solid #4d94ff;
            transition: all 0.3s ease;
        }

        .dropdown-header:hover {
            background-color: #e6f0ff;
        }

        .dropdown-header span {
            font-weight: bold;
            color: #335c99;
        }

        .dropdown-header i {
            color: #4d94ff;
            transition: transform 0.3s ease;
        }

        .dropdown-content {
            display: none;
            padding: 15px;
            background-color: #f9fbff;
            border-radius: 0 0 6px 6px;
            margin-top: 2px;
            font-size: 14px;
            line-height: 1.6;
            color: #666;
        }

        .dropdown-item.active .dropdown-header {
            border-radius: 6px 6px 0 0;
            background-color: #e6f0ff;
        }

        .dropdown-item.active .dropdown-header i {
            transform: rotate(180deg);
        }

        .dropdown-item.active .dropdown-content {
            display: block;
        }

        footer {
            padding: 30px 40px;
            background-color: #335c99;
            color: white;
            text-align: center;
        }

        footer h2 {
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        footer p {
            margin-top: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
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