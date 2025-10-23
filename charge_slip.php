<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'cho_employee'];
check_page_access($required_roles);

include_once 'cslip_function.php';

// Process form submission
save_charge_slip();

// Get logged-in user details from session (already set by auth.php)
$current_user = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['full_name'])) {
    $current_user = [
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'] ?? 'cho_employee'
    ];
}

// Check if viewing a specific slip
$viewing_slip = false;
$current_slip = null;
if (isset($_GET['id'])) {
    $current_slip = get_charge_slip($_GET['id']);
    if ($current_slip) {
        $viewing_slip = true;
    }
}

// Get charge slip history
$history = get_charge_slip_history();
?>

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
            overflow: hidden;
        }

        nav ul li a.active {
            background: rgba(74, 143, 95, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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

        /* Page Title Section */
        .page-title-section {
            padding: 60px 40px 40px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            text-align: center;
            color: white;
            border-bottom: 3px solid #4a8f5f;
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

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
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
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            border-top: 4px solid #2d5f3f;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        /* Form Styles */
        .charge-slip-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .form-row-container {
            display: contents;
        }

        .form-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #a5d6a7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
            background: linear-gradient(135deg, #2d5f3f 0%, #4a8f5f 100%);
        }

        .form-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(45, 95, 63, 0.15);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(74, 143, 95, 0.2);
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.3);
        }

        .section-icon i {
            font-size: 20px;
            color: white;
        }

        .section-header h3 {
            font-size: 1.4rem;
            color: #2d5f3f;
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
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .service-item label:hover {
            background: #f8fdf9;
            border-color: rgba(74, 143, 95, 0.3);
            transform: translateX(5px);
        }

        .service-item input[type="radio"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            accent-color: #2d5f3f;
        }

        .service-item input[type="radio"]:checked+label {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            border-color: #2d5f3f;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.3);
        }

        /* Others input field */
        .others-input {
            display: none;
            margin-top: 10px;
            padding: 12px 16px;
            border: 2px solid rgba(74, 143, 95, 0.2);
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            background: white;
        }

        .others-input.show {
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
            color: #2d5f3f;
            font-size: 14px;
        }

        .field-group input {
            padding: 12px 16px;
            border: 2px solid rgba(74, 143, 95, 0.2);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .field-group input:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
            transform: translateY(-2px);
        }

        /* Quantity Section */
        .quantity-section {
            grid-column: 1 / -1;
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .quantity-section .form-section {
            width: auto;
            max-width: 500px;
        }

        .quantity-section .section-header {
            justify-content: center;
        }

        .quantity-section .field-group {
            align-items: center;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
        }

        .quantity-input button {
            width: 50px;
            height: 50px;
            border: 2px solid #2d5f3f;
            background: white;
            color: #2d5f3f;
            border-radius: 8px;
            cursor: pointer;
            font-size: 24px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .quantity-input button:hover {
            background: #2d5f3f;
            color: white;
            transform: scale(1.1);
        }

        .quantity-input input {
            width: 100px;
            text-align: center;
            padding: 15px;
            border: 2px solid rgba(74, 143, 95, 0.2);
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
        }

        .quantity-input input:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .quantity-label {
            font-size: 18px;
            font-weight: 600;
            color: #2d5f3f;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Button Styles */
        .button-row {
            display: flex !important;
            justify-content: center !important;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
            width: 100%;
        }

        .charge-slip-form .button-row {
            grid-column: 1 / -1;
        }

        .button {
            padding: 15px 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
        }

        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 95, 63, 0.3);
        }

        .back-btn {
            background: white;
            color: #2d5f3f;
            border: 2px solid #4a8f5f;
        }

        .back-btn:hover {
            background: #2d5f3f;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 95, 63, 0.3);
        }

        /* History Button */
        .history-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
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
            box-shadow: 0 4px 15px rgba(45, 95, 63, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        .history-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(45, 95, 63, 0.5);
        }

        .history-btn i {
            font-size: 24px;
            margin-bottom: 4px;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 4px 15px rgba(45, 95, 63, 0.4);
            }

            50% {
                box-shadow: 0 4px 15px rgba(45, 95, 63, 0.6), 0 0 0 0 rgba(45, 95, 63, 0.4);
            }

            100% {
                box-shadow: 0 4px 15px rgba(45, 95, 63, 0.4), 0 0 0 20px rgba(45, 95, 63, 0);
            }
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
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border-top: 4px solid #2d5f3f;
            position: relative;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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
            color: #2d5f3f;
            background: rgba(45, 95, 63, 0.1);
        }

        .modal h2 {
            color: #2d5f3f;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        .modal h2 i {
            color: #4a8f5f;
            margin-right: 10px;
        }

        /* History Table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .history-table th,
        .history-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .history-table th {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
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
            background: #f8fdf9;
        }

        .history-table a {
            color: #2d5f3f;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 15px;
            background: rgba(45, 95, 63, 0.1);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .history-table a:hover {
            background: #2d5f3f;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.3);
        }

        /* Print Styles */
        .print-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 10px;
            width: 100%;
        }

        .printed-charge-slip {
            background: white;
            padding: 12px;
            width: 100%;
            font-family: Arial, sans-serif;
            border: 2px solid #2d5f3f;
            border-radius: 6px;
            font-size: 10px;
            box-sizing: border-box;
        }

        .printed-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #2d5f3f;
        }

        .logo-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 6px;
        }

        .printed-header img {
            height: 32px;
            margin: 0 5px;
        }

        .header-text {
            text-align: center;
            margin: 0 8px;
        }

        .printed-header h3 {
            margin: 1px 0;
            font-size: 8px;
            color: #333;
            line-height: 1.2;
        }

        .printed-header h2 {
            margin: 6px 0 3px 0;
            font-size: 13px;
            font-weight: bold;
            color: #2d5f3f;
        }

        .printed-form {
            background: #f8fdf9;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #2d5f3f;
        }

        .printed-form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 8px;
            gap: 5px;
        }

        .printed-form-row>div {
            flex: 1;
        }

        .printed-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .printed-table th,
        .printed-table td {
            border: 1px solid #2d5f3f;
            padding: 4px 5px;
            text-align: center;
            font-size: 8px;
        }

        .printed-table th {
            background: #2d5f3f;
            color: white;
            font-weight: bold;
        }

        .total-row {
            text-align: right;
            padding: 4px;
            font-weight: bold;
            font-size: 9px;
            background: #e8f5e9;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
            padding-right: 8px;
        }

        .signature-box {
            text-align: center;
            min-width: 100px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 2px;
            padding-top: 15px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 8px;
            color: #000;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .signature-title {
            font-size: 7px;
            color: #000;
            margin-top: 1px;
        }

        /* Print Media Query */
        @media print {

            .no-print,
            .main-header,
            header,
            nav,
            .page-title-section,
            .logo-container,
            .button-row,
            #successMessage,
            #errorMessage {
                display: none !important;
            }

            * {
                box-sizing: border-box;
            }

            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .charge-slip-container {
                background: white !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .print-section {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .print-page {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                grid-auto-rows: auto !important;
                gap: 10px !important;
                padding: 8px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .printed-charge-slip {
                background: white !important;
                box-shadow: none !important;
                border: 2px solid #000 !important;
                padding: 10px !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                width: 100% !important;
            }

            .printed-header img {
                height: 30px !important;
            }

            .printed-table th {
                background: #2d5f3f !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .total-row {
                background: #e8f5e9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .signature-section {
                page-break-inside: avoid !important;
            }

            @page {
                size: letter;
                margin: 0.3in;
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
                gap: 10px;
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

            .history-table {
                font-size: 12px;
            }

            .history-table th,
            .history-table td {
                padding: 10px;
            }

            .signature-section {
                padding-right: 20px;
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
    <header class="main-header no-print">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <?php echo generate_navigation(); ?>
        </nav>
    </header>

    <!-- Page Title Section -->
    <section class="page-title-section no-print">
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
                    <div class="print-page">
                        <?php
                        // Generate charge slips based on quantity (max 6 per page)
                        $quantity = isset($current_slip['quantity']) ? (int)$current_slip['quantity'] : 1;
                        $slips_to_print = min($quantity, 6); // Maximum 6 per page (3x2 grid)

                        // Always print at least 1
                        $slips_to_print = max($slips_to_print, 1);

                        for ($i = 0; $i < $slips_to_print; $i++):
                        ?>
                            <div class="printed-charge-slip">
                                <div class="printed-header">
                                    <div class="logo-row">
                                        <img src="/MHO/media/sanpablologo.png" alt="San Pablo City Logo">
                                        <div class="header-text">
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
                                                <td>₱<?php echo number_format($current_slip['amount'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="total-row">Total:</td>
                                                <td>₱<?php echo number_format($current_slip['amount'], 2); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Signature Section -->
                                <div class="signature-section">
                                    <div class="signature-box">
                                        <div class="signature-line"></div>
                                        <div class="signature-name">
                                            <?php echo $current_user ? htmlspecialchars($current_user['full_name']) : 'CHO Employee'; ?>
                                        </div>
                                        <div class="signature-title">CHO Employee</div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
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
                <form method="POST" action="charge_slip.php" id="charge-slip-form">
                    <input type="hidden" name="generate" value="1">

                    <div class="charge-slip-form">
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
                                <div class="service-item">
                                    <input type="radio" id="others" name="services" value="">
                                    <label for="others">Others (Please Specify)</label>
                                    <input type="text" id="others_input" name="others_input" class="others-input" placeholder="Specify service...">
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

                        <!-- Quantity Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                                <h3>QUANTITY</h3>
                            </div>
                            <div class="field-group">
                                <label for="quantity">Number of Items</label>
                                <div class="quantity-input">
                                    <button type="button" onclick="decrementQuantity()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="100" required>
                                    <button type="button" onclick="incrementQuantity()">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="button-row no-print">
                            <button type="submit" class="button generate-btn">
                                <i class="fas fa-file-plus"></i> Generate Charge Slip
                            </button>
                        </div>
                </form>

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
                                        <!-- <th>ID</th> -->
                                        <th>Patient Name</th>
                                        <th>Service</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $slip): ?>
                                        <tr>
                                            <!-- <td><?php echo $slip['id']; ?></td> -->
                                            <td><?php echo htmlspecialchars($slip['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($slip['services']); ?></td>
                                            <td><?php echo $slip['quantity']; ?></td>
                                            <td>₱<?php echo number_format($slip['total'], 2); ?></td>
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
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message'];
                                                unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="errorMessage" style="position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3); z-index: 3000; animation: slideInRight 0.5s ease;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error_message'];
                                                        unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <script>
        // Quantity controls
        function incrementQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue < 6) {
                input.value = currentValue + 1;
            }
        }

        function decrementQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        // Handle "Others" service option
        document.addEventListener('DOMContentLoaded', function() {
            const othersRadio = document.getElementById('others');
            const othersInput = document.getElementById('others_input');
            const serviceRadios = document.querySelectorAll('input[name="services"]');

            othersRadio.addEventListener('change', function() {
                if (this.checked) {
                    othersInput.classList.add('show');
                    othersInput.required = true;
                    othersInput.focus();
                }
            });

            serviceRadios.forEach(radio => {
                if (radio.id !== 'others') {
                    radio.addEventListener('change', function() {
                        othersInput.classList.remove('show');
                        othersInput.required = false;
                        othersInput.value = '';
                    });
                }
            });

            // Update the value of "others" radio when input changes
            othersInput.addEventListener('input', function() {
                if (othersRadio.checked) {
                    othersRadio.value = this.value;
                }
            });

            // Enhanced radio button interactions
            serviceRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all service labels
                    serviceRadios.forEach(r => {
                        const label = r.nextElementSibling;
                        if (label && label.tagName === 'LABEL') {
                            label.style.background = 'white';
                            label.style.color = '#333';
                            label.style.transform = 'translateX(0)';
                        }
                    });

                    // Add active class to selected service
                    if (this.checked) {
                        const label = this.nextElementSibling;
                        if (label && label.tagName === 'LABEL') {
                            label.style.background = 'linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%)';
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
            const othersRadio = document.getElementById('others');
            const othersInput = document.getElementById('others_input');

            if (!fname || !lname || !service) {
                e.preventDefault();
                alert('Please fill in all required fields and select a service.');
                return false;
            }

            // Validate "Others" input if selected
            if (othersRadio.checked && !othersInput.value.trim()) {
                e.preventDefault();
                alert('Please specify the service in the "Others" field.');
                othersInput.focus();
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