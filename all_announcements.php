<?php
require_once 'auth.php';
require_once 'db_conn.php';
require_once 'image_helper.php';

// Pagination settings
$announcements_per_page = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $announcements_per_page;

// Category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Connect to database
$conn = connect_db();

// Build query based on filter
$where_clause = "status = 'active'";
if ($category_filter !== 'all') {
    $where_clause .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM announcements WHERE " . $where_clause;
$count_result = $conn->query($count_sql);
$total_announcements = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_announcements / $announcements_per_page);

// Fetch announcements
$sql = "SELECT * FROM announcements WHERE " . $where_clause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $announcements_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get all unique categories for filter
$categories_sql = "SELECT DISTINCT category FROM announcements WHERE status = 'active' ORDER BY category";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat['category'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Announcements - City Health Office of San Pablo</title>
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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-green-light) 0%, var(--accent-green) 100%);
            padding: 60px 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 20px;
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 50px 40px;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .filter-label {
            font-weight: 700;
            color: var(--primary-green-dark);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label i {
            color: var(--accent-green);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-button {
            padding: 10px 20px;
            border: 2px solid var(--accent-green);
            background: white;
            color: var(--accent-green);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .filter-button:hover,
        .filter-button.active {
            background: var(--accent-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow);
        }

        /* Results Info */
        .results-info {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--accent-green);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            font-size: 1.1rem;
            color: var(--primary-green-dark);
            font-weight: 600;
        }

        .results-count i {
            color: var(--accent-green);
            margin-right: 8px;
        }

        /* Announcements Grid */
        .announcements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .announcement-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
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
            margin-bottom: 15px;
        }

        .read-more {
            color: var(--accent-green);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: gap 0.3s ease;
        }

        .announcement-card:hover .read-more {
            gap: 10px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 50px;
        }

        .pagination-button {
            padding: 12px 20px;
            background: white;
            color: var(--accent-green);
            border: 2px solid var(--accent-green);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .pagination-button:hover:not(.disabled) {
            background: var(--accent-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .pagination-button.active {
            background: var(--accent-green);
            color: white;
        }

        .pagination-button.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .pagination-numbers {
            display: flex;
            gap: 5px;
        }

        /* No Results */
        .no-announcements {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            border: 2px dashed #ddd;
        }

        .no-announcements i {
            font-size: 5rem;
            color: var(--accent-green);
            margin-bottom: 25px;
            opacity: 0.5;
        }

        .no-announcements h3 {
            color: var(--primary-green);
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .no-announcements p {
            color: var(--gray-text);
            font-size: 1.1rem;
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

            .page-header {
                padding: 40px 20px;
            }

            .page-header h1 {
                font-size: 2.2rem;
            }

            .container {
                padding: 35px 20px;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }

            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-buttons {
                width: 100%;
            }

            .filter-button {
                flex: 1;
                text-align: center;
            }

            .pagination {
                flex-wrap: wrap;
            }

            .pagination-numbers {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.8rem;
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header-content">
            <h1>All Announcements</h1>
            <p>Stay updated with the latest news, events, and health advisories from the City Health Office</p>
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-label">
                <i class="fas fa-filter"></i>
                Filter by Category:
            </div>
            <div class="filter-buttons">
                <a href="all_announcements.php?category=all" class="filter-button <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
                    All
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="all_announcements.php?category=<?php echo urlencode($cat); ?>" class="filter-button <?php echo $category_filter === $cat ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                <i class="fas fa-list-ul"></i>
                Showing <?php echo min($announcements_per_page, $total_announcements - $offset); ?> of <?php echo $total_announcements; ?> announcements
                <?php if ($category_filter !== 'all'): ?>
                    in <strong><?php echo htmlspecialchars($category_filter); ?></strong>
                <?php endif; ?>
            </div>
            <div style="color: var(--gray-text);">
                Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?>
            </div>
        </div>

        <!-- Announcements Grid -->
        <div class="announcements-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($announcement = $result->fetch_assoc()) {
                    echo '<a href="announcement_details.php?id=' . $announcement['id'] . '" class="announcement-card">';

                    if (has_announcement_image($announcement)) {
                        echo '<img src="' . get_image_data_url($announcement['image_data'], $announcement['image_type']) . '" alt="Announcement Image" class="announcement-image">';
                    }

                    echo '<div class="announcement-content">';
                    echo '<div class="announcement-header">';
                    echo '<span class="announcement-category">' . htmlspecialchars($announcement['category']) . '</span>';
                    echo '<span class="announcement-date">' . date('M j, Y', strtotime($announcement['created_at'])) . '</span>';
                    echo '</div>';
                    echo '<h3 class="announcement-title">' . htmlspecialchars($announcement['title']) . '</h3>';
                    echo '<p class="announcement-text">' . htmlspecialchars(substr($announcement['content'], 0, 150)) . '...</p>';
                    echo '<span class="read-more">Read More <i class="fas fa-arrow-right"></i></span>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo '<div class="no-announcements" style="grid-column: 1 / -1;">';
                echo '<i class="fas fa-bell-slash"></i>';
                echo '<h3>No Announcements Found</h3>';
                echo '<p>There are no announcements in this category at the moment. Please check back later or try a different filter.</p>';
                echo '</div>';
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Previous Button -->
                <?php if ($page > 1): ?>
                    <a href="all_announcements.php?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category_filter); ?>" class="pagination-button">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <span class="pagination-button disabled">
                        <i class="fas fa-chevron-left"></i> Previous
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <div class="pagination-numbers">
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="all_announcements.php?page=<?php echo $i; ?>&category=<?php echo urlencode($category_filter); ?>" class="pagination-button <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <!-- Next Button -->
                <?php if ($page < $total_pages): ?>
                    <a href="all_announcements.php?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category_filter); ?>" class="pagination-button">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-button disabled">
                        Next <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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