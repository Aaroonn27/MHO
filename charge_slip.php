<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Slip - City Health Office of San Pablo</title>
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

        /* Header Styles */
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
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        nav ul li a.active {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        nav ul li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        nav ul li a:hover::before {
            left: 100%;
        }

        nav ul li a:hover {
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

        /* Page Title Section */
        .page-title-section {
            padding: 60px 40px 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(15px);
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .page-title-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .page-title-content {
            position: relative;
            z-index: 2;
        }

        .page-title-section h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
        }

        .page-title-section p {
            font-size: 1.2rem;
            opacity: 0.95;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Content */
        .main-content {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Charge Slip Container */
        .charge-slip-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .charge-slip-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
        }

        /* Form Styles */
        .charge-slip-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .form-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .form-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
        }

        .section-icon i {
            font-size: 20px;
            color: white;
        }

        .section-header h3 {
            font-size: 1.4rem;
            color: #333;
            font-weight: 700;
        }

        /* Services Section */
        .services-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .service-item {
            position: relative;
        }

        .service-item label {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .service-item label:hover {
            background: linear-gradient(135deg, #f0f3ff 0%, #e6ebff 100%);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateX(5px);
        }

        .service-item input[type="radio"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .service-item input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            transform: translateX(5px);
        }

        /* Name Fields */
        .name-fields {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-group label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }

        .field-group input {
            padding: 12px 16px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .field-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        /* Discount Options */
        .discount-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .discount-option {
            position: relative;
        }

        .discount-option label {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .discount-option label:hover {
            background: linear-gradient(135deg, #f0f3ff 0%, #e6ebff 100%);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateX(5px);
        }

        .discount-option input[type="radio"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .discount-option input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            transform: translateX(5px);
        }

        /* Button Styles */
        .button-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(102, 126, 234, 0.1);
        }

        .button {
            padding: 15px 35px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .button:hover::before {
            left: 100%;
        }

        .generate-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .generate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        }

        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(108, 117, 125, 0.4);
        }

        /* History Button */
        .history-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        .history-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }

        .history-btn i {
            font-size: 24px;
            margin-bottom: 4px;
        }

        @keyframes pulse {
            0% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }
            50% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6), 0 0 0 0 rgba(102, 126, 234, 0.4); }
            100% { box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4), 0 0 0 20px rgba(102, 126, 234, 0); }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 800px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            border-radius: 20px 20px 0 0;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .modal h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        /* History Table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .history-table th,
        .history-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }

        .history-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table td {
            background: white;
            transition: all 0.3s ease;
        }

        .history-table tr:hover td {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
        }

        .history-table a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        .history-table a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Print Styles */
        .printed-charge-slip {
            background: white;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .printed-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }

        .printed-header img {
            height: 80px;
            margin: 0 15px;
        }

        .printed-header h3 {
            margin: 8px 0;
            font-size: 16px;
            color: #333;
        }

        .printed-header h2 {
            margin: 20px 0;
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }

        .printed-form {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #667eea;
        }

        .printed-form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .printed-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .printed-table th,
        .printed-table td {
            border: 2px solid #667eea;
            padding: 12px 15px;
            text-align: center;
            font-size: 16px;
        }

        .printed-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
        }

        .total-row {
            text-align: right;
            padding: 15px;
            font-weight: bold;
            font-size: 18px;
            background: linear-gradient(135deg, #f0f3ff 0%, #e6ebff 100%);
        }

        /* Print Media Query */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-section, .print-section * {
                visibility: visible;
            }
            
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .no-print {
                display: none;
            }

            .printed-charge-slip {
                box-shadow: none;
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charge-slip-form {
                grid-template-columns: 1fr;
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

            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li a {
                padding: 8px 12px;
            }

            nav ul li a i {
                font-size: 18px;
            }

            nav ul li a span {
                font-size: 11px;
            }

            .page-title-section {
                padding: 40px 20px 30px;
            }

            .page-title-section h1 {
                font-size: 2.2rem;
            }

            .main-content {
                padding: 40px 20px;
            }

            .charge-slip-container {
                padding: 25px 20px;
            }

            .button-row {
                flex-direction: column;
                gap: 15px;
            }

            .button {
                width: 100%;
            }

            .history-btn {
                bottom: 20px;
                left: 20px;
                width: 60px;
                height: 60px;
            }

            .history-btn i {
                font-size: 20px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .logo-container h1 {
                font-size: 1.4rem;
            }

            .page-title-section h1 {
                font-size: 1.8rem;
            }

            .form-section {
                padding: 20px;
            }

            .section-header h3 {
                font-size: 1.2rem;
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
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i><span>Home</span></a></li>
                <li><a href="appointment.php"><i class="far fa-calendar-alt"></i><span>Appointment</span></a></li>
                <li><a href="charge_slip.php" class="active"><i class="fas fa-file-invoice"></i><span>Charge Slip</span></a></li>
                <li><a href="inventory.php"><i class="fas fa-box"></i><span>Inventory</span></a></li>
                <li><a href="rabies_registry.php"><i class="fas fa-user-md"></i><span>Patient Record</span></a></li>
            </ul>
        </nav>
    </header>

    <!-- Page Title Section -->
    <section class="page-title-section">
        <div class="page-title-content">
            <h1>CHARGE SLIP</h1>
            <p>Generate professional charge slips for healthcare services with ease</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content">
        <div class="charge-slip-container">
            <?php if (isset($viewing_slip) && $viewing_slip): ?>
                <!-- Display the charge slip for viewing/printing -->
                <div class="print-section">
                    <div class="printed-charge-slip">
                        <div class="printed-header">
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <img src="/MHO/media/sanpablologo.png" alt="San Pablo City Logo">
                                <div style="text-align: center; margin: 0 15px;">
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
                                    <strong>Name:</strong> <?php echo isset($current_slip) ? htmlspecialchars($current_slip['fname'] . ' ' . $current_slip['mname'] . ' ' . $current_slip['lname']) : 'John Doe Sample'; ?>
                                </div>
                                <div>
                                    <strong>Date/Time:</strong> <?php echo isset($current_slip) ? $current_slip['timeanddate'] : date('Y-m-d H:i:s'); ?>
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
                                        <td><?php echo isset($current_slip) ? htmlspecialchars($current_slip['services']) : 'Health Certificate'; ?></td>
                                        <td>1</td>
                                        <td>₱150.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="total-row">Total:</td>
                                        <td>₱150.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="button-row no-print">
                        <button class="button generate-btn" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="button back-btn" onclick="window.location.href='charge_slip.php'">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Form for creating a new charge slip -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="charge-slip-form">
                    <!-- Services Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <h3>SERVICES</h3>
                        </div>
                        <div class="services-list">
                            <div class="service-item">
                                <input type="radio" id="health_cert" name="services" value="Health Certificate for Workers" required>
                                <label for="health_cert">Health Certificate for Workers</label>
                            </div>
                            <div class="service-item">
                                <input type="radio" id="medical_cert_emp" name="services" value="Medical Certificate for Employment">
                                <label for="medical_cert_emp">Medical Certificate for Employment</label>
                            </div>
                            <div class="service-item">
                                <input type="radio" id="tricycle_cert" name="services" value="Tricycle Driver Medical Certificate">
                                <label for="tricycle_cert">Tricycle Driver Medical Certificate</label>
                            </div>
                            <div class="service-item">
                                <input type="radio" id="medical_cert_leave" name="services" value="Medical Certificate for Leave">
                                <label for="medical_cert_leave">Medical Certificate for Leave</label>
                            </div>
                            <div class="service-item">
                                <input type="radio" id="pwd_cert" name="services" value="PWD Medical Certificate">
                                <label for="pwd_cert">PWD Medical Certificate</label>
                            </div>
                        </div>
                    </div>

                    <!-- Name Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3>PATIENT INFORMATION</h3>
                        </div>
                        <div class="name-fields">
                            <div class="field-group">
                                <label for="fname">First Name *</label>
                                <input type="text" id="fname" name="fname" required placeholder="Enter first name">
                            </div>
                            <div class="field-group">
                                <label for="mname">Middle Name</label>
                                <input type="text" id="mname" name="mname" placeholder="Enter middle name">
                            </div>
                            <div class="field-group">
                                <label for="lname">Last Name *</label>
                                <input type="text" id="lname" name="lname" required placeholder="Enter last name">
                            </div>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-percent"></i>
                            </div>
                            <h3>DISCOUNTS</h3>
                        </div>
                        <div class="discount-options">
                            <div class="discount-option">
                                <input type="radio" id="senior" name="discount" value="senior">
                                <label for="senior">Senior Citizen (20% off)</label>
                            </div>
                            <div class="discount-option">
                                <input type="radio" id="pwd" name="discount" value="pwd">
                                <label for="pwd">PWD (15% off)</label>
                            </div>
                            <div class="discount-option">
                                <input type="radio" id="others" name="discount" value="others">
                                <label for="others">Others (10% off)</label>
                            </div>
                            <div class="discount-option">
                                <input type="radio" id="none" name="discount" value="none" checked>
                                <label for="none">No Discount</label>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Action Buttons -->
                <div class="button-row">
                    <button type="submit" name="generate" class="button generate-btn" form="charge-slip-form">
                        <i class="fas fa-file-plus"></i> Generate Charge Slip
                    </button>
                    <button type="button" onclick="window.history.back()" class="button back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </button>
                </div>

                <!-- History Button -->
                <button id="historyBtn" class="history-btn" onclick="openHistoryModal()">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </button>

                <!-- History Modal -->
                <div id="historyModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeHistoryModal()">&times;</span>
                        <h2><i class="fas fa-history"></i> Charge Slip History</h2>
                        <?php if (isset($history) && !empty($history)): ?>
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Service</th>
                                        <th>Discount</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
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
                                                <a href="charge_slip.php?id=<?php echo $slip['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p style="font-size: 18px;">No charge slip history found.</p>
                                <p style="opacity: 0.7;">Create your first charge slip to see it here!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="successMessage" style="position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3); z-index: 3000; animation: slideInRight 0.5s ease;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="errorMessage" style="position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3); z-index: 3000; animation: slideInRight 0.5s ease;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <script>
        // Add form ID to the form element
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.charge-slip-form');
            if (form) {
                form.id = 'charge-slip-form';
            }

            // Enhanced radio button interactions
            const serviceRadios = document.querySelectorAll('input[name="services"]');
            const discountRadios = document.querySelectorAll('input[name="discount"]');

            serviceRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all service labels
                    serviceRadios.forEach(r => {
                        const label = r.nextElementSibling;
                        if (label) {
                            label.style.background = 'white';
                            label.style.color = '#333';
                            label.style.transform = 'translateX(0)';
                        }
                    });

                    // Add active class to selected service
                    if (this.checked) {
                        const label = this.nextElementSibling;
                        if (label) {
                            label.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                            label.style.color = 'white';
                            label.style.transform = 'translateX(5px)';
                        }
                    }
                });
            });

            discountRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all discount labels
                    discountRadios.forEach(r => {
                        const label = r.nextElementSibling;
                        if (label) {
                            label.style.background = 'white';
                            label.style.color = '#333';
                            label.style.transform = 'translateX(0)';
                        }
                    });

                    // Add active class to selected discount
                    if (this.checked) {
                        const label = this.nextElementSibling;
                        if (label) {
                            label.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                            label.style.color = 'white';
                            label.style.transform = 'translateX(5px)';
                        }
                    }
                });
            });

            // Auto-hide status messages
            setTimeout(() => {
                const successMsg = document.getElementById('successMessage');
                const errorMsg = document.getElementById('errorMessage');
                
                if (successMsg) {
                    successMsg.style.animation = 'slideOutRight 0.5s ease';
                    setTimeout(() => successMsg.remove(), 500);
                }
                
                if (errorMsg) {
                    errorMsg.style.animation = 'slideOutRight 0.5s ease';
                    setTimeout(() => errorMsg.remove(), 500);
                }
            }, 5000);
        });

        // Modal functionality
        const historyModal = document.getElementById("historyModal");

        function openHistoryModal() {
            historyModal.style.display = "block";
            document.body.style.overflow = 'hidden';
        }

        function closeHistoryModal() {
            historyModal.style.display = "none";
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == historyModal) {
                closeHistoryModal();
            }
        }

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && historyModal.style.display === 'block') {
                closeHistoryModal();
            }
        });

        // Form validation enhancement
        document.getElementById('charge-slip-form')?.addEventListener('submit', function(e) {
            const fname = document.getElementById('fname').value.trim();
            const lname = document.getElementById('lname').value.trim();
            const service = document.querySelector('input[name="services"]:checked');

            if (!fname || !lname || !service) {
                e.preventDefault();
                alert('Please fill in all required fields and select a service.');
                return false;
            }

            // Add loading state to button
            const submitBtn = document.querySelector('.generate-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            submitBtn.disabled = true;

            // Reset button after a delay (in case form submission fails)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>