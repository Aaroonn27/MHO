<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - City Health Office of San Pablo City</title>
    <link rel="stylesheet" href="Style/login.css">

</head>
<body>
    <div class="login-container">
        <div class="header">
            <img src="media/chologo.png" alt="CHO Logo">
            <div class="title">
                <h3>City Health Office<br>of<br>San Pablo City</h3>
            </div>
            <img src="media/sanpablologo.png" alt="San Pablo Logo">
        </div>
        
        <div class="login-form">
            <h2>Login</h2>
            
            <?php
            // Display error message if any
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            
            // Display success message if any
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form action="auth.php" method="post">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='index.php'">Cancel</button>
                    <button type="submit" class="btn btn-login">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>