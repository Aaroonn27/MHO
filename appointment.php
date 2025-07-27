<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Page - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="Style/appointment.css">
    <link rel="stylesheet" href="Style/headerstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* SMS Panel Styles */
        .sms-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            color: white;
        }
        
        .sms-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .sms-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .sms-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .sms-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .balance-info {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        /* Enhanced table styles */
        .table-header {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1fr;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1fr;
            gap: 10px;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            align-items: center;
        }
        
        .table-row:hover {
            background: #f8f9fa;
        }
        
        .sms-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-sent {
            background: #d1edff;
            color: #0c5460;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .sms-action-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .sms-action-btn:hover {
            background: #0056b3;
        }
        
        .sms-action-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #28a745;
        }
        
        .notification.error {
            background: #dc3545;
        }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="appointment-container">
            <div class="appointment-header">
                <h1>APPOINTMENT</h1>
                <div class="header-actions">
                    <button id="addapp" onclick="window.location.href='create_appoint.php'">
                        <i class="fas fa-plus"></i> Add Appointment
                    </button>
                    <div class="sort-dropdown">
                        <button class="sort-btn">
                            Sort By <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="?sort=date_asc">Date (Ascending)</a>
                            <a href="?sort=date_desc">Date (Descending)</a>
                            <a href="?sort=name_asc">Name (A-Z)</a>
                            <a href="?sort=name_desc">Name (Z-A)</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Control Panel -->
            <div class="sms-panel">
                <h3><i class="fas fa-sms"></i> SMS Notification Center</h3>
                <div class="sms-controls">
                    <button class="sms-btn" onclick="sendAllReminders()">
                        <i class="fas fa-paper-plane"></i> Send All Reminders
                    </button>
                    <button class="sms-btn" onclick="checkBalance()">
                        <i class="fas fa-wallet"></i> Check Balance
                    </button>
                    <div class="balance-info" id="balanceInfo">
                        Balance: Loading...
                    </div>
                </div>
            </div>

            <div class="appointment-table">
                <div class="table-header">
                    <div>Name</div>
                    <div>Date</div>
                    <div>Contact</div>
                    <div>Program</div>
                    <div>SMS Status</div>
                </div>
                <div class="table-body">
                    <?php
                    include 'db_conn.php';
                    fetch_appointments_with_sms();
                    ?>
                </div>
            </div>
        </div>

        <!-- Notification -->
        <div id="notification" class="notification"></div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get dropdown elements
            const sortBtn = document.querySelector('.sort-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (sortBtn) {
                sortBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownContent.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                window.addEventListener('click', function() {
                    if (dropdownContent.classList.contains('show')) {
                        dropdownContent.classList.remove('show');
                    }
                });
            }

            // Load initial balance
            checkBalance();
        });

        // Send individual SMS reminder
        function sendIndividualSMS(appointmentId, buttonElement) {
            buttonElement.disabled = true;
            buttonElement.innerHTML = '<div class="loading"></div>Sending...';
            
            fetch('send_sms.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=send_individual&appointment_id=' + appointmentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Update button and status
                    buttonElement.innerHTML = 'Sent';
                    buttonElement.disabled = true;
                    // Update status indicator
                    const statusElement = buttonElement.closest('.table-row').querySelector('.sms-status');
                    statusElement.className = 'sms-status status-sent';
                    statusElement.textContent = 'SENT';
                } else {
                    showNotification(data.message, 'error');
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = 'Send SMS';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to send SMS', 'error');
                buttonElement.disabled = false;
                buttonElement.innerHTML = 'Send SMS';
            });
        }

        // Send all reminders
        function sendAllReminders() {
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<div class="loading"></div>Sending All...';
            
            fetch('send_sms.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=send_all'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Refresh the page to update statuses
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(data.message, 'error');
                }
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-paper-plane"></i> Send All Reminders';
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to send reminders', 'error');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-paper-plane"></i> Send All Reminders';
            });
        }

        // Check SMS balance
        function checkBalance() {
            fetch('send_sms.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_balance'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.balance) {
                    const balanceInfo = document.getElementById('balanceInfo');
                    if (data.balance.credits !== undefined) {
                        balanceInfo.textContent = `Balance: ${data.balance.credits} credits`;
                    } else {
                        balanceInfo.textContent = 'Balance: Available';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking balance:', error);
                document.getElementById('balanceInfo').textContent = 'Balance: Error loading';
            });
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');

            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }
    </script>
</body>

</html>