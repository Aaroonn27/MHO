<?php
session_start();
require_once 'auth.php';
require_once 'inventory_functions.php';

$required_roles = ['admin', 'abtc_employee'];
check_page_access($required_roles);

// Get inventory item ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid inventory ID.';
    header('Location: inventory.php');
    exit;
}

// Delete the batch (sets quantity to 0)
if (delete_inventory_batch($id)) {
    $_SESSION['success_message'] = 'Inventory batch deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Error deleting inventory batch. Please try again.';
}

header('Location: inventory.php');
exit;
?>