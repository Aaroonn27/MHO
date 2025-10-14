<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_POST && isset($_POST['login'])) {
    require_once 'db_conn.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        $conn = connect_db();
        
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, role, full_name, status FROM users WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = 'Invalid username or password.';
            }
        } else {
            $error_message = 'Invalid username or password.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - City Health Office of San Pablo</title>
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
            background: linear-gradient(135deg, #1e4d2b 0%, #2d5a3d 50%, #3d6b4d 100%);
            color: #333;
            min-height: 100vh;
            line-height: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            margin: 0 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 50px 45px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #2d5a3d, #4a7c5c, #2d5a3d);
            border-radius: 20px 20px 0 0;
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            flex-direction: column;
            gap: 20px;
        }

        .logo-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            background: rgba(45, 90, 61, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid #2d5a3d;
            box-shadow: 0 8px 20px rgba(45, 90, 61, 0.3);
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header-text {
            text-align: center;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d5a3d;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #4a7c5c;
            font-size: 1rem;
            font-weight: 500;
        }

        .city-name {
            color: #1e4d2b;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #c82333;
            border: 2px solid rgba(220, 53, 69, 0.3);
        }

        .alert-success {
            background: rgba(45, 90, 61, 0.1);
            color: #2d5a3d;
            border: 2px solid rgba(45, 90, 61, 0.3);
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
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2d5a3d;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 20px;
            color: #4a7c5c;
            font-size: 18px;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 18px 20px 18px 55px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            background: #ffffff;
            transition: all 0.3s ease;
            color: #333;
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: #2d5a3d;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(45, 90, 61, 0.1);
        }

        .form-input::placeholder {
            color: #999;
            font-weight: 400;
        }

        /* Login Button */
        .login-button {
            background: linear-gradient(135deg, #2d5a3d 0%, #3d6b4d 100%);
            color: white;
            padding: 18px 30px;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(45, 90, 61, 0.3);
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-button:hover::before {
            left: 100%;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(45, 90, 61, 0.4);
            background: linear-gradient(135deg, #3d6b4d 0%, #2d5a3d 100%);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button i {
            margin-right: 10px;
        }

        /* Footer Links */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #e8f5e9;
        }

        .back-link {
            color: #2d5a3d;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 10px;
        }

        .back-link:hover {
            color: #1e4d2b;
            background: rgba(45, 90, 61, 0.1);
            transform: translateX(-3px);
        }

        /* Loading State */
        .login-button.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-button.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s ease infinite;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
                margin: 0 15px;
            }

            .login-title {
                font-size: 1.75rem;
            }

            .logo-img {
                width: 90px;
                height: 90px;
            }

            .form-input {
                padding: 16px 18px 16px 50px;
                font-size: 15px;
            }

            .login-button {
                padding: 16px 25px;
                font-size: 16px;
            }
        }

        /* Security Badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            padding: 12px;
            background: rgba(45, 90, 61, 0.08);
            border-radius: 10px;
            color: #2d5a3d;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(45, 90, 61, 0.15);
        }

        .security-badge i {
            color: #4a7c5c;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo-img">
                        <img src="/MHO/media/chologo.png" alt="CHO Logo">
                    </div>
                    <div class="header-text">
                        <h1 class="login-title">CHO Login Portal</h1>
                        <p class="login-subtitle">City Health Office System</p>
                        <p class="city-name">San Pablo City</p>
                    </div>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-input" 
                               placeholder="Enter your username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Enter your password"
                               required>
                    </div>
                </div>

                <button type="submit" name="login" class="login-button" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                Secure Login - Your data is protected
            </div>

            <div class="login-footer">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Homepage
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');

            // Add loading state on form submission
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<i class="fas fa-spinner"></i> Signing In...';
            });

            // Add floating animation to inputs on focus
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>

</html>