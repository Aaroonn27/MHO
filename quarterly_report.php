<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);

// Include database connection
require_once 'db_conn.php';

// Get selected year (default to current year if not specified)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get data for the report
$report_data = get_quarterly_report_data($selected_year);
$quarterly_totals = calculate_quarterly_totals($report_data);
$available_years = get_available_years();

// Handle Excel export (changed from CSV to Excel)
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    generate_excel($report_data, $quarterly_totals, $selected_year);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quarterly Report - <?php echo $selected_year; ?> - City Health Office of San Pablo</title>
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

        /* Header Styles */
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
            margin: 0;
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
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
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

        /* Report Header */
        .report-header {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 20px 20px 0 0;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Report Tools */
        .report-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0;
            padding: 25px 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            flex-wrap: wrap;
            gap: 20px;
        }

        .year-selector {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .year-selector label {
            font-weight: 700;
            color: #333;
            font-size: 16px;
        }

        .year-selector select {
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: white;
            color: #333;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .year-selector select:hover,
        .year-selector select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .tool-buttons {
            display: flex;
            gap: 15px;
        }

        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        /* Report Table Container */
        .report-table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            overflow-x: auto;
            width: 100%;
        }

        .report-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            background-color: transparent;
            font-size: 14px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #e9ecef;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }

        .report-table th {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8edff 100%);
            color: #333;
            padding: 15px 8px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-table tbody tr {
            transition: all 0.3s ease;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: rgba(248, 249, 255, 0.5);
        }

        .report-table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.1);
            transform: scale(1.01);
        }

        .month-row {
            background-color: rgba(255, 255, 255, 0.8);
        }

        .quarter-row {
            background: linear-gradient(135deg, rgba(255, 238, 238, 0.9) 0%, rgba(255, 228, 228, 0.9) 100%);
            font-weight: bold;
            color: #d63384;
            border-top: 2px solid #d63384;
            border-bottom: 2px solid #d63384;
        }

        .quarter-row td {
            font-weight: 700;
            font-size: 15px;
        }

        .outcome-cols {
            background-color: rgba(102, 126, 234, 0.08);
        }

        /* Chart Container */
        .chart-container {
            margin-top: 30px;
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .show-graph-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .show-graph-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .show-graph-btn i {
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                width: 98%;
                padding: 0 15px;
            }

            .report-table {
                font-size: 12px;
            }

            .report-table th,
            .report-table td {
                padding: 8px 6px;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 20px;
            }

            .logo-container {
                text-align: center;
            }

            .logo-container h1 {
                font-size: 1.6rem;
            }

            nav ul {
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }

            nav ul li a {
                padding: 10px 15px;
            }

            nav ul li a i {
                font-size: 18px;
            }

            nav ul li a span {
                font-size: 12px;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .report-tools {
                flex-direction: column;
                align-items: stretch;
                gap: 20px;
                text-align: center;
            }

            .year-selector {
                justify-content: center;
                flex-direction: column;
                gap: 10px;
            }

            .tool-buttons {
                justify-content: center;
            }

            .report-header {
                font-size: 20px;
                padding: 20px 15px;
            }

            .report-table {
                font-size: 10px;
            }

            .report-table th,
            .report-table td {
                padding: 6px 4px;
            }

            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .show-graph-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
            }

            .main-header,
            .page-header,
            .report-tools,
            .chart-container {
                display: none;
            }

            .report-header {
                background: transparent !important;
                color: black !important;
                text-shadow: none !important;
                box-shadow: none !important;
            }

            .report-table-container {
                background: white !important;
                box-shadow: none !important;
                border: 2px solid #000 !important;
            }

            .report-table th {
                background: #eee !important;
                color: black !important;
            }

            .quarter-row {
                background: #f5f5f5 !important;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i><span>Home</span></a></li>
                <li><a href="appointment.php"><i class="far fa-calendar-alt"></i><span>Appointment</span></a></li>
                <li><a href="charge_slip.php"><i class="fas fa-file-invoice"></i><span>Charge Slip</span></a></li>
                <li><a href="inventory.php"><i class="fas fa-box"></i><span>Inventory</span></a></li>
                <li><a href="rabies_registry.php"><i class="fas fa-user-md"></i><span>Patient Record</span></a></li>
            </ul>
        </nav>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-chart-line"></i> Quarterly Report</h1>
            <p>Comprehensive quarterly analysis and statistical overview</p>
        </div>
    </div>

    <div class="container">
        <div class="report-header">
            QUARTERLY REPORT - <?php echo $selected_year; ?>
        </div>

        <div class="report-tools">
            <div class="year-selector">
                <label for="year"><i class="fas fa-calendar-alt"></i> Select Year:</label>
                <select id="year" name="year" onchange="changeYear(this.value)">
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="tool-buttons">
                <!-- Changed from CSV to Excel -->
                <a href="?year=<?php echo $selected_year; ?>&export=excel" class="export-btn">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>

        <!-- Rest of your HTML remains the same -->
        <div class="report-table-container">
            <table class="report-table">
                <thead>
                    <tr>
                        <th rowspan="3">Quarter & Year</th>
                        <th colspan="6">Category 1 Exposure</th>
                        <th colspan="6">Category 2 Exposure</th>
                        <th colspan="6">Category 3 Exposure</th>
                    </tr>
                    <tr>
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <th rowspan="2">No. of Registered Exposures</th>
                            <th rowspan="2">No. of patients who received RIG</th>
                            <th colspan="4">Outcome of Post-Exposure Prophylaxis</th>
                        <?php endfor; ?>
                    </tr>
                    <tr>
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <th class="outcome-cols">Complete</th>
                            <th class="outcome-cols">Incomplete</th>
                            <th class="outcome-cols">None</th>
                            <th class="outcome-cols">Died</th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    function print_report_row($label, $data, $is_quarter = false)
                    {
                        $row_class = $is_quarter ? "quarter-row" : "month-row";
                        echo "<tr class='$row_class'>";
                        echo "<td>" . htmlspecialchars($label) . "</td>";

                        // Use numeric keys (1, 2, 3) instead of Roman numerals
                        foreach ([1, 2, 3] as $cat) {
                            if (isset($data[$cat])) {
                                echo "<td>" . $data[$cat]['registered_exposures'] . "</td>";
                                echo "<td>" . $data[$cat]['patients_received_rig'] . "</td>";
                                echo "<td class='outcome-cols'>" . $data[$cat]['outcome_complete'] . "</td>";
                                echo "<td class='outcome-cols'>" . $data[$cat]['outcome_incomplete'] . "</td>";
                                echo "<td class='outcome-cols'>" . $data[$cat]['outcome_none'] . "</td>";
                                echo "<td class='outcome-cols'>" . $data[$cat]['outcome_died'] . "</td>";
                            } else {
                                // Fill with zeros if data doesn't exist for this category
                                echo "<td>0</td><td>0</td><td class='outcome-cols'>0</td><td class='outcome-cols'>0</td><td class='outcome-cols'>0</td><td class='outcome-cols'>0</td>";
                            }
                        }
                        echo "</tr>";
                    }

                    $quarters = [
                        'FIRST QUARTER' => ['January', 'February', 'March'],
                        'SECOND QUARTER' => ['April', 'May', 'June'],
                        'THIRD QUARTER' => ['July', 'August', 'September'],
                        'FOURTH QUARTER' => ['October', 'November', 'December']
                    ];

                    foreach ($quarters as $quarter_name => $months) {
                        foreach ($months as $month) {
                            print_report_row("$month $selected_year", $report_data[$month], false);
                        }
                        print_report_row("$quarter_name $selected_year", $quarterly_totals[$quarter_name], true);
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="chart-container">
            <div class="button-group">
                <a href="analytics_dashboard.php?year=<?php echo $selected_year; ?>" class="show-graph-btn">
                    <i class="fas fa-chart-line"></i> View Analytics Dashboard
                </a>
                <button class="show-graph-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>
    </div>

    <script>
        function changeYear(year) {
            window.location.href = '?year=' + year;
        }

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Add loading animation for year change
        document.getElementById('year').addEventListener('change', function() {
            const btn = this;
            btn.disabled = true;
            btn.style.opacity = '0.6';

            setTimeout(() => {
                changeYear(this.value);
            }, 300);
        });
    </script>
</body>

</html>