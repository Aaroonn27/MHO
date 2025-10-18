<?php
session_start();
require_once 'auth.php';
require_once 'db_conn.php';

// Check if user has admin access
$required_roles = ['admin'];
check_page_access($required_roles);

$error_message = '';
$success_message = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];

        try {
            $conn = connect_db();

            if (!$conn) {
                throw new Exception("Database connection failed");
            }

            if ($action === 'toggle_status') {
                $new_status = $_POST['new_status'];

                // Prevent admin from deactivating themselves
                if ($user_id == $_SESSION['user_id'] && $new_status === 'inactive') {
                    $error_message = 'You cannot deactivate your own account.';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_status, $user_id);

                    if ($stmt->execute()) {
                        $success_message = 'User status updated successfully.';
                    } else {
                        $error_message = 'Failed to update user status.';
                    }
                    $stmt->close();
                }
            } elseif ($action === 'delete_user') {
                // Prevent admin from deleting themselves
                if ($user_id == $_SESSION['user_id']) {
                    $error_message = 'You cannot delete your own account.';
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);

                    if ($stmt->execute()) {
                        $success_message = 'User deleted successfully.';
                    } else {
                        $error_message = 'Failed to delete user.';
                    }
                    $stmt->close();
                }
            }

            $conn->close();
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all users
try {
    $conn = connect_db();
    $query = "SELECT id, username, role, full_name, email, status, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($query);
    $users = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $conn->close();
} catch (Exception $e) {
    $error_message = 'Error fetching users: ' . $e->getMessage();
    $users = [];
}

// Get current user info for header
$current_user = get_user_info();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - City Health Office</title>
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
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            color: #333;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #4a8f5f;
            backdrop-filter: blur(20px);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }

        .user-info i {
            color: #a5d6a7;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(45, 95, 63, 0.3);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.1rem;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 10px;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            min-width: 300px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .search-box i {
            color: #4a8f5f;
            margin-right: 10px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 16px;
            flex: 1;
            color: #333;
        }

        .search-box input::placeholder {
            color: #999;
        }

        /* Management Card */
        .management-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            overflow: hidden;
            position: relative;
        }

        .management-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2d5f3f, #4a8f5f, #2d5f3f);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px;
            margin-bottom: 0;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc3545;
            border: 2px solid #fca5a5;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #28a745;
            border: 2px solid #c3e6cb;
            border-left: 4px solid #28a745;
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

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin: 20px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .users-table th {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .users-table tbody tr {
            transition: all 0.2s ease;
        }

        .users-table tbody tr:hover {
            background: #f8fdf9;
        }

        .users-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .users-table tbody tr:nth-child(even):hover {
            background: #f8fdf9;
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
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

        /* Role Badge */
        .role-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
            background: rgba(45, 95, 63, 0.1);
            color: #2d5f3f;
            border: 1px solid rgba(74, 143, 95, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-small {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-toggle {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #fcd34d;
        }

        .btn-toggle:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #fcd34d 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #c53030;
            border: 1px solid #fca5a5;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        /* Buttons */
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 95, 63, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #2d5f3f;
            border: 2px solid #4a8f5f;
        }

        .btn-secondary:hover {
            background: #f8fdf9;
            transform: translateY(-2px);
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(45, 95, 63, 0.3);
        }

        .back-link:hover {
            background: linear-gradient(135deg, #3d7f4f 0%, #2d5f3f 100%);
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            color: #4a8f5f;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #2d5f3f;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-top: 4px solid #2d5f3f;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #2d5f3f;
        }

        .modal-content p {
            margin-bottom: 25px;
            color: #666;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .logo-container h1 {
                font-size: 1.6rem;
            }

            .container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 2rem;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
            }

            .table-container {
                margin: 10px;
            }

            .users-table {
                font-size: 12px;
            }

            .users-table th,
            .users-table td {
                padding: 10px 8px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn-small {
                justify-content: center;
            }

            .btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .logo-container h1 {
                font-size: 1.4rem;
            }

            .logo-img {
                width: 50px;
                height: 50px;
            }

            .management-card {
                border-radius: 12px;
            }

            .modal-content {
                padding: 20px;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .user-info {
                flex-direction: column;
                gap: 5px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-container">
                <div class="logo-img">
                    <img src="/MHO/media/chologo.png" alt="CHO Logo">
                </div>
                <h1>City Health Office of San Pablo</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <span><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                <span>(<?php echo get_role_display_name($current_user['role']); ?>)</span>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="page-header">
            <h2 class="page-title">Account Management</h2>
            <p class="page-subtitle">Manage user accounts and permissions</p>
        </div>

        <div class="action-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search users by name, username, or role...">
            </div>
            <a href="create_account.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i>
                Create New Account
            </a>
        </div>

        <div class="management-card">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>There are no user accounts to display.</p>
                        <a href="create_account.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Create First Account
                        </a>
                    </div>
                <?php else: ?>
                    <table class="users-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span style="color: #667eea; font-size: 12px;">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?: 'Not provided'); ?></td>
                                    <td>
                                        <span class="role-badge">
                                            <?php echo get_role_display_name($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $created = new DateTime($user['created_at']);
                                        echo $created->format('M j, Y');
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn-small btn-toggle"
                                                    onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>', '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                                    <i class="fas fa-toggle-<?php echo $user['status'] === 'active' ? 'off' : 'on'; ?>"></i>
                                                    <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                                <button class="btn-small btn-delete"
                                                    onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic; font-size: 12px;">Your account</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Confirm Action</h3>
            <p id="modalMessage">Are you sure you want to proceed?</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Hidden Forms for Actions -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="user_id" id="statusUserId">
        <input type="hidden" name="new_status" id="newStatus">
    </form>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="deleteUserId">
    </form>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                // Search in username, full name, and role columns
                const username = cells[1].textContent.toLowerCase();
                const fullName = cells[2].textContent.toLowerCase();
                const role = cells[4].textContent.toLowerCase();

                if (username.includes(searchTerm) ||
                    fullName.includes(searchTerm) ||
                    role.includes(searchTerm)) {
                    found = true;
                }

                row.style.display = found ? '' : 'none';
            }
        });

        // Modal functions
        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        function toggleUserStatus(userId, currentStatus, userName) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';

            document.getElementById('modalTitle').textContent = 'Confirm Status Change';
            document.getElementById('modalMessage').textContent =
                `Are you sure you want to ${action} ${userName}?`;

            document.getElementById('confirmButton').onclick = function() {
                document.getElementById('statusUserId').value = userId;
                document.getElementById('newStatus').value = newStatus;
                document.getElementById('statusForm').submit();
            };

            document.getElementById('confirmationModal').style.display = 'block';
        }

        function confirmDelete(userId, userName) {
            document.getElementById('modalTitle').textContent = 'Confirm Deletion';
            document.getElementById('modalMessage').textContent =
                `Are you sure you want to permanently delete ${userName}? This action cannot be undone.`;

            document.getElementById('confirmButton').onclick = function() {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            };

            document.getElementById('confirmationModal').style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target === modal) {
                closeModal();
            }
        };

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>

</html>