<?php
// Include database functions
include_once 'cslip_function.php';

// Process form submission
save_charge_slip();

// Check if we're viewing a specific charge slip
$viewing_slip = false;
$current_slip = null;

if (isset($_GET['id'])) {
    $current_slip = get_charge_slip($_GET['id']);
    if ($current_slip) {
        $viewing_slip = true;
    }
}

// Get charge slip history for the history button
$history = get_charge_slip_history();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Slip</title>
    <link rel="stylesheet" href="Style/chargeslip.css">
    <style>
        /* Additional print styles */
        @media print {
            .no-print {
                display: none;
            }
            .print-section {
                padding: 0;
                margin: 0;
            }
            body {
                background-color: #def;
            }
            .charge-slip-container {
                border: none;
                box-shadow: none;
            }
        }
        
        /* Styles for the printed charge slip format */
        .printed-charge-slip {
            background-color: #def;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }
        
        .printed-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .printed-header img {
            height: 60px;
            margin: 0 10px;
        }
        
        .printed-header h3 {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .printed-header h2 {
            margin: 10px 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .printed-form {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
        }
        
        .printed-form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .printed-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .printed-table th, .printed-table td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: center;
        }
        
        .printed-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .total-row {
            text-align: right;
            padding: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="charge-slip-container">
        <?php display_status_messages(); ?>

        <?php if ($viewing_slip): ?>
            <!-- Display the charge slip for viewing/printing -->
            <div class="print-section">
                <div class="printed-charge-slip">
                    <div class="printed-header">
                        <div style="display: flex; justify-content: center; align-items: center;">
                            <img src="/MHO/media/sanpablologo.png" alt="San Pablo City Logo">
                            <div style="text-align: center; margin: 0 10px;">
                                <h3>Republic of the Philippines</h3>
                                <h3>OFFICE OF THE CITY HEALTH OFFICER</h3>
                                <h3>San Pablo City</h3>
                            </div>
                            <img src="/MHO/media/chologo.png" alt="CHO Logo">
                        </div>
                        <h2>CHARGE SLIP</h2>
                    </div>
                    
                    <div class="printed-form">
                        <div class="printed-form-row">
                            <div>
                                <strong>Name:</strong> <?php echo htmlspecialchars($current_slip['fname'] . ' ' . $current_slip['mname'] . ' ' . $current_slip['lname']); ?>
                            </div>
                            <div>
                                <strong>Date/Time:</strong> <?php echo $current_slip['timeanddate']; ?>
                            </div>
                        </div>
                        
                        <table class="printed-table">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">SERVICES</th>
                                    <th style="width: 20%;">QUANTITY</th>
                                    <th style="width: 20%;">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($current_slip['services']); ?></td>
                                    <td>1</td>
                                    <td>
                                        <?php 
                                        // Get the service amount from the service price function
                                        $amount = get_service_price($current_slip['services']); 
                                        echo "₱" . number_format($amount, 2);
                                        
                                        // Calculate discount if applicable
                                        $discountAmount = 0;
                                        if ($current_slip['discount'] > 0) {
                                            $discountAmount = $amount * ($current_slip['discount'] / 100);
                                            $amount -= $discountAmount;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php if ($current_slip['discount'] > 0): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        if ($current_slip['discount'] == 20) echo "Senior Citizen Discount";
                                        else if ($current_slip['discount'] == 15) echo "PWD Discount";
                                        else if ($current_slip['discount'] == 10) echo "Other Discount";
                                        ?> (<?php echo $current_slip['discount']; ?>%)
                                    </td>
                                    <td>-</td>
                                    <td>-₱<?php echo number_format($discountAmount, 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="2" class="total-row">Total:</td>
                                    <td>₱<?php echo number_format($amount, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="button-row no-print">
                    <button class="button back-btn" onclick="window.print()">Print</button>
                    <button class="button back-btn" onclick="window.location.href='charge_slip.php'">Back</button>
                </div>
            </div>
        <?php else: ?>
            <!-- Form for creating a new charge slip -->
            <div class="charge-slip-header">CHARGE SLIP</div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="charge-slip-form">
                <div class="section services-section">
                    <div class="section-header">SERVICES</div>
                    <div class="services-list">
                        <?php foreach (get_service_options() as $service): ?>
                            <div class="service-item">
                                <label>
                                    <input type="radio" name="services" value="<?php echo $service; ?>" required>
                                    <?php echo $service; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="section name-section">
                    <div class="section-header">NAME</div>
                    <div class="name-fields">
                        <div class="field-group">
                            <label for="fname">First Name:</label>
                            <input type="text" id="fname" name="fname" required>
                        </div>
                        <div class="field-group">
                            <label for="mname">Middle Name:</label>
                            <input type="text" id="mname" name="mname">
                        </div>
                        <div class="field-group">
                            <label for="lname">Last Name:</label>
                            <input type="text" id="lname" name="lname" required>
                        </div>
                    </div>
                </div>

                <div class="section discount-section">
                    <div class="section-header">DISCOUNT</div>
                    <div class="discount-options">
                        <div class="discount-option">
                            <label>
                                <input type="radio" name="discount" value="senior">
                                SENIOR CITIZEN
                            </label>
                        </div>
                        <div class="discount-option">
                            <label>
                                <input type="radio" name="discount" value="pwd">
                                PWD
                            </label>
                        </div>
                        <div class="discount-option">
                            <label>
                                <input type="radio" name="discount" value="others">
                                Others
                            </label>
                        </div>
                        <div class="discount-option">
                            <label>
                                <input type="radio" name="discount" value="none" checked>
                                None
                            </label>
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" name="generate" class="button generate-btn">Generate</button>
                    <button type="button" onclick="window.history.back()" class="button back-btn">Back</button>
                </div>
            </form>

            <!-- History button -->
            <button id="historyBtn" class="history-btn" onclick="openHistoryModal()">
                <div class="history-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z" />
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z" />
                    </svg>
                </div>
                <span class="history-text">History</span>
            </button>

            <!-- History modal -->
            <div id="historyModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeHistoryModal()">&times;</span>
                    <h2>Charge Slip History</h2>
                    <?php if (!empty($history)): ?>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Services</th>
                                    <th>Discount</th>
                                    <th>Date/Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $slip): ?>
                                    <tr>
                                        <td><?php echo $slip['id']; ?></td>
                                        <td><?php echo htmlspecialchars($slip['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($slip['services']); ?></td>
                                        <td><?php echo $slip['discount']; ?>%</td>
                                        <td><?php echo $slip['timeanddate']; ?></td>
                                        <td>
                                            <a href="charge_slip.php?id=<?php echo $slip['id']; ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No charge slip history found.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Modal scripts
        const historyModal = document.getElementById("historyModal");

        function openHistoryModal() {
            historyModal.style.display = "block";
        }

        function closeHistoryModal() {
            historyModal.style.display = "none";
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            if (event.target == historyModal) {
                historyModal.style.display = "none";
            }
        }
    </script>
</body>

</html>