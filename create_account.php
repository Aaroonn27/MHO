<?php
session_start();
require_once 'auth.php';
require_once 'db_conn.php';

// Check if user has admin access
$required_roles = ['admin'];
check_page_access($required_roles);

$error_message = '';
$success_message = '';

// Handle form submission - COMPLETELY REWRITTEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {



    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';



    // Validation
    if (empty($username) || empty($password) || empty($role) || empty($full_name)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif (strlen($username) < 3) {
        $error_message = 'Username must be at least 3 characters long.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {


        try {
            $conn = connect_db();

            if (!$conn) {
                throw new Exception("Database connection failed");
            }



            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            if (!$check_stmt) {
                throw new Exception("Prepare failed for username check: " . $conn->error);
            }

            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();


            if ($check_result->num_rows > 0) {
                $error_message = 'Username already exists. Please choose a different username.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $insert_query = "INSERT INTO users (username, password, role, full_name, email, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);

                if (!$stmt) {
                    throw new Exception("Prepare failed for insert: " . $conn->error);
                }


                $stmt->bind_param("ssssss", $username, $hashed_password, $role, $full_name, $email, $status);


                if ($stmt->execute()) {
                    $new_user_id = $conn->insert_id;
                    $success_message = 'Account created successfully for ' . htmlspecialchars($full_name) . '! (User ID: ' . $new_user_id . ')';
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $stmt->close();
            }

            $check_stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get current user info for header
$current_user = get_user_info();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - City Health Office</title>
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
            background: linear-gradient(135deg, #8c9be0ff 0%, #8260a5ff 100%);
            color: #333;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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

        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #ffffffff;
        }

        .user-info i {
            color: white;
        }

        /* Main Container */
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        /* Form Card */
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            border-radius: 20px 20px 0 0;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
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
            background: rgba(255, 107, 107, 0.15);
            color: #dc3545;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
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

        /* Form Styles */
        .form {
            display: grid;
            gap: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .required {
            color: #dc3545;
            margin-left: 2px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: #667eea;
            font-size: 16px;
            z-index: 2;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            color: #333;
        }

        .form-select {
            padding-left: 45px;
            cursor: pointer;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #999;
            font-weight: 400;
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            display: flex;
            gap: 3px;
            height: 4px;
        }

        .strength-bar {
            flex: 1;
            background: #e0e0e0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-bar.active.weak {
            background: #ff4757;
        }

        .strength-bar.active.medium {
            background: #ffa502;
        }

        .strength-bar.active.strong {
            background: #2ed573;
        }

        .password-hint {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #666;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 2px solid rgba(108, 117, 125, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.2);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Back to Dashboard Link */
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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .form-card {
                padding: 25px;
            }

            .page-title {
                font-size: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {

            .form-input,
            .form-select {
                padding: 12px 12px 12px 40px;
                font-size: 15px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 15px;
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
            <h2 class="page-title">Create New Account</h2>
            <p class="page-subtitle">Add a new user to the City Health Office system</p>
        </div>

        <div class="form-card">
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

            <form class="form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="accountForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Enter username"
                                value="<?php echo ($success_message ? '' : (isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '')); ?>"
                                required
                                minlength="3"
                                maxlength="50">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select id="role" name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo (!$success_message && isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="cho_employee" <?php echo (!$success_message && isset($_POST['role']) && $_POST['role'] === 'cho_employee') ? 'selected' : ''; ?>>CHO Employee</option>
                                <option value="abtc_employee" <?php echo (!$success_message && isset($_POST['role']) && $_POST['role'] === 'abtc_employee') ? 'selected' : ''; ?>>ABTC Employee</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text"
                            id="full_name"
                            name="full_name"
                            class="form-input"
                            placeholder="Enter full name"
                            value="<?php echo ($success_message ? '' : (isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '')); ?>"
                            required
                            maxlength="100">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="Enter email address (optional)"
                            value="<?php echo ($success_message ? '' : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '')); ?>"
                            maxlength="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter password"
                                required
                                minlength="6">
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                        <div class="password-hint">Password must be at least 6 characters long</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm password"
                                required
                                minlength="6">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Account Status</label>
                    <div class="input-wrapper">
                        <i class="fas fa-toggle-on"></i>
                        <select id="status" name="status" class="form-select">
                            <option value="active" <?php echo ($success_message || !isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (!$success_message && isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="manage_accounts.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" name="create_account" value="1" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthBars = document.querySelectorAll('.strength-bar');
            const form = document.getElementById('accountForm');
            const submitBtn = document.getElementById('submitBtn');

            // Debug: Log form submission
            form.addEventListener('submit', function(e) {
                console.log('Form submitted!');
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);

                // Check if create_account button is in the form data
                const formData = new FormData(form);
                console.log('Form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }

                // Update button text
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                submitBtn.disabled = true;
            });

            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = calculatePasswordStrength(password);
                updateStrengthBars(strength);
            });

            function calculatePasswordStrength(password) {
                let strength = 0;

                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^A-Za-z0-9]/)) strength++;

                return Math.min(strength, 4);
            }

            function updateStrengthBars(strength) {
                strengthBars.forEach((bar, index) => {
                    bar.classList.remove('active', 'weak', 'medium', 'strong');

                    if (index < strength) {
                        bar.classList.add('active');

                        if (strength <= 2) {
                            bar.classList.add('weak');
                        } else if (strength <= 3) {
                            bar.classList.add('medium');
                        } else {
                            bar.classList.add('strong');
                        }
                    }
                });
            }

            // Password confirmation validation
            function validatePasswords() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            }

            passwordInput.addEventListener('input', validatePasswords);
            confirmPasswordInput.addEventListener('input', validatePasswords);

            // Username validation (alphanumeric and underscore only)
            const usernameInput = document.getElementById('username');
            usernameInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
            });
        });
    </script>
</body>

</html>