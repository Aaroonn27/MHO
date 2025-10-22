<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            /* San Pablo Green Color Palette */
            --primary-green: #1a5f3f;
            --primary-green-dark: #0f3d28;
            --primary-green-light: #2a7f5f;
            --accent-green: #3a9b6f;
            --light-green-bg: #e8f5f0;
            --white: #ffffff;
            --gray-text: #555555;
            --gray-light: #f8f9fa;
            --shadow: rgba(26, 95, 63, 0.1);
            --shadow-hover: rgba(26, 95, 63, 0.2);
        }

        body {
            background: var(--gray-light);
            color: var(--gray-text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles */
        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #4a8f5f;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            background: white;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #4a8f5f;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-container h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            align-items: center;
        }

        nav ul li {
            display: inline-block;
        }

        nav ul li a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(74, 143, 95, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        nav ul li a i {
            font-size: 22px;
            margin-bottom: 6px;
        }

        nav ul li a span {
            font-size: 13px;
            font-weight: 600;
        }

        /* Hero Banner */
        .hero-banner {
            padding: 60px 40px;
            background: linear-gradient(135deg, var(--primary-green-light) 0%, var(--accent-green) 100%);
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-banner h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .hero-banner p {
            font-size: 1.2rem;
            margin-bottom: 25px;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 35px;
        }

        .stat-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 20px 30px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            display: block;
            color: white;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Content */
        main {
            padding: 50px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        .section-title p {
            font-size: 1.1rem;
            color: var(--gray-text);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Announcements Section */
        .announcements-section {
            margin-bottom: 60px;
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .announcements-title {
            display: flex;
            align-items: center;
            color: var(--primary-green);
        }

        .announcements-title i {
            font-size: 2.2rem;
            margin-right: 15px;
            color: var(--accent-green);
        }

        .announcements-title h2 {
            font-size: 2.2rem;
            font-weight: 700;
        }

        .admin-link {
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green-light) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--shadow-hover);
        }

        .admin-link i {
            margin-right: 8px;
        }

        .announcements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .announcement-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px var(--shadow-hover);
        }

        .announcement-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .announcement-content {
            padding: 25px;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .announcement-category {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .announcement-date {
            color: #999;
            font-size: 14px;
            font-weight: 500;
        }

        .announcement-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green-dark);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .announcement-text {
            color: var(--gray-text);
            line-height: 1.6;
            font-size: 15px;
        }

        .no-announcements {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            border: 2px dashed #ddd;
        }

        .no-announcements i {
            font-size: 4rem;
            color: var(--accent-green);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-announcements h3 {
            color: var(--primary-green);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .no-announcements p {
            color: var(--gray-text);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }

        .content-column {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-column:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px var(--shadow-hover);
        }

        .column-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-green-bg);
        }

        .column-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .column-icon i {
            font-size: 24px;
            color: white;
        }

        .column-header h3 {
            font-size: 1.7rem;
            color: var(--primary-green-dark);
            font-weight: 700;
        }

        .dropdown-item {
            margin-bottom: 18px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
        }

        .dropdown-item:hover {
            box-shadow: 0 4px 12px var(--shadow);
        }

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light-green-bg);
            padding: 18px 22px;
            cursor: pointer;
            border-left: 4px solid var(--accent-green);
            transition: all 0.3s ease;
        }

        .dropdown-header:hover {
            background: #d4ebe1;
            border-left-color: var(--primary-green);
        }

        .dropdown-header span {
            font-weight: 600;
            color: var(--primary-green-dark);
            font-size: 1.05rem;
            flex: 1;
            padding-right: 15px;
        }

        .dropdown-header i {
            color: var(--accent-green);
            font-size: 16px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .dropdown-content {
            display: none;
            padding: 22px;
            background: white;
            font-size: 15px;
            line-height: 1.7;
            color: var(--gray-text);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item.active .dropdown-header {
            border-left-color: var(--primary-green);
            background: #d4ebe1;
        }

        .dropdown-item.active .dropdown-header i {
            transform: rotate(180deg);
            color: var(--primary-green);
        }

        .dropdown-item.active .dropdown-content {
            display: block;
        }

        .dropdown-content ol {
            margin: 15px 0;
            padding-left: 25px;
        }

        .dropdown-content li {
            margin: 8px 0;
            color: var(--gray-text);
        }

        .dropdown-content strong {
            color: var(--primary-green-dark);
        }

        .dropdown-content a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .dropdown-content a:hover {
            color: var(--primary-green);
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-green-dark) 0%, var(--primary-green) 100%);
            color: white;
            padding: 50px 40px 30px;
            text-align: center;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-green), var(--primary-green-light), var(--accent-green));
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .footer h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
        }

        .footer p {
            font-size: 1.05rem;
            line-height: 1.7;
            opacity: 0.95;
            margin-bottom: 25px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 25px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 22px;
            transition: all 0.3s ease;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .footer-links a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .copyright {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Floating Chatbot Button */
        .chatbot-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
        }

        .chatbot-button {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px var(--shadow-hover);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .chatbot-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px var(--shadow-hover);
        }

        .chatbot-button i {
            font-size: 26px;
            color: white;
        }

        .chatbot-tooltip {
            background: white;
            padding: 10px 16px;
            border-radius: 20px;
            color: var(--primary-green);
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid var(--light-green-bg);
            opacity: 0;
            transform: translateX(10px);
            transition: all 0.3s ease;
            white-space: nowrap;
            pointer-events: none;
        }

        .chatbot-float:hover .chatbot-tooltip {
            opacity: 1;
            transform: translateX(0);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 6px 20px var(--shadow-hover);
            }

            50% {
                box-shadow: 0 6px 20px var(--shadow-hover), 0 0 0 0 rgba(58, 155, 111, 0.4);
            }

            100% {
                box-shadow: 0 6px 20px var(--shadow-hover), 0 0 0 15px rgba(58, 155, 111, 0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-stats {
                gap: 35px;
            }

            .content-grid {
                gap: 25px;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
            }

            .logo-container h1 {
                font-size: 1.5rem;
                text-align: center;
            }

            nav ul {
                gap: 8px;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li a {
                padding: 8px 12px;
            }

            nav ul li a i {
                font-size: 18px;
            }

            nav ul li a span {
                font-size: 11px;
            }

            .hero-banner {
                padding: 40px 20px;
            }

            .hero-banner h1 {
                font-size: 2.2rem;
            }

            .hero-banner p {
                font-size: 1.05rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }

            main {
                padding: 35px 20px;
            }

            .content-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .content-column {
                padding: 25px 20px;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .announcements-header {
                flex-direction: column;
                align-items: center;
                gap: 20px;
                text-align: center;
            }

            .announcements-title h2 {
                font-size: 1.8rem;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                padding: 35px 20px 25px;
            }

            .footer-links {
                gap: 18px;
            }

            .chatbot-float {
                bottom: 20px;
                right: 20px;
            }

            .chatbot-button {
                width: 55px;
                height: 55px;
            }

            .chatbot-button i {
                font-size: 22px;
            }

            .chatbot-tooltip {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .logo-img {
                margin-right: 0;
            }

            .logo-container h1 {
                font-size: 1.3rem;
            }

            .hero-banner h1 {
                font-size: 1.8rem;
            }

            .dropdown-header {
                padding: 15px 18px;
            }

            .dropdown-header span {
                font-size: 0.95rem;
            }

            .dropdown-content {
                padding: 18px;
                font-size: 14px;
            }

            .announcement-content {
                padding: 20px;
            }

            .announcement-title {
                font-size: 1.15rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <?php echo generate_navigation(); ?>
        </nav>
    </header>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1>CITY HEALTH OFFICE</h1>
            <p>Providing quality healthcare services and promoting wellness for all residents of San Pablo City</p>

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">5+</span>
                    <span class="stat-label">Services</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Available</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Dedicated</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <!-- Announcements Section -->
        <section class="announcements-section">
            <div class="announcements-header">
                <div class="announcements-title">
                    <i class="fas fa-bullhorn"></i>
                    <h2>Latest Announcements</h2>
                </div>
                <a href="manage_announcements.php" class="admin-link">
                    <i class="fas fa-plus"></i>Manage Announcements
                </a>
            </div>

            <div class="announcements-grid">
                <?php
                // Include database connection
                require_once 'db_conn.php';

                // Connect to database
                $conn = connect_db();

                // Fetch announcements
                $sql = "SELECT * FROM announcements WHERE status = 'active' ORDER BY created_at DESC LIMIT 6";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($announcement = $result->fetch_assoc()) {
                        echo '<a href="announcement_details.php?id=' . $announcement['id'] . '" class="announcement-card" style="text-decoration: none; color: inherit; display: block;">';

                        if ($announcement['image_path']) {
                            echo '<img src="' . htmlspecialchars($announcement['image_path']) . '" alt="Announcement Image" class="announcement-image">';
                        }

                        echo '<div class="announcement-content">';
                        echo '<div class="announcement-header">';
                        echo '<span class="announcement-category">' . htmlspecialchars($announcement['category']) . '</span>';
                        echo '<span class="announcement-date">' . date('M j, Y', strtotime($announcement['created_at'])) . '</span>';
                        echo '</div>';
                        echo '<h3 class="announcement-title">' . htmlspecialchars($announcement['title']) . '</h3>';
                        echo '<p class="announcement-text">' . htmlspecialchars(substr($announcement['content'], 0, 150)) . '...</p>';

                        // Add "Read More" link
                        echo '<div style="margin-top: 15px;">';
                        echo '<span style="color: var(--accent-green); font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">';
                        echo 'Read More <i class="fas fa-arrow-right"></i>';
                        echo '</span>';
                        echo '</div>';

                        echo '</div>';
                        echo '</a>';
                    }
                } else {
                    echo '<div class="no-announcements">';
                    echo '<i class="fas fa-bell-slash"></i>';
                    echo '<h3>No Announcements Yet</h3>';
                    echo '<p>Check back later for important updates and announcements from the City Health Office.</p>';
                    echo '</div>';
                }

                // Close connection
                $conn->close();
                ?>
        </section>

        <!-- Citizen's Charter Section -->
        <div class="section-title">
            <h2>Citizen's Charter</h2>
            <p>Comprehensive list of services available at the City Health Office of San Pablo</p>
        </div>

        <div style="max-width: 1200px; margin: 0 auto;">
            <!-- Single Citizen's Charter Column -->
            <div class="content-column">
                <div class="column-header">
                    <div class="column-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Citizen's Charter - Health Services</h3>
                </div>

                <!-- 1. Sanitary Permit -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Issuance of Sanitary Permit for Food and Non-Food Establishments</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D.856 Chapter III, Section 14a) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196, s. 2024)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Owner, Manager, or Operator<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li>A duly accomplished Unified Clearance Form</li>
                            <li>Barangay Business Permit</li>
                            <li>For those applying for renewal of Sanitary Permit previously issued Mayor's Permit</li>
                            <li>Additional Requirements (Click "More Details" For Clarification)</li>
                        </ol>
                        <a href="https://drive.google.com/file/d/1DEMIrwVUJJ8uTaft7FUtTA9vyJjdkuFv/view?usp=sharing">More Details</a>
                    </div>
                </div>

                <!-- 2. Health Certification for Workers -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Issuance of Health Certification for Workers of Business Establishments</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856 Chapter III, Section 15) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Workers of food and non-food establishments<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li>Valid Laboratory Exam Result (Click "More Details" For Clarification)</li>
                            <li>1x1 ID Picture</li>
                            <li>Community Tax Certificate for the current Year</li>
                            <li>Identification Card</li>
                        </ol>
                        <a href="https://drive.google.com/file/d/1bzcWDB02_oAagntv52JUbiM8wipUbGpi/view?usp=sharing">More Details</a>
                    </div>
                </div>

                <!-- 3. Medical Certificates for Employment -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Issuance of Medical Certificates for Employment, On-the-job-training, Loans, Scholarships, School Entrants</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        As required by employers, schools and financial institutions. Fees collected is pursuant to Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Applicants for employment, on-the-job-training, loans, scholarships, school entrant<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li>Identification Card</li>
                            <li>Valid Laboratory Exam Results (Click "More Details" For Clarification)</li>
                        </ol>
                        <a href="https://drive.google.com/file/d/1uhFaIHCb0K7ewsj14dj1Rci1DTqsPKHh/view?usp=sharing">More Details</a>
                    </div>
                </div>

                <!-- 4. Tricycle Driver Medical Certificate -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Issuance of Medical Certificate for Tricycle Drivers (Tricycle Franchise)</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to:</strong> Local Ordinance No. 2011-01 (The 2011 Revised Comprehensive Traffic Code of the City of San Pablo, and Creating a Comprehensive and Integrated Traffic Management System/Traffic Assessment Plan in the City of San Pablo)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Tricycle Drivers<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li>Driver's License</li>
                            <li>Unified Clearance</li>
                        </ol>
                        <a href="https://drive.google.com/file/d/11Hb-Sz7ivHctu_TFhpitSPvH8cOfbVI9/view?usp=sharing">More Details</a>
                    </div>
                </div>

                <!-- 5. Medical Certificate for Leave -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Issuance of Medical Certificate for Leave of Absence</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        As required by private employers and pursuant to CSC MC No. 41, s. 1998<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Government Employees and General Public<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li>Consultation within the first three (3) days of illness</li>
                            <li>Laboratory Test (If Available)</li>
                        </ol>
                        <a href="https://drive.google.com/file/d/1ER8_ky-ooMjzNFNTmLxnoOxmfpjQvlGS/view?usp=sharing">More Details</a>
                    </div>
                </div>

                <!-- See More Button -->
                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--light-green-bg);">
                    <a href="citizens_charter.php" style="display: inline-block; background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green-light) 100%); color: white; padding: 15px 40px; border-radius: 25px; text-decoration: none; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 4px 12px var(--shadow);">
                        <i class="fas fa-list" style="margin-right: 10px;"></i>See All Services (25 Total)
                    </a>
                </div>

            </div>
        </div>
    </main>

    <!-- Floating Chatbot Button with Animation -->
    <div class="chatbot-float" onclick="window.location.href='chatbot.php'">
        <div class="chatbot-tooltip">Need help? Let's chat! 💬</div>
        <div class="chatbot-button">
            <i class="fas fa-robot"></i>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h2>About Us</h2>
            <p>The City Health Office of San Pablo is dedicated to providing quality healthcare services to the residents of San Pablo City. Our mission is to promote health, prevent disease, and protect the well-being of our community through accessible and responsive healthcare programs.</p>

            <div class="footer-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" title="Email"><i class="fas fa-envelope"></i></a>
                <a href="#" title="Phone"><i class="fas fa-phone"></i></a>
            </div>

            <div class="copyright">
                <p>&copy; 2024 City Health Office of San Pablo. All rights reserved.</p>
            </div>
        </div>
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

            // Add smooth scrolling for internal links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>