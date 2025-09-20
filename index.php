<?php
session_start();
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

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.15);
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
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
            gap: 30px;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
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
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        nav ul li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        nav ul li a:hover::before {
            left: 100%;
        }

        nav ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
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
            padding: 80px 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(15px);
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-banner h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
        }

        .hero-banner p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
            font-weight: 300;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            color: #ffd700;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Content */
        main {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .section-title p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Announcements Section */
        .announcements-section {
            margin-bottom: 80px;
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .announcements-title {
            display: flex;
            align-items: center;
            color: white;
        }

        .announcements-title i {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #ffd700;
        }

        .announcements-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .admin-link {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .admin-link i {
            margin-right: 8px;
        }

        .announcements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .announcement-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .announcement-date {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .announcement-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .announcement-text {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }

        .no-announcements {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .no-announcements i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 20px;
        }

        .no-announcements h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .no-announcements p {
            color: rgba(255, 255, 255, 0.8);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 80px;
        }

        .content-column {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-column:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .column-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .column-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .column-icon i {
            font-size: 24px;
            color: white;
        }

        .column-header h3 {
            font-size: 1.8rem;
            color: #333;
            font-weight: 700;
        }

        .dropdown-item {
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            padding: 20px 25px;
            cursor: pointer;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dropdown-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s;
        }

        .dropdown-header:hover::before {
            left: 100%;
        }

        .dropdown-header:hover {
            background: linear-gradient(135deg, #e8edff 0%, #dde4ff 100%);
            border-left-color: #764ba2;
        }

        .dropdown-header span {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
            flex: 1;
            padding-right: 15px;
        }

        .dropdown-header i {
            color: #667eea;
            font-size: 18px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .dropdown-content {
            display: none;
            padding: 25px;
            background: white;
            font-size: 15px;
            line-height: 1.7;
            color: #555;
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
            border-left-color: #764ba2;
            background: linear-gradient(135deg, #dde4ff 0%, #d0d9ff 100%);
        }

        .dropdown-item.active .dropdown-header i {
            transform: rotate(180deg);
            color: #764ba2;
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
            color: #666;
        }

        .dropdown-content strong {
            color: #333;
        }

        .dropdown-content a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .dropdown-content a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
            color: white;
            padding: 60px 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .footer h2 {
            font-size: 2.2rem;
            margin-bottom: 25px;
            color: white;
            font-weight: 700;
        }

        .footer p {
            font-size: 1.1rem;
            line-height: 1.7;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: #ffd700;
            transform: translateY(-3px);
        }

        .copyright {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0.7;
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
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .chatbot-button:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }

        .chatbot-button i {
            font-size: 28px;
            color: white;
        }

        .chatbot-tooltip {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 12px 18px;
            border-radius: 25px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }

            50% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6), 0 0 0 0 rgba(102, 126, 234, 0.4);
            }

            100% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4), 0 0 0 20px rgba(102, 126, 234, 0);
            }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .chatbot-float {
                bottom: 20px;
                right: 20px;
            }

            .chatbot-button {
                width: 60px;
                height: 60px;
            }

            .chatbot-button i {
                font-size: 24px;
            }

            .chatbot-tooltip {
                display: none;
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-stats {
                gap: 40px;
            }

            .content-grid {
                gap: 30px;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container h1 {
                font-size: 1.6rem;
                text-align: center;
            }

            nav ul {
                gap: 15px;
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
                padding: 50px 20px;
            }

            .hero-banner h1 {
                font-size: 2.5rem;
            }

            .hero-banner p {
                font-size: 1.1rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 30px;
            }

            main {
                padding: 40px 20px;
            }

            .content-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .content-column {
                padding: 30px 25px;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .announcements-header {
                flex-direction: column;
                align-items: center;
                gap: 20px;
                text-align: center;
            }

            .announcements-title h2 {
                font-size: 2rem;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                padding: 40px 20px 30px;
            }

            .footer-links {
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .logo-container h1 {
                font-size: 1.4rem;
            }

            .hero-banner h1 {
                font-size: 2rem;
            }

            .dropdown-header {
                padding: 15px 20px;
            }

            .dropdown-header span {
                font-size: 1rem;
            }

            .dropdown-content {
                padding: 20px;
                font-size: 14px;
            }

            .announcement-content {
                padding: 20px;
            }

            .announcement-title {
                font-size: 1.2rem;
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
                        echo '<div class="announcement-card">';

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
                        echo '</div>';
                        echo '</div>';
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
            </div>
        </section>

        <div class="section-title">
            <h2>Our Services & Programs</h2>
            <p>Comprehensive healthcare solutions tailored for our community's needs</p>
        </div>

        <div class="content-grid">
            <!-- Services Column -->
            <div class="content-column">
                <div class="column-header">
                    <div class="column-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3>Services</h3>
                </div>

                <!-- Service Items -->
                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Health Certification for Workers</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to The Code on Sanitation of the Philippines</strong> (P.D. 856 Chapter III, Section 15) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Workers of food and non-food establishments<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li><strong>Valid Laboratory Exam Result</strong> (Click "More Details" For Clarification)</li>
                            <li><strong>1x1 ID Picture</strong></li>
                            <li><strong>Community Tax Certificate for the current Year</strong></li>
                            <li><strong>Identification Card</strong></li>
                        </ol>
                        <a href='https://drive.google.com/file/d/1bzcWDB02_oAagntv52JUbiM8wipUbGpi/view?usp=drive_link'>More Details</a>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Medical Certificates for Employment & Training</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        As required by employers, schools and financial institutions. Fees collected is pursuant to Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Applicants for employment, on-the-job-training, loans, scholarships, school entrant<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li><strong>Identification Card</strong></li>
                            <li><strong>Valid Laboratory Exam Results (Click "More Details" For Clarification)</strong></li>
                        </ol>
                        <a href='https://drive.google.com/file/d/1uhFaIHCb0K7ewsj14dj1Rci1DTqsPKHh/view?usp=drive_link'>More Details</a>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Tricycle Driver Medical Certificates</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to Local Ordinance No. 2011-01</strong> (The 2011 Revised Comprehensive Traffic Code of the City of San Pablo)<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Tricycle Drivers<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li><strong>Driver's License</strong></li>
                            <li><strong>Unified Clearance</strong></li>
                        </ol>
                        <a href='https://drive.google.com/file/d/11Hb-Sz7ivHctu_TFhpitSPvH8cOfbVI9/view?usp=drive_link'>More Details</a>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Medical Certificates for Leave of Absence</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        As required by private employers and pursuant to CSC MC No. 41, s. 1998<br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Government Employees and General Public<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li><strong>Consultation within the first three (3) days of illness</strong></li>
                            <li><strong>Laboratory Test (If Available)</strong></li>
                        </ol>
                        <a href='https://drive.google.com/file/d/1ER8_ky-ooMjzNFNTmLxnoOxmfpjQvlGS/view?usp=drive_link'>More Details</a>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>PWD Medical Certificates</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <strong>Pursuant to National Council on Disability Affairs Administrative Order No. 001, s. 2008</strong><br>
                        <strong>Office or Division:</strong> City Health Office<br>
                        <strong>Who May Avail:</strong> Persons with Disabilities and/or their relatives<br>
                        <strong>Requirements:</strong>
                        <ol>
                            <li><strong>Philippine Registry Form for Persons with Disabilities</strong></li>
                            <li><strong>Certification from a Specialist if the disability is uncertain</strong></li>
                            <li><strong>Proof of the disability if client is unable to report for physical examination</strong></li>
                        </ol>
                        <a href='https://drive.google.com/file/d/173ZpJwyo8AVjci1erlrxOwzfsJIOG9rS/view?usp=drive_link'>More Details</a>
                    </div>
                </div>
            </div>

            <!-- Programs Column -->
            <div class="content-column">
                <div class="column-header">
                    <div class="column-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Programs</h3>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Dengue Prevention Campaign</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p>Our comprehensive dengue prevention program focuses on community education, mosquito control measures, and early detection. We conduct regular awareness seminars and provide resources for effective dengue prevention in households and communities.</p>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Nutrition Month Activities</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p>Annual nutrition programs designed to promote healthy eating habits and address malnutrition in our community. Includes free nutritional assessments, cooking demonstrations, and distribution of educational materials about proper nutrition.</p>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Community Wellness Program</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p>A holistic approach to community health featuring regular health screenings, fitness activities, mental health awareness sessions, and wellness workshops. Our goal is to promote overall well-being for all age groups.</p>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Health Screening Days</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p>Monthly community health screening events offering free basic health checkups, blood pressure monitoring, diabetes screening, and health consultations. These events help in early detection and prevention of common health conditions.</p>
                    </div>
                </div>

                <div class="dropdown-item">
                    <div class="dropdown-header">
                        <span>Senior Health Initiative</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <p>Specialized healthcare program for senior citizens including regular health monitoring, medication assistance, health education sessions tailored for elderly needs, and coordination with families for comprehensive care.</p>
                    </div>
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