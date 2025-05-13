<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_conn.php';
require_once 'analytics.php';

// Get selected year (default to current year if not specified)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$available_years = get_available_years();

// Get analytics data
$analytics_report = generate_analytics_report($selected_year);

// Check for errors
if (isset($analytics_report['error'])) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($analytics_report['message']) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - <?php echo $selected_year; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .year-selector {
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2196F3;
        }
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .chart-container h2 {
            margin-top: 0;
            color: #333;
        }
        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 500px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="dashboard-header">
            <h1>Analytics Dashboard</h1>
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
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Cases</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['total_cases']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Months</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['active_months']; ?></div>
            </div>
            <div class="stat-card">
                <h3>RIG Completion Rate</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['rig_completion_rate']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Complete Outcome Rate</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['complete_outcome_rate']; ?></div>
            </div>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="chart-container">
            <h2>Monthly Trends</h2>
            <canvas id="monthlyTrendsChart"></canvas>
        </div>

        <!-- Category Distribution Chart -->
        <div class="chart-container">
            <h2>Category Distribution</h2>
            <canvas id="categoryDistributionChart"></canvas>
        </div>

        <!-- Animal Type Analysis -->
        <div class="chart-container">
            <h2>Animal Type Analysis</h2>
            <canvas id="animalTypeChart"></canvas>
        </div>

        <!-- Outcome Analysis -->
        <div class="chart-container">
            <h2>Outcome Analysis</h2>
            <canvas id="outcomeChart"></canvas>
        </div>
    </div>

    <script>
        // Define changeYear function in global scope
        window.changeYear = function(year) {
            window.location.href = '?year=' + year;
        }

        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($item) { 
                    return date('F', mktime(0, 0, 0, $item['month'], 1)); 
                }, $analytics_report['monthly_trends'])); ?>,
                datasets: [{
                    label: 'Total Cases',
                    data: <?php echo json_encode(array_map(function($item) { 
                        return $item['monthly_cases']; 
                    }, $analytics_report['monthly_trends'])); ?>,
                    borderColor: '#2196F3',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Case Distribution'
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryDistributionCtx = document.getElementById('categoryDistributionChart').getContext('2d');
        new Chart(categoryDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Category 1', 'Category 2', 'Category 3'],
                datasets: [{
                    data: [
                        <?php echo floatval(str_replace('%','',$analytics_report['summary']['category1_percentage'])); ?>,
                        <?php echo floatval(str_replace('%','',$analytics_report['summary']['category2_percentage'])); ?>,
                        <?php echo floatval(str_replace('%','',$analytics_report['summary']['category3_percentage'])); ?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]  
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    title: {
                        display: true,
                        text: 'Category Distribution'
                    }
                }
            }
        });

        // Animal Type Chart
        const animalTypeCtx = document.getElementById('animalTypeChart').getContext('2d');
        new Chart(animalTypeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($item) { 
                    return $item['animal_type']; 
                }, $analytics_report['animal_types'])); ?>,
                datasets: [{
                    label: 'Number of Cases',
                    data: <?php echo json_encode(array_map(function($item) { 
                        return $item['count']; 
                    }, $analytics_report['animal_types'])); ?>,
                    backgroundColor: '#4CAF50'
                }]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    title: {
                        display: true,
                        text: 'Animal Type Distribution'
                    }
                }
            }
        });

        // Outcome Chart
        const outcomeCtx = document.getElementById('outcomeChart').getContext('2d');
        new Chart(outcomeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($item) { 
                    return $item['outcome']; 
                }, $analytics_report['outcomes'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($item) { 
                        return $item['count']; 
                    }, $analytics_report['outcomes'])); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    title: {
                        display: true,
                        text: 'Outcome Distribution'
                    }
                }
            }
        });
    </script>
</body>
</html> 