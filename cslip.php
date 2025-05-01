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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="Style/cslip.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="charge-slip-container">
        <div class="charge-slip-header">CHARGE SLIP</div>

        <?php display_status_messages(); ?>

        <?php if ($viewing_slip): ?>
            <!-- Display the charge slip for viewing/printing -->
            <div class="print-section">
                <div class="charge-slip-form">
                    <div class="section services-section">
                        <div class="section-header">SERVICES</div>
                        <div><?php echo htmlspecialchars($current_slip['services']); ?></div>
                    </div>

                    <div class="section name-section">
                        <div class="section-header">NAME</div>
                        <div>
                            <strong>First Name:</strong> <?php echo htmlspecialchars($current_slip['fname']); ?>
                        </div>
                        <div>
                            <strong>Middle Name:</strong> <?php echo htmlspecialchars($current_slip['mname']); ?>
                        </div>
                        <div>
                            <strong>Last Name:</strong> <?php echo htmlspecialchars($current_slip['lname']); ?>
                        </div>
                    </div>

                    <div class="section discount-section">
                        <div class="section-header">DISCOUNT</div>
                        <strong><?php
                                if ($current_slip['discount'] == 20) echo "SENIOR CITIZEN";
                                else if ($current_slip['discount'] == 15) echo "PWD";
                                else if ($current_slip['discount'] == 10) echo "Others";
                                else echo "None";
                                ?></strong>
                        <div>
                            <strong>Discount Rate:</strong> <?php echo $current_slip['discount']; ?>%
                        </div>
                        <div>
                            <strong>Date/Time:</strong> <?php echo $current_slip['timeanddate']; ?>
                        </div>
                    </div>
                </div>

                <div class="button-row no-print">
                    <button class="button back-btn" onclick="window.print()">Print</button>
                    <button class="button back-btn" onclick="window.location.href='charge_slip.php'">Back</button>
                </div>
            </div>
        <?php else: ?>
            <!-- Form for creating a new charge slip -->
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