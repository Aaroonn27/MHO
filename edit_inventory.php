<?php
session_start();
require_once 'auth.php';
require_once 'inventory_functions.php';

$required_roles = ['admin', 'abtc_employee'];
check_page_access($required_roles);

// Get inventory item ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: inventory.php');
    exit;
}

// Get item details
$item = get_inventory_item($id);

if (!$item) {
    header('Location: inventory.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vaccine_name' => trim($_POST['vaccine_name']),
        'vaccine_type' => trim($_POST['vaccine_type']),
        'batch_id' => trim($_POST['batch_id']),
        'expiry_date' => trim($_POST['expiry_date']),
        'current_quantity' => intval($_POST['current_quantity'])
    ];
    
    if (update_inventory_batch($id, $data)) {
        $_SESSION['success_message'] = 'Inventory updated successfully!';
        header('Location: inventory.php');
        exit;
    } else {
        $error_message = 'Error updating inventory. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory - City Health Office of San Pablo</title>
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

        /* Header Styles */
        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #4a8f5f;
            margin-bottom: 0;
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

        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            display: inline-block;
        }

        nav ul li a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(74, 143, 95, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        nav ul li a i {
            font-size: 22px;
            margin-bottom: 6px;
        }

        nav ul li a span {
            font-size: 13px;
            font-weight: 600;
        }

        /* Main Content */
        main {
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(45, 95, 63, 0.3);
        }

        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-title p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.95);
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            border-top: 4px solid #2d5f3f;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            font-size: 1.3rem;
            color: #2d5f3f;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .form-section h3 i {
            color: #4a8f5f;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d5f3f;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-group label .required {
            color: #dc3545;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
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
            border-color: #4a8f5f;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[readonly] {
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .info-box {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #a5d6a7;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-box i {
            font-size: 1.5rem;
            color: #2d5f3f;
        }

        .info-box .info-content {
            flex: 1;
        }

        .info-box .info-label {
            font-size: 0.85rem;
            color: #1b5e20;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-box .info-value {
            font-size: 1.1rem;
            color: #1b5e20;
            font-weight: 700;
        }

        .warning-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-left: 5px solid #ff9800;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: start;
            gap: 15px;
        }

        .warning-box i {
            font-size: 1.5rem;
            color: #ff6b6b;
            margin-top: 3px;
        }

        .warning-box .warning-content {
            flex: 1;
        }

        .warning-box .warning-title {
            font-weight: 700;
            color: #856404;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .warning-box .warning-text {
            color: #856404;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #fca5a5;
            border-left: 5px solid #ef4444;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #991b1b;
            font-weight: 600;
        }

        .error-message i {
            font-size: 1.5rem;
            color: #dc2626;
        }

        /* Button Styles */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 95, 63, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }

        /* Usage History Section */
        .usage-history {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }

        .usage-history h3 {
            font-size: 1.3rem;
            color: #2d5f3f;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .usage-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .usage-table thead {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
        }

        .usage-table th,
        .usage-table td {
            padding: 12px 15px;
            text-align: left;
        }

        .usage-table th {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .usage-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s ease;
        }

        .usage-table tbody tr:hover {
            background: #f8f9fa;
        }

        .usage-table tbody tr:last-child {
            border-bottom: none;
        }

        .no-usage {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container h1 {
                font-size: 1.6rem;
                text-align: center;
            }

            nav ul {
                gap: 10px;
                flex-wrap: wrap;
                justify-content: center;
            }

            main {
                padding: 20px;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .form-container {
                padding: 25px 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .usage-table {
                font-size: 0.85rem;
            }

            .usage-table th,
            .usage-table td {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <?php echo generate_navigation(); ?>
    </header>

    <main>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-edit"></i> Edit Inventory Batch</h1>
                <p>Update vaccine batch information</p>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div class="form-container">
            <!-- Current Stock Info -->
            <div class="info-box">
                <i class="fas fa-box"></i>
                <div class="info-content">
                    <div class="info-label">Current Available Stock</div>
                    <div class="info-value"><?php echo $item['current_quantity']; ?> vials</div>
                </div>
            </div>

            <?php if ($item['days_until_expiry'] <= 30): ?>
                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="warning-content">
                        <div class="warning-title">Expiry Warning</div>
                        <div class="warning-text">
                            This batch will expire in <?php echo $item['days_until_expiry']; ?> days. 
                            Please prioritize using this batch first.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST" action="">
                <div class="form-section">
                    <h3><i class="fas fa-syringe"></i> Vaccine Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="vaccine_name">
                                Vaccine Name <span class="required">*</span>
                            </label>
                            <input type="text" id="vaccine_name" name="vaccine_name" 
                                   value="<?php echo htmlspecialchars($item['vaccine_name']); ?>" 
                                   required placeholder="e.g., Verorab, Rabipur">
                        </div>

                        <div class="form-group">
                            <label for="vaccine_type">
                                Vaccine Type <span class="required">*</span>
                            </label>
                            <select id="vaccine_type" name="vaccine_type" required>
                                <option value="">Select Type</option>
                                <?php
                                $types = get_vaccine_types();
                                foreach ($types as $code => $name) {
                                    $selected = ($item['vaccine_type'] === $code) ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$code - $name</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="batch_id">
                                Batch ID / Serial Number <span class="required">*</span>
                            </label>
                            <input type="text" id="batch_id" name="batch_id" 
                                   value="<?php echo htmlspecialchars($item['batch_id']); ?>" 
                                   required placeholder="e.g., VRB2024001">
                        </div>

                        <div class="form-group">
                            <label for="current_quantity">
                                Current Quantity <span class="required">*</span>
                            </label>
                            <input type="number" id="current_quantity" name="current_quantity" 
                                   value="<?php echo $item['current_quantity']; ?>" 
                                   required min="0" placeholder="Number of vials">
                        </div>

                        <div class="form-group full-width">
                            <label for="expiry_date">
                                Expiry Date <span class="required">*</span>
                            </label>
                            <input type="date" id="expiry_date" name="expiry_date" 
                                   value="<?php echo $item['expiry_date']; ?>" 
                                   required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="inventory.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i>
                        Delete Batch
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Usage History -->
            <div class="usage-history">
                <h3><i class="fas fa-history"></i> Usage History</h3>
                <?php
                $usage_history = get_batch_usage_history($item['batch_id']);
                if (!empty($usage_history)):
                ?>
                    <table class="usage-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient ID</th>
                                <th>Used By</th>
                                <th>Quantity</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usage_history as $usage): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($usage['usage_date'])); ?></td>
                                    <td><?php echo $usage['patient_id'] ? htmlspecialchars($usage['patient_id']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($usage['used_by']); ?></td>
                                    <td><?php echo $usage['quantity_used']; ?></td>
                                    <td><?php echo htmlspecialchars($usage['purpose'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-usage">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                        <p>No usage history for this batch yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this batch? This will mark it as depleted and cannot be undone.')) {
                window.location.href = 'delete_inventory.php?id=<?php echo $id; ?>';
            }
        }

        // Add visual feedback for expiry date selection
        document.getElementById('expiry_date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            const diffTime = selectedDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays <= 0) {
                alert('Warning: The selected expiry date has already passed!');
                this.style.borderColor = '#dc3545';
            } else if (diffDays <= 30) {
                alert('Warning: This batch will expire in ' + diffDays + ' days.');
                this.style.borderColor = '#ffc107';
            } else {
                this.style.borderColor = '#4a8f5f';
            }
        });

        // Prevent negative quantity
        document.getElementById('current_quantity').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    </script>
</body>

</html>