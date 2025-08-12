<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Bite Treatment Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .statistics-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #74b9ff;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .filters-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2d3436;
        }

        .filter-input {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-button {
            background: #74b9ff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .inventory-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .inventory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            margin: -25px -25px 20px -25px;
            position: relative;
        }

        .item-number {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .batch-info {
            margin-top: 5px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .stock-section {
            margin-bottom: 20px;
        }

        .stock-label {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stock-value {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1.1rem;
            font-weight: 500;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-value.low-stock {
            background: #fff5f5;
            border-color: #feb2b2;
            color: #c53030;
        }

        .stock-value.critical-stock {
            background: #fed7d7;
            border-color: #fc8181;
            color: #9b2c2c;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .expiry-info {
            margin-bottom: 20px;
        }

        .expiry-date {
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            border-radius: 10px;
            padding: 12px 15px;
            color: #0c4a6e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .expiry-date.near-expiry {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #92400e;
        }

        .expiry-date.expired {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .expiry-warning {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .warning-near {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .warning-critical {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .edit-btn {
            background: #74b9ff;
            color: white;
        }

        .edit-btn:hover {
            background: #0984e3;
            transform: translateY(-2px);
        }

        .use-btn {
            background: #00b894;
            color: white;
        }

        .use-btn:hover {
            background: #00a085;
            transform: translateY(-2px);
        }

        .control-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
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
            gap: 10px;
        }

        .add-button {
            background: linear-gradient(135deg, #00b894, #00a085);
        }

        .add-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 184, 148, 0.3);
        }

        .back-button {
            background: linear-gradient(135deg, #636e72, #2d3436);
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(99, 110, 114, 0.3);
        }

        .batch-id {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-left: auto;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: white;
        }

        @media (max-width: 768px) {
            .inventory-grid {
                grid-template-columns: 1fr;
            }

            .control-buttons {
                flex-direction: column;
                align-items: center;
            }

            .statistics-bar {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }
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
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 400px;
            max-width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .modal-btn.primary {
            background: #74b9ff;
            color: white;
        }

        .modal-btn.secondary {
            background: #ddd;
            color: #333;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <?php
    // Include the updated functions
    include 'inventory_functions.php';

    // Get statistics and items
    $stats = get_inventory_stats();
    ?>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-syringe"></i> Animal Bite Treatment Center</h1>
            <p>Inventory Management System</p>
        </div>

        <!-- Statistics Bar -->
        <div class="statistics-bar">
            <div class="stat-card">
                <div class="stat-number" id="total-batches"><?php echo $stats['total_batches']; ?></div>
                <div class="stat-label">Total Batches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-vials"><?php echo $stats['total_vials']; ?></div>
                <div class="stat-label">Available Vials</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="expiring-soon"><?php echo $stats['expiring_soon']; ?></div>
                <div class="stat-label">Expiring Soon</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="low-stock"><?php echo $stats['low_stock']; ?></div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3 style="margin-bottom: 15px; color: #2d3436;">
                <i class="fas fa-filter"></i> Filters
            </h3>
            <form id="filter-form">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="vaccine_name">Vaccine Name</label>
                        <input type="text" id="vaccine_name" name="vaccine_name" class="filter-input"
                            value="<?php echo isset($_GET['vaccine_name']) ? htmlspecialchars($_GET['vaccine_name']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="vaccine_type">Vaccine Type</label>
                        <select id="vaccine_type" name="vaccine_type" class="filter-input">
                            <option value="">All Types</option>
                            <?php
                            $vaccine_types = get_vaccine_types();
                            foreach ($vaccine_types as $key => $value) {
                                $selected = (isset($_GET['vaccine_type']) && $_GET['vaccine_type'] === $key) ? 'selected' : '';
                                echo "<option value='$key' $selected>$key - $value</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="batch_id">Batch ID</label>
                        <input type="text" id="batch_id" name="batch_id" class="filter-input"
                            value="<?php echo isset($_GET['batch_id']) ? htmlspecialchars($_GET['batch_id']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="stock_level">Stock Level</label>
                        <select id="stock_level" name="stock_level" class="filter-input">
                            <option value="">All Levels</option>
                            <option value="critical" <?php echo (isset($_GET['stock_level']) && $_GET['stock_level'] === 'critical') ? 'selected' : ''; ?>>Critical (≤5)</option>
                            <option value="low" <?php echo (isset($_GET['stock_level']) && $_GET['stock_level'] === 'low') ? 'selected' : ''; ?>>Low (≤10)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="expiry_status">Expiry Status</label>
                        <select id="expiry_status" name="expiry_status" class="filter-input">
                            <option value="">All</option>
                            <option value="expiring_soon" <?php echo (isset($_GET['expiry_status']) && $_GET['expiry_status'] === 'expiring_soon') ? 'selected' : ''; ?>>Expiring Soon</option>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="applyFilters()" class="filter-button">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <button type="button" onclick="clearFilters()" class="filter-button" style="background: #636e72; margin-left: 10px;">
                    <i class="fas fa-times"></i> Clear
                </button>
            </form>
        </div>

        <!-- Inventory Grid -->
        <div class="inventory-grid" id="inventory-container">
            <div class="loading" id="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading inventory...
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="control-buttons">
            <a href="add_inventory.php" class="main-button add-button">
                <i class="fas fa-plus"></i>
                Add New Batch
            </a>
            <a href="inventory.php" class="main-button back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

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
                    <input type="text" id="patient_id" name="patient_id">
                </div>

                <div class="form-group">
                    <label for="used_by">Used By *</label>
                    <input type="text" id="used_by" name="used_by" required>
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
                    <button type="button" class="modal-btn secondary" onclick="closeModal('useVialModal')">Cancel</button>
                    <button type="submit" class="modal-btn primary">Use Vial</button>
                </div>
            </form>
        </div>
    </div>

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
            const loading = document.getElementById('loading');

            loading.style.display = 'block';
            container.innerHTML = '<div class="loading" id="loading"><i class="fas fa-spinner fa-spin"></i> Loading inventory...</div>';

            fetch('inventory_functions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_items&filters=' + encodeURIComponent(JSON.stringify(filters))
                })
                .then(response => response.json())
                .then(data => {
                    displayInventoryItems(data);
                    updateStatistics();
                })
                .catch(error => {
                    console.error('Error loading inventory:', error);
                    container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading inventory</h3><p>Please try again later.</p></div>';
                });
        }

        // Display inventory items
        function displayInventoryItems(items) {
            const container = document.getElementById('inventory-container');

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-vial"></i>
                        <h3>No inventory items found</h3>
                        <p>Try adjusting your filters or add new batches.</p>
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
                let expiryColor = '#00b894';
                if (item.expiry_status === 'expired') {
                    expiryClass = 'expired';
                    expiryColor = '#991b1b';
                } else if (item.expiry_status === 'near_expiry') {
                    expiryClass = 'near-expiry';
                    expiryColor = '#d68910';
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
                                <i class="fas fa-vial"></i>
                                ${item.vaccine_name} (${item.vaccine_type})
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
                                <small style="color: #666;">from original ${item.original_quantity}</small>
                            </div>
                        </div>

                        <div class="expiry-info">
                            <div class="stock-label">
                                <i class="fas fa-calendar-alt"></i>
                                Expiry Date
                            </div>
                            <div class="expiry-date ${expiryClass}">
                                <span>${expiryDate}</span>
                                <span style="font-size: 0.8rem; color: ${expiryColor};">${item.days_until_expiry} days left</span>
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
                .catch(error => console.error('Error updating statistics:', error));
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
                        alert('Vial used successfully!');
                        closeModal('useVialModal');
                        loadInventory(currentFilters);
                        this.reset();
                    } else {
                        alert('Error using vial. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error using vial. Please try again.');
                });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('useVialModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>