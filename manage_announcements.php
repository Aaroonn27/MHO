<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee', 'cho_employee'];
check_page_access($required_roles);

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

                // Handle image upload - store in database as BLOB
                $image_data = null;
                $image_type = null;
                $upload_error = null;

                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        // Validate file type
                        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        $file_type = $_FILES['image']['type'];

                        if (!in_array($file_type, $allowed_types)) {
                            $upload_error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
                        }

                        // Validate file size (5MB max)
                        $max_size = 5 * 1024 * 1024;
                        if ($_FILES['image']['size'] > $max_size) {
                            $upload_error = "File is too large. Maximum size is 5MB.";
                        }

                        if (!$upload_error) {
                            // Read the file content
                            $image_data = file_get_contents($_FILES['image']['tmp_name']);
                            $image_type = $file_type;
                        }
                    } else {
                        switch ($_FILES['image']['error']) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $upload_error = "File is too large.";
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $upload_error = "File was only partially uploaded.";
                                break;
                            default:
                                $upload_error = "File upload error (Code: " . $_FILES['image']['error'] . ")";
                        }
                    }
                }

                if ($upload_error) {
                    $conn->close();
                    header("Location: manage_announcements.php?error=" . urlencode($upload_error));
                    exit;
                }

                // Prepare statement with BLOB data
                $sql = "INSERT INTO announcements (title, content, category, image_data, image_type, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssbss", $title, $content, $category, $image_data, $image_type, $status);
                
                // Send binary data
                $stmt->send_long_data(3, $image_data);

                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?success=added");
                    exit;
                } else {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?error=" . urlencode("Database error: " . $conn->error));
                    exit;
                }
                break;

            case 'update_status':
                $id = intval($_POST['id']);
                $status = $conn->real_escape_string($_POST['status']);
                
                $sql = "UPDATE announcements SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $status, $id);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?success=updated");
                    exit;
                } else {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?error=" . urlencode("Failed to update status"));
                    exit;
                }
                break;

            case 'delete':
                $id = intval($_POST['id']);
                
                $sql = "DELETE FROM announcements WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?success=deleted");
                    exit;
                } else {
                    $stmt->close();
                    $conn->close();
                    header("Location: manage_announcements.php?error=" . urlencode("Failed to delete announcement"));
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
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d5f3f;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2d5f3f;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .header h1 i {
            margin-right: 15px;
            color: #4a8f5f;
        }

        .back-btn {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(45, 95, 63, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 95, 63, 0.4);
        }

        .form-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d5f3f;
        }

        .form-section h2 {
            color: #2d5f3f;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .form-section h2 i {
            margin-right: 10px;
            color: #4a8f5f;
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
            color: #2d5f3f;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }

        .announcements-list {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d5f3f;
        }

        .announcements-list h2 {
            color: #2d5f3f;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .announcements-list h2 i {
            margin-right: 10px;
            color: #4a8f5f;
        }

        .announcement-item {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: white;
        }

        .announcement-item:hover {
            box-shadow: 0 4px 15px rgba(45, 95, 63, 0.1);
            border-color: #4a8f5f;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .announcement-info h3 {
            color: #2d5f3f;
            font-size: 1.3rem;
            margin-bottom: 8px;
        }

        .announcement-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
            flex-wrap: wrap;
        }

        .announcement-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .announcement-meta i {
            color: #4a8f5f;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.inactive {
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24;
            border: 1px solid #f1aeb5;
        }

        .announcement-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .action-btn.toggle {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
        }

        .action-btn.delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            border: 2px solid #e9ecef;
        }

        .success-message, .error-message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease;
        }

        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #c3e6cb;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24;
            border: 2px solid #f1aeb5;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message i {
            color: #28a745;
        }

        .error-message i {
            color: #dc3545;
        }

        .no-announcements {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-announcements i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #4a8f5f;
            opacity: 0.5;
        }

        .no-announcements h3 {
            color: #2d5f3f;
            margin-bottom: 10px;
        }

        .upload-container {
            border: 3px dashed #4a8f5f;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #f8fdf9;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-container:hover {
            border-color: #2d5f3f;
            background: #e8f5e9;
        }

        .upload-container.dragover {
            border-color: #28a745;
            background: #d4edda;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3rem;
            color: #4a8f5f;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #2d5f3f;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .upload-hint {
            color: #666;
            font-size: 13px;
        }

        .image-preview-container {
            margin-top: 15px;
            display: none;
        }

        .image-preview-container.active {
            display: block;
        }

        .preview-wrapper {
            position: relative;
            display: inline-block;
        }

        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            border: 3px solid #4a8f5f;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
            transition: all 0.3s ease;
        }

        .remove-image:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .file-info {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .file-info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13px;
        }

        .file-info-label {
            color: #666;
        }

        .file-info-value {
            color: #2d5f3f;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
                justify-content: center;
            }

            .back-btn {
                width: 100%;
                justify-content: center;
            }

            .form-section,
            .announcements-list {
                padding: 20px;
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
                width: 100%;
            }

            .action-btn {
                flex: 1;
                min-width: 110px;
                justify-content: center;
            }

            .announcement-meta {
                flex-direction: column;
                gap: 8px;
            }

            .announcement-image {
                width: 60px;
                height: 60px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.6rem;
            }

            .form-section h2,
            .announcements-list h2 {
                font-size: 1.4rem;
            }

            .announcement-info h3 {
                font-size: 1.1rem;
            }

            .submit-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-bullhorn"></i>Manage Announcements</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>Return to Dashboard
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

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
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

                    <div class="form-group full-width">
                        <label for="image">Image (Optional)</label>
                        <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                        
                        <div class="upload-container" id="uploadContainer">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">Tap to select an image</div>
                            <div class="upload-hint">or drag and drop here</div>
                            <div class="upload-hint" style="margin-top: 8px;">
                                <i class="fas fa-info-circle"></i> JPG, PNG, GIF, WEBP (Max: 5MB)
                            </div>
                        </div>

                        <div class="image-preview-container" id="previewContainer">
                            <div class="preview-wrapper">
                                <img id="imagePreview" class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image" id="removeImage">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="file-info" id="fileInfo"></div>
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
                                <?php if (!empty($announcement['image_data'])): ?>
                                    <img src="data:<?php echo htmlspecialchars($announcement['image_type']); ?>;base64,<?php echo base64_encode($announcement['image_data']); ?>" 
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
        (function() {
            const fileInput = document.getElementById('image');
            const uploadContainer = document.getElementById('uploadContainer');
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const removeButton = document.getElementById('removeImage');
            const fileInfo = document.getElementById('fileInfo');

            uploadContainer.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                handleFiles(this.files);
            });

            uploadContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadContainer.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFiles(files);
                }
            });

            removeButton.addEventListener('click', function() {
                fileInput.value = '';
                previewContainer.classList.remove('active');
                uploadContainer.style.display = 'block';
            });

            function handleFiles(files) {
                if (files.length === 0) return;

                const file = files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!validTypes.includes(file.type)) {
                    alert('Invalid file type! Please select a JPG, PNG, GIF, or WEBP image.');
                    fileInput.value = '';
                    return;
                }

                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('File is too large! Maximum size is 5MB.');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    uploadContainer.style.display = 'none';
                    previewContainer.classList.add('active');
                    displayFileInfo(file);
                };
                reader.readAsDataURL(file);
            }

            function displayFileInfo(file) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const fileType = file.type.split('/')[1].toUpperCase();

                fileInfo.innerHTML = `
                    <div class="file-info-item">
                        <span class="file-info-label"><i class="fas fa-file-image"></i> Filename:</span>
                        <span class="file-info-value">${file.name}</span>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label"><i class="fas fa-file-alt"></i> Type:</span>
                        <span class="file-info-value">${fileType}</span>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label"><i class="fas fa-weight"></i> Size:</span>
                        <span class="file-info-value">${fileSizeMB} MB</span>
                    </div>
                `;
            }

            document.querySelector('form').addEventListener('submit', function(e) {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        e.preventDefault();
                        alert('Please remove or replace the image. File size exceeds 5MB limit.');
                        return false;
                    }
                }
            });
        })();
    </script>
</body>
</html>