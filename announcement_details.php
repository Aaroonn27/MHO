<?php
require_once 'auth.php';
require_once 'db_conn.php';
require_once 'image_helper.php';

// Get announcement ID from URL
$announcement_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connect to database
$conn = connect_db();

// Fetch the specific announcement
$sql = "SELECT * FROM announcements WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$announcement = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($announcement['title']); ?> - City Health Office</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
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

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--accent-green);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow);
            margin-bottom: 30px;
        }

        .back-button:hover {
            background: var(--primary-green);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--shadow-hover);
        }

        /* Main Content */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 50px 40px;
        }

        .announcement-detail {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid #e0e0e0;
        }

        .announcement-header-section {
            padding: 40px;
            border-bottom: 2px solid var(--light-green-bg);
        }

        .announcement-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .announcement-category {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .announcement-date {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #999;
            font-size: 15px;
            font-weight: 500;
        }

        .announcement-date i {
            color: var(--accent-green);
        }

        .announcement-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green-dark);
            line-height: 1.3;
            margin-bottom: 15px;
        }

        .announcement-image-container {
            width: 100%;
            max-height: 500px;
            overflow: hidden;
            background: var(--gray-light);
        }

        .announcement-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .announcement-content-section {
            padding: 40px;
        }

        .announcement-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--gray-text);
            white-space: pre-wrap;
        }

        .announcement-content p {
            margin-bottom: 20px;
        }

        .announcement-footer {
            padding: 30px 40px;
            background: var(--light-green-bg);
            border-top: 2px solid #d4ebe1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .share-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .share-label {
            font-weight: 600;
            color: var(--primary-green-dark);
        }

        .share-buttons {
            display: flex;
            gap: 10px;
        }

        .share-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .share-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .share-facebook {
            background: #1877f2;
        }

        .share-twitter {
            background: #1da1f2;
        }

        .share-email {
            background: var(--accent-green);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-green-dark) 0%, var(--primary-green) 100%);
            color: white;
            padding: 50px 40px 30px;
            text-align: center;
            margin-top: 60px;
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

        .copyright {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
            }

            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .logo-img {
                margin-right: 0;
            }

            .logo-container h1 {
                font-size: 1.5rem;
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

            .container {
                padding: 30px 20px;
            }

            .announcement-header-section,
            .announcement-content-section,
            .announcement-footer {
                padding: 25px 20px;
            }

            .announcement-title {
                font-size: 1.8rem;
            }

            .announcement-content {
                font-size: 1rem;
            }

            .announcement-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .announcement-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .share-section {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .announcement-title {
                font-size: 1.5rem;
            }

            .back-button {
                padding: 10px 20px;
                font-size: 14px;
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

    <!-- Main Content -->
    <div class="container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>

        <div class="announcement-detail">
            <div class="announcement-header-section">
                <div class="announcement-meta">
                    <span class="announcement-category"><?php echo htmlspecialchars($announcement['category']); ?></span>
                    <span class="announcement-date">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
                    </span>
                </div>
                <h1 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h1>
            </div>

            <?php
            if (has_announcement_image($announcement)): ?>
                <div class="announcement-image-container">
                    <img src="<?php echo get_image_data_url($announcement['image_data'], $announcement['image_type']); ?>"
                        alt="<?php echo htmlspecialchars($announcement['title']); ?>"
                        class="announcement-image">
                </div>
            <?php endif; ?>

            <div class="announcement-content-section">
                <div class="announcement-content">
                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                </div>
            </div>

            <div class="announcement-footer">
                <div class="share-section">
                    <span class="share-label">View this announcement on:</span>
                    <div class="share-buttons">
                        <a href="#" class="share-button share-facebook" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <!-- <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($announcement['title']); ?>" target="_blank" class="share-button share-twitter" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="mailto:?subject=<?php echo urlencode($announcement['title']); ?>&body=<?php echo urlencode('Check out this announcement: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="share-button share-email" title="Share via Email">
                            <i class="fas fa-envelope"></i> -->
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h2>About Us</h2>
            <p>The City Health Office of San Pablo is dedicated to providing quality healthcare services to the residents of San Pablo City.</p>
            <div class="copyright">
                <p>&copy; 2024 City Health Office of San Pablo. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>