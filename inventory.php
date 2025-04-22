<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="Style/home.css">
    <link rel="stylesheet" href="Style/inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="inventory-container">
            <!-- Filters sidebar -->
            <div class="filters-sidebar">
                <h2>Filters</h2>
                <form action="inventory.php" method="GET">
                    <div class="filter-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="filter-input"
                            value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="type">Type</label>
                        <input type="text" id="type" name="type" class="filter-input"
                            value="<?php echo isset($_GET['type']) ? htmlspecialchars($_GET['type']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="serial">Serial No.</label>
                        <input type="text" id="serial" name="serial" class="filter-input"
                            value="<?php echo isset($_GET['serial']) ? htmlspecialchars($_GET['serial']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="expiry">Expiry</label>
                        <input type="date" id="expiry" name="expiry" class="filter-input"
                            value="<?php echo isset($_GET['expiry']) ? htmlspecialchars($_GET['expiry']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="filter-input"
                            value="<?php echo isset($_GET['quantity']) ? htmlspecialchars($_GET['quantity']) : ''; ?>">
                    </div>

                    <button type="submit" class="filter-button">Go</button>
                </form>
            </div>

            <!-- Main content area -->
            <div class="inventory-content">
                <!-- Analytics section -->
                <div class="analytics-section">
                    <div class="analytics-card">
                        <canvas id="stockLevelChart"></canvas>
                    </div>
                    <div class="analytics-card">
                        <canvas id="expiryChart"></canvas>
                    </div>
                    <div class="analytics-card">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>

                <!-- Inventory list section -->
                <div class="inventory-table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Serial No.</th>
                                <th>Expiry</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include 'inventory_functions.php';
                            display_inventory(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Item Button -->
                <div class="add-item-container">
                    <a href="add_inventory.php" class="add-item-button">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="js/inventory.js"></script>
</body>

</html>