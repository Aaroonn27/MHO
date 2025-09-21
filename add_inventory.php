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