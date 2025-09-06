<?php
// Include database connection
require_once 'db_conn.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connect_db();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $conn->real_escape_string($_POST['title']);
                $content = $conn->real_escape_string($_POST['content']);
                $category = $conn->real_escape_string($_POST['category']);
                $status = $conn->real_escape_string($_POST['status']);
                
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = 'uploads/announcements/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $image_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                        $image_path = $conn->real_escape_string($image_path);
                    } else {
                        $image_path = null;
                    }
                }
                
                $sql = "INSERT INTO announcements (title, content, category, image_path, status, created_at) 
                        VALUES ('$title', '$content', '$category', " . 
                        ($image_path ? "'$image_path'" : "NULL") . ", '$status', NOW())";
                
                if ($conn->query($sql)) {
                    header("Location: manage_announcements.php?success=added");
                    exit;
                }
                break;
                
            case 'update_status':
                $id = (int)$_POST['id'];
                $status = $conn->real_escape_string($_POST['status']);
                
                $sql = "UPDATE announcements SET status = '$status' WHERE id = $id";
                
                if ($conn->query($sql)) {
                    header("Location: manage_announcements.php?success=updated");
                    exit;
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get image path before deletion
                $sql = "SELECT image_path FROM announcements WHERE id = $id";
                $result = $conn->query($sql);
                $announcement = $result->fetch_assoc();
                
                // Delete the announcement
                $sql = "DELETE FROM announcements WHERE id = $id";
                
                if ($conn->query($sql)) {
                    // Delete the image file if it exists
                    if ($announcement && $announcement['image_path'] && file_exists($announcement['image_path'])) {
                        unlink($announcement['image_path']);
                    }
                    
                    header("Location: manage_announcements.php?success=deleted");
                    exit;
                }
                break;
        }
    }
    
    $conn->close();
}

// Fetch all announcements
$conn = connect_db();
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);
$announcements = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - City Health Office</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .header h1 i {
            margin-right: 15px;
            color: #667eea;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .form-section h2 i {
            margin-right: 10px;
            color: #667eea;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            opacity: 0;
            position: absolute;
            z-index: -1;
        }

        .file-input-display {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .file-input-wrapper:hover .file-input-display {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .file-input-display i {
            margin-right: 10px;
            color: #667eea;
        }

        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .announcements-list {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .announcements-list h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .announcements-list h2 i {
            margin-right: 10px;
            color: #667eea;
        }

        .announcement-item {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .announcement-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .announcement-info h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 8px;
        }

        .announcement-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }

        .announcement-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .announcement-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.edit {
            background: #ffc107;
            color: #212529;
        }

        .action-btn.delete {
            background: #dc3545;
            color: white;
        }

        .action-btn.toggle {
            background: #6f42c1;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .announcement-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .announcement-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
            border: 2px solid #e0e0e0;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .no-announcements {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-announcements i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ccc;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .announcement-header {
                flex-direction: column;
                gap: 15px;
            }

            .announcement-actions {
                justify-content: center;
                flex-wrap: wrap;
            }

            .announcement-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-bullhorn"></i>Manage Announcements</h1>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>Back to Home
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php
                switch ($_GET['success']) {
                    case 'added':
                        echo 'Announcement added successfully!';
                        break;
                    case 'updated':
                        echo 'Announcement status updated successfully!';
                        break;
                    case 'deleted':
                        echo 'Announcement deleted successfully!';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i>Add New Announcement</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Health Alert">Health Alert</option>
                            <option value="Service Update">Service Update</option>
                            <option value="Event">Event</option>
                            <option value="Program">Program</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" placeholder="Enter the full announcement content..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image (Optional)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="file-input-display">
                                <i class="fas fa-camera"></i>
                                <span>Choose image file...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i>Add Announcement
                </button>
            </form>
        </div>

        <div class="announcements-list">
            <h2><i class="fas fa-list"></i>All Announcements</h2>
            
            <?php if ($announcements): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-item">
                        <div class="announcement-header">
                            <div style="display: flex; align-items: flex-start;">
                                <?php if ($announcement['image_path'] && file_exists($announcement['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                                         alt="Announcement Image" class="announcement-image">
                                <?php endif; ?>
                                
                                <div class="announcement-info">
                                    <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                    <div class="announcement-meta">
                                        <span><i class="fas fa-tag"></i><?php echo htmlspecialchars($announcement['category']); ?></span>
                                        <span><i class="fas fa-calendar"></i><?php echo date('M j, Y - g:i A', strtotime($announcement['created_at'])); ?></span>
                                        <span class="status-badge <?php echo $announcement['status']; ?>">
                                            <?php echo ucfirst($announcement['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="announcement-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $announcement['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                    <button type="submit" class="action-btn toggle">
                                        <i class="fas fa-toggle-<?php echo $announcement['status'] === 'active' ? 'off' : 'on'; ?>"></i>
                                        <?php echo $announcement['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                                    <button type="submit" class="action-btn delete">
                                        <i class="fas fa-trash"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="announcement-content">
                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-announcements">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No Announcements Yet</h3>
                    <p>Create your first announcement using the form above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // File input display update
        document.getElementById('image').addEventListener('change', function() {
            const display = this.parentElement.querySelector('.file-input-display span');
            if (this.files && this.files[0]) {
                display.textContent = this.files[0].name;
            } else {
                display.textContent = 'Choose image file...';
            }
        });

        // Auto-hide success messages after 5 seconds
        const successMessage = document.querySelector('.success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>