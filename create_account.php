<?php
session_start();
require_once 'auth.php';
require_once 'db_conn.php';

// Check if user has admin access
$required_roles = ['admin'];
check_page_access($required_roles);

$error_message = '';
$success_message = '';

// Handle form submission - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    
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
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, email, status) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare failed for insert: " . $conn->error);
                }
                
                $stmt->bind_param("ssssss", $username, $hashed_password, $role, $full_name, $email, $status);
                
                if ($stmt->execute()) {
                    $success_message = 'Account created successfully for ' . htmlspecialchars($full_name) . '!';
                    // Clear form data after successful creation
                    $_POST = array();
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
    
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1 class="header-title">City Health Office</h1>
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

            <form class="form" method="POST" action="" id="accountForm">
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
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
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
                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="cho_employee" <?php echo (isset($_POST['role']) && $_POST['role'] === 'cho_employee') ? 'selected' : ''; ?>>CHO Employee</option>
                                <option value="abtc_employee" <?php echo (isset($_POST['role']) && $_POST['role'] === 'abtc_employee') ? 'selected' : ''; ?>>ABTC Employee</option>
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
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
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
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                            <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
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

            // Ensure form submits properly
            form.addEventListener('submit', function(e) {
                // Don't prevent default - let the form submit normally
                
                // Update button text
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                submitBtn.disabled = true;
                
                // Re-enable button after 5 seconds in case of issues
                setTimeout(function() {
                    submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
                    submitBtn.disabled = false;
                }, 5000);
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