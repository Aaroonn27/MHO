<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to check user role
function has_role($roles) {
    // If $roles is a string, convert to array
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    // Check if user has any of the required roles
    return is_logged_in() && in_array($_SESSION['role'], $roles);
}

// Function to require login (redirect if not logged in)
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error'] = "Please log in to access this page";
        header("Location: ../login.php");
        exit();
    }
}

// Function to require specific role(s)
function require_role($roles) {
    require_login();
    
    if (!has_role($roles)) {
        $_SESSION['error'] = "You don't have permission to access this page";
        
        // Redirect to appropriate dashboard based on role
        switch ($_SESSION['role']) {
            case 'cho_admin':
                header("Location: ../admin/dashboard.php");
                break;
            case 'cho_healthcare':
                header("Location: ../healthcare/dashboard.php");
                break;
            case 'abtc_admin':
                header("Location: ../abtc/admin/dashboard.php");
                break;
            case 'abtc_healthcare':
                header("Location: ../abtc/healthcare/dashboard.php");
                break;
            default:
                header("Location: ../dashboard.php");
        }
        exit();
    }
}

// Function to get current user information
function get_current_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Get user role name for display
function get_role_name($role) {
    switch ($role) {
        case 'cho_admin':
            return 'CHO Administrator';
        case 'cho_healthcare':
            return 'CHO Healthcare Professional';
        case 'abtc_admin':
            return 'ABTC Administrator';
        case 'abtc_healthcare':
            return 'ABTC Healthcare Professional';
        default:
            return 'User';
    }
}
?>