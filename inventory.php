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
    <title>Animal Bite Treatment Inventory - City Health Office of San Pablo</title>
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

        /* Header Styles - Integrated from appointment.php */
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

        /* Statistics Bar */
        .statistics-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Filters Section */
        .filters-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .filters-section h3 {
            font-size: 1.5rem;
            color: #333;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filters-section h3 i {
            color: #667eea;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .filter-input {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .filter-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .filter-button.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        .filter-button.secondary:hover {
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        }

        /* Inventory Grid */
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .inventory-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .inventory-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin: -25px -25px 25px -25px;
            position: relative;
        }

        .item-number {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .item-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .batch-info {
            margin-top: 8px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .batch-id {
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
        }

        .stock-section {
            margin-bottom: 20px;
        }

        .stock-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .stock-value {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .stock-value.low-stock {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-color: #feb2b2;
            color: #c53030;
        }

        .stock-value.critical-stock {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            border-color: #fc8181;
            color: #9b2c2c;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .expiry-info {
            margin-bottom: 20px;
        }

        .expiry-date {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            border-radius: 12px;
            padding: 15px 20px;
            color: #0c4a6e;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }

        .expiry-date.near-expiry {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-color: #fcd34d;
            color: #92400e;
        }

        .expiry-date.expired {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #fca5a5;
            color: #991b1b;
        }

        .expiry-warning {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .warning-near {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .warning-critical {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .action-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .edit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .use-btn {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
        }

        .use-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3);
        }

        /* Control Buttons */
        .control-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .main-button {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .add-button {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        }

        .add-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 184, 148, 0.4);
        }

        .back-button {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            margin: 20px 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 25px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 1rem;
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 60px;
            color: white;
            font-size: 1.1rem;
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: 15px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 8% auto;
            padding: 40px;
            border-radius: 20px;
            width: 450px;
            max-width: 95%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            color: #333;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header h3 i {
            color: #667eea;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .modal-btn {
            padding: 12px 25px;
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

        .modal-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .modal-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e9ecef;
        }

        .modal-btn.secondary:hover {
            background: #e9ecef;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            main {
                padding: 30px 20px;
            }

            .inventory-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container h1 {
                font-size: 1.8rem;
                text-align: center;
            }

            nav ul {
                gap: 15px;
            }

            nav ul li a {
                padding: 10px 12px;
            }

            nav ul li a i {
                font-size: 18px;
                margin-bottom: 4px;
            }

            nav ul li a span {
                font-size: 11px;
            }

            .page-title h1 {
                font-size: 2.2rem;
            }

            .statistics-bar {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .filter-actions {
                flex-direction: column;
            }

            .inventory-grid {
                grid-template-columns: 1fr;
            }

            .control-buttons {
                flex-direction: column;
                align-items: center;
            }

            .modal-content {
                margin: 10% auto;
                padding: 30px 20px;
                width: 90%;
            }
        }

        @media (max-width: 480px) {
            .main-header {
                padding: 10px 15px;
            }

            .logo-img {
                width: 50px;
                height: 50px;
                margin-right: 15px;
            }

            .logo-container h1 {
                font-size: 1.5rem;
            }

            main {
                padding: 20px 15px;
            }

            .statistics-bar {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Integrated Header -->
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
                <h1><i class="fas fa-syringe"></i> Animal Bite Treatment Center</h1>
                <p>Inventory Management System</p>
            </div>
        </div>

        <!-- Statistics Bar -->
        <div class="statistics-bar">
            <div class="stat-card">
                <div class="stat-number" id="total-batches">0</div>
                <div class="stat-label">Total Batches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-vials">0</div>
                <div class="stat-label">Available Vials</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="expiring-soon">0</div>
                <div class="stat-label">Expiring Soon</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="low-stock">0</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3><i class="fas fa-filter"></i> Filters</h3>
            <form id="filter-form">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="vaccine_name">Vaccine Name</label>
                        <input type="text" id="vaccine_name" name="vaccine_name" class="filter-input"
                            value="<?php echo isset($_GET['vaccine_name']) ? htmlspecialchars($_GET['vaccine_name']) : ''; ?>"
                            placeholder="Enter vaccine name...">
                    </div>

                    <div class="filter-group">
                        <label for="vaccine_type">Vaccine Type</label>
                        <select id="vaccine_type" name="vaccine_type" class="filter-input">
                            <option value="">All Types</option>
                            <option value="HDCV">HDCV - Human Diploid Cell Vaccine</option>
                            <option value="HRIG">HRIG - Human Rabies Immunoglobulin</option>
                            <option value="PCECV">PCECV - Purified Chick Embryo Cell Vaccine</option>
                            <option value="TT">TT - Tetanus Toxoid</option>
                            <option value="RIG">RIG - Rabies Immunoglobulin</option>
                            <option value="ERIG">ERIG - Equine Rabies Immunoglobulin</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="batch_id">Batch ID</label>
                        <input type="text" id="batch_id" name="batch_id" class="filter-input"
                            value="<?php echo isset($_GET['batch_id']) ? htmlspecialchars($_GET['batch_id']) : ''; ?>"
                            placeholder="Enter batch ID...">
                    </div>

                    <div class="filter-group">
                        <label for="stock_level">Stock Level</label>
                        <select id="stock_level" name="stock_level" class="filter-input">
                            <option value="">All Levels</option>
                            <option value="critical">Critical (≤5 vials)</option>
                            <option value="low">Low (≤10 vials)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="expiry_status">Expiry Status</label>
                        <select id="expiry_status" name="expiry_status" class="filter-input">
                            <option value="">All Items</option>
                            <option value="expiring_soon">Expiring Soon (≤30 days)</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" onclick="clearFilters()" class="filter-button secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                    <button type="button" onclick="applyFilters()" class="filter-button">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Inventory Grid -->
        <div class="inventory-grid" id="inventory-container">
            <div class="loading" id="loading">
                <i class="fas fa-spinner"></i>
                <div>Loading inventory...</div>
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="control-buttons">
            <a href="add_inventory.php" class="main-button add-button">
                <i class="fas fa-plus"></i>
                Add New Batch
            </a>
        </div>
    </main>

    <!-- Use Vial Modal -->
    <div id="useVialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-syringe"></i> Use Vial</h3>
                <span class="close" onclick="closeModal('useVialModal')">&times;</span>
            </div>
            <form id="useVialForm">
                <input type="hidden" id="modal_batch_id" name="batch_id">

                <div class="form-group">
                    <label for="patient_id">Patient ID (Optional)</label>
                    <input type="text" id="patient_id" name="patient_id" placeholder="Enter patient ID...">
                </div>

                <div class="form-group">
                    <label for="used_by">Used By *</label>
                    <input type="text" id="used_by" name="used_by" required placeholder="Enter staff name...">
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <select id="purpose" name="purpose">
                        <option value="Post-exposure prophylaxis">Post-exposure prophylaxis</option>
                        <option value="Pre-exposure prophylaxis">Pre-exposure prophylaxis</option>
                        <option value="Booster dose">Booster dose</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <input type="text" id="notes" name="notes" placeholder="Additional notes...">
                </div>

                <div class="modal-buttons">
                    <button type="button" class="modal-btn secondary" onclick="closeModal('useVialModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="modal-btn primary">
                        <i class="fas fa-syringe"></i> Use Vial
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification"></div>

    <script>
        // Global variables
        let currentFilters = {};

        // Load inventory on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadInventory();
        });

        // Load inventory data
        function loadInventory(filters = {}) {
            const container = document.getElementById('inventory-container');

            container.innerHTML = '<div class="loading" id="loading"><i class="fas fa-spinner"></i><div>Loading inventory...</div></div>';

            // Check if inventory_functions.php exists
            fetch('inventory_functions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_items&filters=' + encodeURIComponent(JSON.stringify(filters))
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    displayInventoryItems(data);
                    updateStatistics();
                })
                .catch(error => {
                    console.error('Error loading inventory:', error);
                    // Show sample data if backend is not available
                    showSampleData();
                });
        }

        // Show sample data for demonstration
        function showSampleData() {
            const sampleData = [
                {
                    id: 1,
                    vaccine_name: 'Verorab',
                    vaccine_type: 'HDCV',
                    batch_id: 'VRB2024001',
                    current_quantity: 15,
                    used_quantity: 5,
                    original_quantity: 20,
                    expiry_date: '2024-12-31',
                    days_until_expiry: 120,
                    stock_level: 'normal',
                    expiry_status: 'good'
                },
                {
                    id: 2,
                    vaccine_name: 'HyperRAB',
                    vaccine_type: 'HRIG',
                    batch_id: 'HRB2024002',
                    current_quantity: 3,
                    used_quantity: 7,
                    original_quantity: 10,
                    expiry_date: '2024-10-15',
                    days_until_expiry: 45,
                    stock_level: 'critical',
                    expiry_status: 'near_expiry'
                },
                {
                    id: 3,
                    vaccine_name: 'Rabipur',
                    vaccine_type: 'PCECV',
                    batch_id: 'RPR2024003',
                    current_quantity: 8,
                    used_quantity: 12,
                    original_quantity: 20,
                    expiry_date: '2025-03-20',
                    days_until_expiry: 180,
                    stock_level: 'low',
                    expiry_status: 'good'
                }
            ];

            displayInventoryItems(sampleData);
            
            // Update statistics with sample data
            document.getElementById('total-batches').textContent = '3';
            document.getElementById('total-vials').textContent = '26';
            document.getElementById('expiring-soon').textContent = '1';
            document.getElementById('low-stock').textContent = '2';
        }

        // Display inventory items
        function displayInventoryItems(items) {
            const container = document.getElementById('inventory-container');

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-vial"></i>
                        <h3>No inventory items found</h3>
                        <p>Try adjusting your filters or add new batches to get started.</p>
                    </div>
                `;
                return;
            }

            const vaccineTypes = {
                'HDCV': 'Human Diploid Cell Vaccine',
                'HRIG': 'Human Rabies Immunoglobulin',
                'PCECV': 'Purified Chick Embryo Cell Vaccine',
                'TT': 'Tetanus Toxoid',
                'RIG': 'Rabies Immunoglobulin',
                'ERIG': 'Equine Rabies Immunoglobulin'
            };

            let html = '';

            items.forEach(item => {
                let stockClass = '';
                let stockIcon = 'fas fa-check-circle';
                let stockColor = '#00b894';
                let warningHtml = '';

                // Determine stock level styling
                if (item.stock_level === 'critical') {
                    stockClass = 'critical-stock';
                    stockIcon = 'fas fa-exclamation-triangle';
                    stockColor = '#c0392b';
                    warningHtml = `
                        <div class="expiry-warning warning-critical">
                            <i class="fas fa-exclamation-triangle"></i>
                            CRITICAL: Very low stock - Immediate reorder required!
                        </div>
                    `;
                } else if (item.stock_level === 'low') {
                    stockClass = 'low-stock';
                    stockIcon = 'fas fa-exclamation-triangle';
                    stockColor = '#e67e22';
                    warningHtml = `
                        <div class="expiry-warning warning-near">
                            <i class="fas fa-exclamation-triangle"></i>
                            Low stock alert - Consider reordering soon
                        </div>
                    `;
                }

                // Determine expiry styling
                let expiryClass = '';
                let expiryColor = '#0c4a6e';
                if (item.expiry_status === 'expired') {
                    expiryClass = 'expired';
                    expiryColor = '#991b1b';
                } else if (item.expiry_status === 'near_expiry') {
                    expiryClass = 'near-expiry';
                    expiryColor = '#92400e';
                }

                const vaccineFull = vaccineTypes[item.vaccine_type] || item.vaccine_type;
                const expiryDate = new Date(item.expiry_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                html += `
                    <div class="inventory-card">
                        <div class="card-header">
                            <div class="item-number">
                                <div class="item-title">
                                    <i class="fas fa-vial"></i>
                                    ${item.vaccine_name} (${item.vaccine_type})
                                </div>
                                <span class="batch-id">Batch: ${item.batch_id}</span>
                            </div>
                            <div class="batch-info">${vaccineFull}</div>
                        </div>

                        <div class="stock-section">
                            <div class="stock-label">
                                <i class="fas fa-box"></i>
                                Available Vials
                            </div>
                            <div class="stock-value ${stockClass}">
                                <span>${item.current_quantity} vials</span>
                                <i class="${stockIcon}" style="color: ${stockColor};"></i>
                            </div>
                        </div>

                        <div class="stock-section">
                            <div class="stock-label">
                                <i class="fas fa-minus-circle"></i>
                                Used Vials
                            </div>
                            <div class="stock-value">
                                <span>${item.used_quantity} vials</span>
                                <small style="color: #666; font-size: 0.9rem;">from original ${item.original_quantity}</small>
                            </div>
                        </div>

                        <div class="expiry-info">
                            <div class="stock-label">
                                <i class="fas fa-calendar-alt"></i>
                                Expiry Date
                            </div>
                            <div class="expiry-date ${expiryClass}">
                                <span>${expiryDate}</span>
                                <span style="font-size: 0.85rem; color: ${expiryColor}; font-weight: 600;">${item.days_until_expiry} days left</span>
                            </div>
                            ${warningHtml}
                        </div>

                        <div class="card-actions">
                            <button class="action-btn edit-btn" onclick="editBatch(${item.id})">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <button class="action-btn use-btn" onclick="openUseVialModal('${item.batch_id}')">
                                <i class="fas fa-syringe"></i>
                                Use Vial
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Update statistics
        function updateStatistics() {
            fetch('inventory_functions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_stats'
                })
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('total-batches').textContent = stats.total_batches;
                    document.getElementById('total-vials').textContent = stats.total_vials;
                    document.getElementById('expiring-soon').textContent = stats.expiring_soon;
                    document.getElementById('low-stock').textContent = stats.low_stock;
                })
                .catch(error => {
                    console.error('Error updating statistics:', error);
                    // Keep existing values or show defaults
                });
        }

        // Apply filters
        function applyFilters() {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            const filters = {};

            for (let [key, value] of formData.entries()) {
                if (value.trim()) {
                    filters[key] = value.trim();
                }
            }

            currentFilters = filters;
            loadInventory(filters);
        }

        // Clear filters
        function clearFilters() {
            document.getElementById('filter-form').reset();
            currentFilters = {};
            loadInventory();
        }

        // Open use vial modal
        function openUseVialModal(batchId) {
            document.getElementById('modal_batch_id').value = batchId;
            document.getElementById('useVialModal').style.display = 'block';
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Edit batch
        function editBatch(id) {
            window.location.href = `edit_inventory.php?id=${id}`;
        }

        // Handle use vial form submission
        document.getElementById('useVialForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'use_vial');

            fetch('inventory_functions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Vial used successfully!', 'success');
                        closeModal('useVialModal');
                        loadInventory(currentFilters);
                        this.reset();
                    } else {
                        showNotification(data.message || 'Error using vial. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error using vial. Please try again.', 'error');
                });
        });

        // Show notification
        function showNotification(message, type) {
            // Create notification element if it doesn't exist
            let notification = document.getElementById('notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'notification';
                notification.className = 'notification';
                document.body.appendChild(notification);
            }

            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 30px;
                right: 30px;
                padding: 15px 25px;
                border-radius: 12px;
                color: white;
                font-weight: 600;
                z-index: 1001;
                transform: translateX(400px);
                transition: transform 0.3s ease;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                ${type === 'success' ? 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);' : 'background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);'}
            `;

            // Show notification
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Hide notification after 5 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
            }, 5000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('useVialModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Handle filter form submission with Enter key
        document.getElementById('filter-form').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    </script>
</body>

</html>