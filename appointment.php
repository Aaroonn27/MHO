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
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles (same as homepage) */
        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.15);
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
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
            gap: 30px;
            list-style: none;
            align-items: center;
        }

        nav ul li a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
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
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-title p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        /* SMS Panel */
        .sms-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .panel-header h3 {
            font-size: 1.5rem;
            color: #333;
            font-weight: 700;
        }

        .panel-header h3 i {
            color: #667eea;
            margin-right: 10px;
        }

        .balance-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .sms-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .sms-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sms-btn.primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .sms-btn.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .sms-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .sms-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Content Container */
        .content-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .content-header h2 {
            font-size: 1.8rem;
            color: #333;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .action-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .action-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e9ecef;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* Sort Dropdown */
        .sort-dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            min-width: 220px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 5px;
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 5px;
        }

        .dropdown-content a:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .dropdown-content.show {
            display: block;
            animation: slideDown 0.3s ease;
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

        /* Table Styles */
        .appointment-table-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .table-header {
            display: grid;
            grid-template-columns: 2fr 1.8fr 1.5fr 2fr 1.2fr;
            gap: 15px;
            padding: 20px 25px;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            border-bottom: 2px solid #e9ecef;
        }

        .header-cell {
            font-weight: 700;
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-cell i {
            color: #667eea;
            font-size: 16px;
        }

        .table-body {
            min-height: 200px;
            background: white;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1.8fr 1.5fr 2fr 1.2fr;
            gap: 15px;
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
            transition: all 0.3s ease;
        }

        .table-row:hover {
            background: #f8f9ff;
            transform: translateX(5px);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        /* SMS Status Badges */
        .sms-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            color: white;
        }

        .status-sent {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .status-failed {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .sms-action-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .sms-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .sms-action-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 15px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .notification.error {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            main {
                padding: 30px 20px;
            }

            .table-header,
            .table-row {
                grid-template-columns: 2fr 1.5fr 1.5fr 1.8fr 1fr;
                gap: 10px;
                padding: 15px 20px;
            }
        }

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

            .content-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .panel-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .sms-controls {
                width: 100%;
                justify-content: center;
            }

            .table-header,
            .table-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .header-cell,
            .table-row > div {
                padding: 5px 0;
            }
        }
    </style>
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