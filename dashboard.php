<?php
session_start();
require_once 'auth.php';

// Require login for this page
require_login();

// Get user info and accessible pages
$user = get_user_info();
$accessible_pages = get_accessible_pages();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - City Health Office of San Pablo</title>
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

        /* Header Styles - Same as index.php */
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

        /* Welcome Banner */
        .welcome-banner {
            padding: 60px 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(15px);
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .welcome-banner h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        .welcome-banner p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 20px;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-block;
            padding: 15px 25px;
            border-radius: 15px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-role {
            color: #ffd700;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Main Content */
        main {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 35px;
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .card-description {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .card-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* No access message */
        .no-access {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .no-access i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
            margin-top: 80px;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container h1 {
                font-size: 1.6rem;
            }

            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .welcome-banner {
                padding: 40px 20px;
            }

            .welcome-banner h1 {
                font-size: 2.2rem;
            }

            main {
                padding: 40px 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .dashboard-card {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Header with dynamic navigation -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <?php echo generate_navigation(); ?>
    </header>

    <!-- Welcome Banner -->
    <section class="welcome-banner">
        <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        <p>Manage your health office responsibilities from your dashboard</p>
        <div class="user-info">
            <div class="user-role"><?php echo get_role_display_name($user['role']); ?></div>
            <small>Logged in as: <?php echo htmlspecialchars($user['username']); ?></small>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <?php if (!empty($accessible_pages)): ?>
            <div class="section-title" style="text-align: center; margin-bottom: 50px;">
                <h2 style="font-size: 2.5rem; color: white; margin-bottom: 15px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">Your Dashboard</h2>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem;">Access your authorized tools and features</p>
            </div>

            <div class="dashboard-grid">
                <?php foreach ($accessible_pages as $page): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="<?php echo $page['icon']; ?>"></i>
                        </div>
                        <h3 class="card-title"><?php echo htmlspecialchars($page['title']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars($page['description']); ?></p>
                        <a href="<?php echo $page['url']; ?>" class="card-button">
                            <i class="fas fa-arrow-right"></i>
                            Access Tool
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-access">
                <i class="fas fa-lock"></i>
                <h3>No Tools Available</h3>
                <p>You don't have access to any tools at the moment. Please contact your administrator.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div style="position: relative;">
            <p>&copy; 2024 City Health Office of San Pablo. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>