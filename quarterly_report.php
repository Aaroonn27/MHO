<?php
// Include database connection
require_once 'db_conn.php';

// Get selected year (default to current year if not specified)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get data for the report
$report_data = get_quarterly_report_data($selected_year);
$quarterly_totals = calculate_quarterly_totals($report_data);
$available_years = get_available_years();

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    generate_csv($report_data, $quarterly_totals, $selected_year);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quarterly Report - <?php echo $selected_year; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Style/quarter_report.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="report-header">
            QUARTERLY REPORT
        </div>

        <div class="report-tools">
            <div class="year-selector">
                <label for="year">Select Year:</label>
                <select id="year" name="year" onchange="changeYear(this.value)">
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="?year=<?php echo $selected_year; ?>&export=csv" class="export-btn">
                <i class="fas fa-file-csv"></i> Export to CSV
            </a>
        </div>

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
                $categories = ['I', 'II', 'III'];
                function print_report_row($label, $data) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($label) . "</td>";
                    foreach (['I', 'II', 'III'] as $cat) {
                        echo "<td>" . ($data[$cat]['registered_exposures'] ?? 0) . "</td>";
                        echo "<td>" . ($data[$cat]['patients_received_rig'] ?? 0) . "</td>";
                        echo "<td class='outcome-cols'>" . ($data[$cat]['outcome_complete'] ?? 0) . "</td>";
                        echo "<td class='outcome-cols'>" . ($data[$cat]['outcome_incomplete'] ?? 0) . "</td>";
                        echo "<td class='outcome-cols'>" . ($data[$cat]['outcome_none'] ?? 0) . "</td>";
                        echo "<td class='outcome-cols'>" . ($data[$cat]['outcome_died'] ?? 0) . "</td>";
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
                        print_report_row("$month $selected_year", $report_data[$month]);
                    }
                    print_report_row("$quarter_name $selected_year", $quarterly_totals[$quarter_name]);
                }
                ?>
            </tbody>
        </table>

        <div class="chart-container">
            <button class="show-graph-btn" onclick="window.location.href='report_graph.php?year=<?php echo $selected_year; ?>'">
                Show Graph
            </button>
        </div>
    </div>

    <script>
        function changeYear(year) {
            window.location.href = '?year=' + year;
        }
    </script>
</body>

</html>