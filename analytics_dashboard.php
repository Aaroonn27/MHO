<?php

session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);

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
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Analytics Dashboard - <?php echo $selected_year; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles - Integrated from appointment.php */
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

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .year-selector {
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .year-selector select {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-icon {
            float: right;
            font-size: 2rem;
            color: #667eea;
            opacity: 0.7;
        }

        .gender-distribution {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .gender-item {
            text-align: center;
            flex: 1;
        }

        .gender-item .gender-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .gender-item .gender-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .chart-container h2 {
            margin-top: 0;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-align: center;
        }

        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:hover {
            background: #f5f5f5;
        }

        .highlight-card {
            border-left: 5px solid #667eea;
        }

        .danger-card {
            border-left: 5px solid #e74c3c;
        }

        .success-card {
            border-left: 5px solid #27ae60;
        }

        .warning-card {
            border-left: 5px solid #f39c12;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 400px;
        }

        .metric-trend {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 8px;
        }

        .badge-high {
            background: #e74c3c;
            color: white;
        }

        .badge-medium {
            background: #f39c12;
            color: white;
        }

        .badge-low {
            background: #27ae60;
            color: white;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }

        .debug-info {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .filter-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-left: 14px;
            align-items: center;
            gap: 8px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                padding: 20px 15px;
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
                font-size: 1.8rem;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            nav ul li a {
                padding: 10px 15px;
            }

            nav ul li a i {
                font-size: 18px;
                margin-bottom: 4px;
            }

            nav ul li a span {
                font-size: 11px;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .main-header {
                padding: 10px 15px;
            }

            .logo-img {
                width: 50px;
                height: 50px;
                margin-right: 15px;
            }

            .logo-container h1 {
                font-size: 1.4rem;
            }

            .container {
                padding: 15px 10px;
            }

            .dashboard-header h1 {
                font-size: 1.8rem;
            }

            .chart-container,
            .table-container {
                padding: 20px 15px;
            }

            .stat-card {
                padding: 20px 15px;
            }
        }
    </style>
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
        <nav>
            <?php echo generate_navigation(); ?>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fas fa-chart-line"></i> Animal Bite Surveillance Dashboard</h1>
            <div class="year-selector">
                <label for="year" style="color: white; font-weight: 600;">Select Year:</label>
                <select id="year" name="year" onchange="changeYear(this.value)">
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a type="button" class="filter-button" href="descriptive_analysis.php">
                        <i class="fas fa-search"></i> View Analysis
                    </a>
            </div>
            
        </div>

        <!-- Enhanced Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card highlight-card">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <h3>Total Cases</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['total_cases']; ?></div>
                <div class="metric-trend">Active in <?php echo $analytics_report['summary']['active_months']; ?> months</div>
            </div>
            <div class="stat-card warning-card">
                <i class="fas fa-syringe stat-icon"></i>
                <h3>RIG Administration</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['rig_completion_rate']; ?></div>
                <div class="metric-trend">For high-risk cases</div>
            </div>
            <div class="stat-card danger-card">
                <i class="fas fa-exclamation-triangle stat-icon"></i>
                <h3>High-Risk Cases</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['category3_percentage']; ?></div>
                <div class="metric-trend">Category 3 exposures</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-tint stat-icon"></i>
                <h3>Wound Washing Rate</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['washing_rate']; ?></div>
                <div class="metric-trend">Immediate first aid</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends stat-icon"></i>
                <h3>Gender Distribution</h3>
                <div class="gender-distribution">
                    <div class="gender-item">
                        <div class="gender-label">Male</div>
                        <div class="gender-value"><?php echo $analytics_report['summary']['male_percentage']; ?></div>
                    </div>
                    <div class="gender-item">
                        <div class="gender-label">Female</div>
                        <div class="gender-value"><?php echo $analytics_report['summary']['female_percentage']; ?></div>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-birthday-cake stat-icon"></i>
                <h3>Average Age</h3>
                <div class="stat-value"><?php echo $analytics_report['summary']['average_age']; ?></div>
                <div class="metric-trend">Years old</div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Monthly Trends</h2>
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Age Group Distribution</h2>
                <canvas id="ageGroupChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Animal Type Analysis</h2>
                <canvas id="animalTypeChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Vaccine Compliance Rate</h2>
                <canvas id="vaccineComplianceChart"></canvas>
            </div>
        </div>

        <!-- Top Barangays Table -->
        <div class="table-container">
            <h2><i class="fas fa-map-marker-alt"></i> Top 10 Barangays with Most Cases</h2>
            <?php if (!empty($analytics_report['barangay_analysis'])): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Barangay</th>
                            <th>Total Cases</th>
                            <th>Percentage</th>
                            <th>High-Risk Cases</th>
                            <th>Completed Treatment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        foreach ($analytics_report['barangay_analysis'] as $barangay):
                        ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($barangay['barangay']); ?></td>
                                <td><?php echo number_format(intval($barangay['count'])); ?></td>
                                <td><?php echo number_format(floatval($barangay['percentage']), 1); ?>%</td>
                                <td><?php echo number_format(intval($barangay['high_risk_cases'])); ?></td>
                                <td><?php echo number_format(intval($barangay['completed_cases'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No barangay data available for this year.</div>
            <?php endif; ?>
        </div>

        <!-- Bite Places Analysis -->
        <div class="table-container">
            <h2><i class="fas fa-location-arrow"></i> Most Common Bite Locations</h2>
            <?php if (!empty($analytics_report['bite_place_analysis'])): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Location</th>
                            <th>Cases</th>
                            <th>Percentage</th>
                            <th>High-Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        foreach ($analytics_report['bite_place_analysis'] as $place):
                        ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($place['bite_place']); ?></td>
                                <td><?php echo number_format(intval($place['count'])); ?></td>
                                <td><?php echo number_format(floatval($place['percentage']), 1); ?>%</td>
                                <td><?php echo number_format(intval($place['high_risk_cases'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No bite location data available for this year.</div>
            <?php endif; ?>
        </div>

        <!-- Charts Row 3 -->
        <div class="charts-row">
            <div class="chart-container">
                <h2>Bite Site Distribution</h2>
                <canvas id="biteSiteChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Treatment Outcome Analysis</h2>
                <canvas id="outcomeChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Define changeYear function in global scope
        window.changeYear = function(year) {
            window.location.href = '?year=' + year;
        }

        // Monthly Trends Chart - Using real data from PHP
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        const monthlyData = <?php echo json_encode($analytics_report['monthly_trends']); ?>;
        
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return months[item.month - 1] || 'Unknown';
                }),
                datasets: [{
                    label: 'Total Cases',
                    data: monthlyData.map(item => parseInt(item.monthly_cases) || 0),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'High Risk (Category 3)',
                    data: monthlyData.map(item => parseInt(item.category3_cases) || 0),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Age Group Chart - Using real data from PHP
        const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
        const ageGroupData = <?php echo json_encode($analytics_report['age_groups']); ?>;
        
        new Chart(ageGroupCtx, {
            type: 'doughnut',
            data: {
                labels: ageGroupData.map(item => item.age_group),
                datasets: [{
                    data: ageGroupData.map(item => parseInt(item.count) || 0),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Animal Type Chart - Using real data from PHP
        const animalTypeCtx = document.getElementById('animalTypeChart').getContext('2d');
        const animalTypeData = <?php echo json_encode($analytics_report['animal_types']); ?>;
        
        new Chart(animalTypeCtx, {
            type: 'bar',
            data: {
                labels: animalTypeData.map(item => item.animal_type),
                datasets: [{
                    label: 'Total Cases',
                    data: animalTypeData.map(item => parseInt(item.count) || 0),
                    backgroundColor: '#4CAF50'
                }, {
                    label: 'High Risk Cases',
                    data: animalTypeData.map(item => parseInt(item.high_risk_cases) || 0),
                    backgroundColor: '#e74c3c'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Vaccine Compliance Chart - Using real data from PHP
        const vaccineComplianceCtx = document.getElementById('vaccineComplianceChart').getContext('2d');
        const vaccineComplianceData = <?php echo json_encode($analytics_report['vaccine_compliance']); ?>;
        
        new Chart(vaccineComplianceCtx, {
            type: 'bar',
            data: {
                labels: vaccineComplianceData.map(item => item.vaccine_day),
                datasets: [{
                    label: 'Compliance Rate (%)',
                    data: vaccineComplianceData.map(item => parseFloat(item.compliance_rate) || 0),
                    backgroundColor: ['#4CAF50', '#8BC34A', '#CDDC39', '#FFC107', '#FF9800']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Bite Site Chart - Using real data from PHP
        const biteSiteCtx = document.getElementById('biteSiteChart').getContext('2d');
        const biteSiteData = <?php echo json_encode($analytics_report['bite_sites']); ?>;
        
        new Chart(biteSiteCtx, {
            type: 'pie',
            data: {
                labels: biteSiteData.map(item => item.bite_site),
                datasets: [{
                    data: biteSiteData.map(item => parseInt(item.count) || 0),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Outcome Chart - Using real data from PHP
        const outcomeCtx = document.getElementById('outcomeChart').getContext('2d');
        const outcomeData = <?php echo json_encode($analytics_report['outcomes']); ?>;
        
        new Chart(outcomeCtx, {
            type: 'doughnut',
            data: {
                labels: outcomeData.map(item => item.outcome_label),
                datasets: [{
                    data: outcomeData.map(item => parseInt(item.count) || 0),
                    backgroundColor: ['#4CAF50', '#FF9800', '#f44336', '#9E9E9E']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>