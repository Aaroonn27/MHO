<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>

<body>
    <!-- Header (same as homepage) -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i><span>Home</span></a></li>
                <li><a href="appointment.php" class="active"><i class="far fa-calendar-alt"></i><span>Appointment</span></a>
                </li>
                <li><a href="charge_slip.php"><i class="fas fa-file-invoice"></i><span>Charge Slip</span></a></li>
                <li><a href="inventory.php"><i class="fas fa-box"></i><span>Inventory</span></a></li>
                <li><a href="rabies_registry.php"><i class="fas fa-user-md"></i><span>Patient Record</span></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="far fa-calendar-alt"></i> Appointment Management</h1>
                <p>Manage and schedule patient appointments efficiently</p>
            </div>
        </div>

        <!-- SMS Control Panel -->
        <div class="sms-panel">
            <div class="panel-header">
                <h3><i class="fas fa-sms"></i> SMS Notification Center</h3>
                <div class="balance-info" id="balanceInfo">
                    <i class="fas fa-wallet"></i> Balance: Loading...
                </div>
            </div>
            <div class="sms-controls">
                <button class="sms-btn primary" onclick="sendAllReminders()">
                    <i class="fas fa-paper-plane"></i> Send All Reminders
                </button>
                <button class="sms-btn secondary" onclick="checkBalance()">
                    <i class="fas fa-sync-alt"></i> Refresh Balance
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-container">
            <div class="content-header">
                <h2>Scheduled Appointments</h2>
                <div class="header-actions">
                    <button class="action-btn primary" onclick="window.location.href='create_appoint.php'">
                        <i class="fas fa-plus"></i> New Appointment
                    </button>
                    <div class="sort-dropdown">
                        <button class="action-btn secondary sort-btn">
                            <i class="fas fa-sort"></i> Sort By <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="?sort=date_asc"><i class="fas fa-calendar-plus"></i> Date (Recent First)</a>
                            <a href="?sort=date_desc"><i class="fas fa-calendar-minus"></i> Date (Oldest First)</a>
                            <a href="?sort=name_asc"><i class="fas fa-sort-alpha-down"></i> Name (A-Z)</a>
                            <a href="?sort=name_desc"><i class="fas fa-sort-alpha-up"></i> Name (Z-A)</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="appointment-table-container">
                <div class="table-header">
                    <div class="header-cell">
                        <i class="fas fa-user"></i> Patient Name
                    </div>
                    <div class="header-cell">
                        <i class="fas fa-calendar"></i> Date & Time
                    </div>
                    <div class="header-cell">
                        <i class="fas fa-phone"></i> Contact
                    </div>
                    <div class="header-cell">
                        <i class="fas fa-procedures"></i> Program
                    </div>
                    <div class="header-cell">
                        <i class="fas fa-sms"></i> SMS Status
                    </div>
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