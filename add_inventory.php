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
    <title>Add New Vaccine Batch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(45, 95, 63, 0.3);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            border-top: 4px solid #2d5f3f;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2d3436;
        }

        .form-header h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #2d5f3f;
        }

        .form-header p {
            color: #666;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #c3e6cb;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24;
            border: 2px solid #f1aeb5;
            border-left: 4px solid #dc3545;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #a5d6a7;
            position: relative;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2d5f3f 0%, #4a8f5f 100%);
            border-radius: 12px 0 0 12px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d5f3f;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #4a8f5f;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d5f3f;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #4a8f5f;
        }

        .required {
            color: #e74c3c;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .form-input:invalid {
            border-color: #e74c3c;
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
        }

        .input-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .batch-preview {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.3);
        }

        .batch-preview h3 {
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .batch-preview h3 i {
            margin-right: 8px;
        }

        .batch-id-display {
            font-size: 1.5rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 5px;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .submit-button {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .cancel-button {
            background: white;
            color: #2d5f3f;
            border: 2px solid #4a8f5f;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .cancel-button:hover {
            background: #2d5f3f;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 95, 63, 0.3);
        }

        .cancel-button:active {
            transform: translateY(0);
        }

        .cost-summary {
            background: white;
            border: 2px solid #a5d6a7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(45, 95, 63, 0.1);
        }

        .cost-summary h4 {
            color: #2d5f3f;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cost-summary h4 i {
            color: #4a8f5f;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #333;
        }

        .cost-total {
            font-weight: bold;
            font-size: 1.1rem;
            border-top: 2px solid #4a8f5f;
            padding-top: 10px;
            margin-top: 10px;
            color: #2d5f3f;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                padding: 25px;
            }

            .form-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header p {
                font-size: 1rem;
            }

            .batch-id-display {
                font-size: 1.2rem;
                padding: 8px 15px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 1.6rem;
            }

            .form-container {
                padding: 20px;
            }

            .form-section {
                padding: 20px;
            }

            .section-title {
                font-size: 1.1rem;
            }

            .submit-button,
            .cancel-button {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php
    include 'inventory_functions.php';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'vaccine_name' => $_POST['vaccine_name'],
            'vaccine_type' => $_POST['vaccine_type'],
            'batch_id' => $_POST['batch_id'],
            'manufacturer' => $_POST['manufacturer'],
            'original_quantity' => $_POST['original_quantity'],
            'expiry_date' => $_POST['expiry_date'],
            'storage_location' => $_POST['storage_location'],
            'storage_temperature' => $_POST['storage_temperature'],
            'date_received' => $_POST['date_received'],
            'supplier' => $_POST['supplier'],
            'unit_cost' => $_POST['unit_cost'],
            'notes' => $_POST['notes']
        ];

        if (add_inventory_batch($data)) {
            $success_message = "New vaccine batch added successfully!";
        } else {
            $error_message = "Error adding vaccine batch. Please try again.";
        }
    }
    ?>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> Add New Vaccine Batch</h1>
            <p>Animal Bite Treatment Center Inventory</p>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>New Batch Registration</h2>
                <p>Enter the details for the new vaccine batch below</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Batch ID Preview -->
            <div class="batch-preview">
                <h3><i class="fas fa-barcode"></i> Generated Batch ID</h3>
                <div class="batch-id-display" id="batch-id-preview">
                    Will be generated automatically
                </div>
            </div>

            <form id="inventory-form" action="" method="POST">
                <div class="form-grid">
                    <!-- Vaccine Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-vial"></i>
                            Vaccine Information
                        </div>

                        <div class="form-group">
                            <label for="vaccine_name">
                                <i class="fas fa-syringe"></i>
                                Vaccine Name <span class="required">*</span>
                            </label>
                            <input type="text" id="vaccine_name" name="vaccine_name" class="form-input"
                                placeholder="e.g., Rabies Vaccine" required>
                            <div class="input-help">Enter the common name of the vaccine</div>
                        </div>

                        <div class="form-group">
                            <label for="vaccine_type">
                                <i class="fas fa-tags"></i>
                                Vaccine Type <span class="required">*</span>
                            </label>
                            <select id="vaccine_type" name="vaccine_type" class="form-select" required>
                                <option value="">Select vaccine type...</option>
                                <?php
                                $vaccine_types = get_vaccine_types();
                                foreach ($vaccine_types as $key => $value) {
                                    echo "<option value='$key'>$key - $value</option>";
                                }
                                ?>
                            </select>
                            <div class="input-help">Choose the specific type/formulation</div>
                        </div>

                        <div class="form-group">
                            <label for="batch_id">
                                <i class="fas fa-barcode"></i>
                                Batch ID <span class="required">*</span>
                            </label>
                            <input type="text" id="batch_id" name="batch_id" class="form-input"
                                placeholder="e.g., RB-2024-001" required>
                            <div class="input-help">Enter the manufacturer's batch identifier</div>
                        </div>

                        <div class="form-group">
                            <label for="manufacturer">
                                <i class="fas fa-industry"></i>
                                Manufacturer
                            </label>
                            <select id="manufacturer" name="manufacturer" class="form-select">
                                <option value="">Select manufacturer...</option>
                                <option value="Sanofi Pasteur">Sanofi Pasteur</option>
                                <option value="GSK">GSK</option>
                                <option value="Novartis">Novartis</option>
                                <option value="Grifols">Grifols</option>
                                <option value="Bharat Biotech">Bharat Biotech</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity and Dates Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-calculator"></i>
                            Quantity & Dates
                        </div>

                        <div class="form-group">
                            <label for="original_quantity">
                                <i class="fas fa-boxes"></i>
                                Original Quantity <span class="required">*</span>
                            </label>
                            <input type="number" id="original_quantity" name="original_quantity"
                                class="form-input" min="1" required>
                            <div class="input-help">Total number of vials in this batch</div>
                        </div>

                        <div class="form-group">
                            <label for="expiry_date">
                                <i class="fas fa-calendar-alt"></i>
                                Expiry Date <span class="required">*</span>
                            </label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-input" required>
                            <div class="input-help">Date when the vaccine expires</div>
                        </div>

                        <div class="form-group">
                            <label for="date_received">
                                <i class="fas fa-truck"></i>
                                Date Received <span class="required">*</span>
                            </label>
                            <input type="date" id="date_received" name="date_received" class="form-input" required>
                            <div class="input-help">Date when batch was received</div>
                        </div>

                        <div class="form-group">
                            <label for="unit_cost">
                                <i class="fas fa-peso-sign"></i>
                                Unit Cost (PHP)
                            </label>
                            <input type="number" id="unit_cost" name="unit_cost" class="form-input"
                                step="0.01" min="0" placeholder="0.00">
                            <div class="input-help">Cost per vial in Philippine Pesos</div>
                        </div>
                    </div>

                    <!-- Storage Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-thermometer-half"></i>
                            Storage Information
                        </div>

                        <div class="form-group">
                            <label for="storage_location">
                                <i class="fas fa-map-marker-alt"></i>
                                Storage Location
                            </label>
                            <select id="storage_location" name="storage_location" class="form-select">
                                <option value="">Select storage location...</option>
                                <option value="Refrigerator A">Refrigerator A</option>
                                <option value="Refrigerator B">Refrigerator B</option>
                                <option value="Refrigerator C">Refrigerator C</option>
                                <option value="Freezer A">Freezer A</option>
                                <option value="Cold Room">Cold Room</option>
                                <option value="Pharmacy Storage">Pharmacy Storage</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="storage_temperature">
                                <i class="fas fa-temperature-low"></i>
                                Storage Temperature
                            </label>
                            <select id="storage_temperature" name="storage_temperature" class="form-select">
                                <option value="">Select temperature range...</option>
                                <option value="2-8°C">2-8°C (Standard Refrigeration)</option>
                                <option value="-15 to -25°C">-15 to -25°C (Freezer)</option>
                                <option value="15-25°C">15-25°C (Room Temperature)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="supplier">
                                <i class="fas fa-building"></i>
                                Supplier/Distributor
                            </label>
                            <input type="text" id="supplier" name="supplier" class="form-input"
                                placeholder="e.g., Medical Supply Corp">
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-sticky-note"></i>
                            Additional Information
                        </div>

                        <div class="form-group">
                            <label for="notes">
                                <i class="fas fa-comment"></i>
                                Notes
                            </label>
                            <textarea id="notes" name="notes" class="form-textarea"
                                placeholder="Any additional notes about this batch..."></textarea>
                            <div class="input-help">Optional notes about special handling, etc.</div>
                        </div>

                        <!-- Cost Summary -->
                        <div class="cost-summary" id="cost-summary" style="display: none;">
                            <h4><i class="fas fa-calculator"></i> Cost Summary</h4>
                            <div class="cost-item">
                                <span>Quantity:</span>
                                <span id="summary-quantity">0 vials</span>
                            </div>
                            <div class="cost-item">
                                <span>Unit Cost:</span>
                                <span id="summary-unit-cost">₱0.00</span>
                            </div>
                            <div class="cost-item cost-total">
                                <span>Total Value:</span>
                                <span id="summary-total">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="submit-button">
                        <i class="fas fa-save"></i>
                        Add Batch
                    </button>
                    <a href="inventory.php" class="cancel-button">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-generate batch ID based on vaccine type and current date
        function generateBatchId() {
            const vaccineType = document.getElementById('vaccine_type').value;
            const today = new Date();
            const year = today.getFullYear();

            if (vaccineType) {
                // Generate a simple batch ID format
                const prefix = vaccineType;
                const dateStr = year.toString();
                const randomNum = Math.floor(Math.random() * 900) + 100; // 3-digit random number

                const batchId = `${prefix}-${dateStr}-${randomNum.toString().padStart(3, '0')}`;
                document.getElementById('batch_id').value = batchId;
                document.getElementById('batch-id-preview').textContent = batchId;
            } else {
                document.getElementById('batch-id-preview').textContent = 'Will be generated automatically';
            }
        }

        // Update cost summary
        function updateCostSummary() {
            const quantity = parseInt(document.getElementById('original_quantity').value) || 0;
            const unitCost = parseFloat(document.getElementById('unit_cost').value) || 0;
            const total = quantity * unitCost;

            document.getElementById('summary-quantity').textContent = `${quantity} vials`;
            document.getElementById('summary-unit-cost').textContent = `₱${unitCost.toFixed(2)}`;
            document.getElementById('summary-total').textContent = `₱${total.toFixed(2)}`;

            const costSummary = document.getElementById('cost-summary');
            if (quantity > 0 && unitCost > 0) {
                costSummary.style.display = 'block';
            } else {
                costSummary.style.display = 'none';
            }
        }

        // Set default date received to today
        document.getElementById('date_received').valueAsDate = new Date();

        // Event listeners
        document.getElementById('vaccine_type').addEventListener('change', generateBatchId);
        document.getElementById('original_quantity').addEventListener('input', updateCostSummary);
        document.getElementById('unit_cost').addEventListener('input', updateCostSummary);

        // Form validation
        document.getElementById('inventory-form').addEventListener('submit', function(e) {
            const expiryDate = new Date(document.getElementById('expiry_date').value);
            const receivedDate = new Date(document.getElementById('date_received').value);
            const today = new Date();

            // Check if expiry date is not in the past
            if (expiryDate <= today) {
                alert('Warning: Expiry date is in the past. Please verify the date.');
                return;
            }

            // Check if received date is not in the future
            if (receivedDate > today) {
                if (!confirm('Received date is in the future. Do you want to continue?')) {
                    e.preventDefault();
                    return;
                }
            }

            // Check if expiry is before received date
            if (expiryDate <= receivedDate) {
                alert('Error: Expiry date cannot be before or same as received date.');
                e.preventDefault();
                return;
            }
        });

        // Auto-complete batch ID when manually typing
        document.getElementById('batch_id').addEventListener('input', function() {
            document.getElementById('batch-id-preview').textContent = this.value || 'Will be generated automatically';
        });

        // Initialize batch ID generation
        generateBatchId();
    </script>
</body>

</html>