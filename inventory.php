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