<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory Item</title>
    <link rel="stylesheet" href="Style/inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="logo-container">
            <h1>LOGO</h1>
        </div>
        <nav>
            <ul>
                <li>
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#">
                        <i class="fas fa-calendar"></i>
                        <span>Appointment</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="appointment.php"><i class="fas fa-list"></i>View Appointments</a>
                        <a href="create_appoint.php"><i class="fas fa-plus"></i>Create Appointment</a>
                    </div>
                </li>
                <li>
                    <a href="announcement.php">
                        <i class="fas fa-info-circle"></i>
                        <span>Announcement</span>
                    </a>
                </li>
                <li>
                    <a href="message.php">
                        <i class="fas fa-envelope"></i>
                        <span>Message</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="active">
                        <i class="fas fa-boxes"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="profilepage.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

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