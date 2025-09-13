<?php
// auth.php - Authentication functions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_conn.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get current user's role
function get_user_role() {
    return $_SESSION['role'] ?? null;
}

// Get current user's info
function get_user_info() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'full_name' => $_SESSION['full_name']
    ];
}

// Check if user has required role
function has_role($required_roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = get_user_role();
    
    // Convert single role to array
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    return in_array($user_role, $required_roles);
}

// Redirect to login page
function redirect_to_login($message = '') {
    $redirect_url = 'login.php';
    if ($message) {
        $redirect_url .= '?message=' . urlencode($message);
    }
    header("Location: " . $redirect_url);
    exit();
}

// Check page access (use this at the top of protected pages)
function check_page_access($required_roles) {
    if (!is_logged_in()) {
        redirect_to_login('Please log in to access this page.');
    }
    
    if (!has_role($required_roles)) {
        redirect_to_login('You do not have permission to access this page.');
    }
}

// Require login (use this for pages that need login but no specific role)
function require_login() {
    if (!is_logged_in()) {
        redirect_to_login('Please log in to continue.');
    }
}

// Get role display name
function get_role_display_name($role) {
    switch ($role) {
        case 'admin':
            return 'Administrator';
        case 'cho_employee':
            return 'CHO Employee';
        case 'abtc_employee':
            return 'ABTC Employee';
        default:
            return 'Unknown';
    }
}

// Check if user can access specific features
function can_access_announcements() {
    return has_role(['admin', 'cho_employee', 'abtc_employee']);
}

function can_access_charge_slip() {
    return has_role(['admin', 'cho_employee']);
}

function can_access_appointments() {
    return has_role(['admin', 'abtc_employee']);
}

function can_access_inventory() {
    return has_role(['admin', 'abtc_employee']);
}

function can_access_patient_records() {
    return has_role(['admin', 'abtc_employee']);
}

function can_create_accounts() {
    return has_role(['admin']);
}

// Logout function
function logout_user() {
    // Destroy all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Generate navigation menu based on user role
function generate_navigation() {
    if (!is_logged_in()) {
        // Return public navigation
        return '
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i><span>Home</span></a></li>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i><span>Login</span></a></li>
            </ul>
        </nav>';
    }
    
    $nav_items = [];
    $role = get_user_role();
    
    // Home is always available
    $nav_items[] = '<li><a href="index.php"><i class="fas fa-home"></i><span>Home</span></a></li>';
    
    // Role-based menu items
    if (can_access_appointments()) {
        $nav_items[] = '<li><a href="appointment.php"><i class="far fa-calendar-alt"></i><span>Appointment</span></a></li>';
    }
    
    if (can_access_charge_slip()) {
        $nav_items[] = '<li><a href="charge_slip.php"><i class="fas fa-file-invoice"></i><span>Charge Slip</span></a></li>';
    }
    
    if (can_access_inventory()) {
        $nav_items[] = '<li><a href="inventory.php"><i class="fas fa-box"></i><span>Inventory</span></a></li>';
    }
    
    if (can_access_patient_records()) {
        $nav_items[] = '<li><a href="rabies_registry.php"><i class="fas fa-user-md"></i><span>Patient Record</span></a></li>';
    }
    
    // Admin-only items
    if (can_create_accounts()) {
        // $nav_items[] = '<li><a href="create_account.php"><i class="fas fa-user-plus"></i><span>Create Account</span></a></li>';
    }
    
    // Dashboard and logout
    // $nav_items[] = '<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>';
    $nav_items[] = '<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>';
    
    return '<nav><ul>' . implode('', $nav_items) . '</ul></nav>';
}

// Get user's accessible pages for dashboard
function get_accessible_pages() {
    if (!is_logged_in()) {
        return [];
    }
    
    $pages = [];
    
    if (can_access_announcements()) {
        $pages[] = [
            'title' => 'Manage Announcements',
            'url' => 'manage_announcements.php',
            'icon' => 'fas fa-bullhorn',
            'description' => 'Create and manage health office announcements'
        ];
    }
    
    if (can_access_charge_slip()) {
        $pages[] = [
            'title' => 'Charge Slip',
            'url' => 'charge_slip.php',
            'icon' => 'fas fa-file-invoice',
            'description' => 'Generate charge slips for services'
        ];
    }
    
    if (can_access_appointments()) {
        $pages[] = [
            'title' => 'Appointments',
            'url' => 'appointment.php',
            'icon' => 'far fa-calendar-alt',
            'description' => 'Manage patient appointments'
        ];
    }
    
    if (can_access_inventory()) {
        $pages[] = [
            'title' => 'Inventory',
            'url' => 'inventory.php',
            'icon' => 'fas fa-box',
            'description' => 'Manage medical supplies and equipment'
        ];
    }
    
    if (can_access_patient_records()) {
        $pages[] = [
            'title' => 'Patient Records',
            'url' => 'rabies_registry.php',
            'icon' => 'fas fa-user-md',
            'description' => 'View and manage patient records'
        ];
    }
    
    if (can_create_accounts()) {
        $pages[] = [
            'title' => 'Create Account',
            'url' => 'create_account.php',
            'icon' => 'fas fa-user-plus',
            'description' => 'Create new employee accounts'
        ];
    }
    
    return $pages;
}

// Verify current user is still valid (call this periodically)
function verify_user_session() {
    if (!is_logged_in()) {
        return false;
    }
    
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $valid = $result->num_rows === 1;
    
    $stmt->close();
    $conn->close();
    
    if (!$valid) {
        logout_user();
    }
    
    return $valid;
}
?>