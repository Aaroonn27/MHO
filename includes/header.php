<header>
    <div class="logo-container">
        <h1>LOGO</h1>
    </div>
    <nav>
        <ul>
            <li <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' || basename($_SERVER['PHP_SELF']) == 'create_appoint.php' ? 'class="active"' : ''; ?>>
                <a href="appointment.php">
                    <i class="fas fa-calendar"></i>
                    <span>Appointment</span>
                </a>
            </li>
            <li <?php echo basename($_SERVER['PHP_SELF']) == 'charge_slip.php' ? 'class="active"' : ''; ?>>
                <a href="charge_slip.php">
                    <i class="fas fa-file-invoice"></i>
                    <span>Charge Slip</span>
                </a>
            </li>
            <li <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'class="active"' : ''; ?>>
                <a href="inventory.php">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li <?php echo basename($_SERVER['PHP_SELF']) == 'patient_records.php' ? 'class="active"' : ''; ?>>
                <a href="patient_records.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Patient Records</span>
                </a>
            </li>
        </ul>
    </nav>
</header>