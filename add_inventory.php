<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory Item</title>
    <link rel="stylesheet" href="Style/addinven.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>


    <main>
        <div class="form-container">
            <h2>Add New Inventory Item</h2>

            <?php
            // Display success/error messages if they exist
            if (isset($_GET['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
            }

            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['message']) . '</div>';
            }
            ?>

            <form id="inventory-form" action="save_inventory.php" method="POST">
                <div class="form-group">
                    <label for="name">Item Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="type">Type *</label>
                    <input type="text" id="type" name="type" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="serial_no">Serial No. *</label>
                    <input type="text" id="serial_no" name="serial_no" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input">
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input type="number" id="quantity" name="quantity" class="form-input" min="1" value="1" required>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="submit-button">Save Item</button>
                    <a href="inventory.php" class="cancel-button">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script src="js/inventory_validation.js"></script>
</body>

</html>