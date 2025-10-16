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

// Generate insights based on data
function generate_insights($analytics_report, $selected_year)
{
    $insights = [];

    // Temporal Pattern Analysis
    $monthly_trends = $analytics_report['monthly_trends'];
    if (!empty($monthly_trends)) {
        $peak_month = array_reduce($monthly_trends, function ($carry, $item) {
            return ($carry === null || intval($item['monthly_cases']) > intval($carry['monthly_cases'])) ? $item : $carry;
        });

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $peak_month_name = $months[$peak_month['month'] - 1];

        $insights['temporal'] = [
            'peak_month' => $peak_month_name,
            'peak_cases' => intval($peak_month['monthly_cases']),
            'interpretation' => "The highest incidence of animal bites occurred in {$peak_month_name} with " . intval($peak_month['monthly_cases']) . " cases. This seasonal pattern suggests increased outdoor activities and animal interactions during this period."
        ];

        // Calculate quarterly trends
        $q1 = array_sum(array_map(function ($m) {
            return $m['month'] <= 3 ? intval($m['monthly_cases']) : 0;
        }, $monthly_trends));
        $q2 = array_sum(array_map(function ($m) {
            return $m['month'] > 3 && $m['month'] <= 6 ? intval($m['monthly_cases']) : 0;
        }, $monthly_trends));
        $q3 = array_sum(array_map(function ($m) {
            return $m['month'] > 6 && $m['month'] <= 9 ? intval($m['monthly_cases']) : 0;
        }, $monthly_trends));
        $q4 = array_sum(array_map(function ($m) {
            return $m['month'] > 9 ? intval($m['monthly_cases']) : 0;
        }, $monthly_trends));

        $quarters = ['Q1' => $q1, 'Q2' => $q2, 'Q3' => $q3, 'Q4' => $q4];
        arsort($quarters);
        $peak_quarter = array_key_first($quarters);

        $insights['quarterly'] = [
            'peak' => $peak_quarter,
            'cases' => $quarters[$peak_quarter],
            'interpretation' => "{$peak_quarter} showed the highest concentration of cases with " . $quarters[$peak_quarter] . " incidents, indicating a clear temporal pattern that requires targeted prevention efforts."
        ];
    }

    // Geographic Pattern Analysis
    if (!empty($analytics_report['barangay_analysis'])) {
        $top_3_barangays = array_slice($analytics_report['barangay_analysis'], 0, 3);
        $total_in_top_3 = array_sum(array_map(function ($b) {
            return intval($b['count']);
        }, $top_3_barangays));
        $total_cases = intval($analytics_report['summary']['total_cases']);
        $concentration_rate = ($total_cases > 0) ? ($total_in_top_3 / $total_cases) * 100 : 0;

        $insights['geographic'] = [
            'top_barangays' => array_map(function ($b) {
                return $b['barangay'];
            }, $top_3_barangays),
            'concentration_rate' => number_format($concentration_rate, 1),
            'interpretation' => "Geographic concentration analysis reveals that " . number_format($concentration_rate, 1) . "% of all cases are concentrated in just 3 barangays: " . implode(', ', array_map(function ($b) {
                return $b['barangay'];
            }, $top_3_barangays)) . ". This geographic clustering suggests specific environmental or socioeconomic factors contributing to higher exposure rates."
        ];
    }

    // Demographic Pattern Analysis
    $age_groups = $analytics_report['age_groups'];
    if (!empty($age_groups)) {
        $most_affected = array_reduce($age_groups, function ($carry, $item) {
            return ($carry === null || intval($item['count']) > intval($carry['count'])) ? $item : $carry;
        });

        $insights['demographic'] = [
            'vulnerable_group' => $most_affected['age_group'],
            'cases' => intval($most_affected['count']),
            'interpretation' => "Age distribution analysis identifies the {$most_affected['age_group']} age group as the most vulnerable demographic with " . intval($most_affected['count']) . " cases. This pattern indicates age-specific risk factors requiring tailored prevention strategies."
        ];
    }

    // Risk Assessment Insights
    $total_cases = intval($analytics_report['summary']['total_cases']);
    $category3_pct = floatval(str_replace('%', '', $analytics_report['summary']['category3_percentage']));

    $insights['risk'] = [
        'high_risk_rate' => $category3_pct,
        'interpretation' => "Risk stratification shows that {$category3_pct}% of cases are classified as Category 3 (high-risk) exposures requiring immediate and comprehensive post-exposure prophylaxis. This elevated rate indicates the severity of animal bite incidents in the community."
    ];

    // Treatment Compliance Insights
    $vaccine_compliance = $analytics_report['vaccine_compliance'];
    if (!empty($vaccine_compliance)) {
        $day0_compliance = floatval($vaccine_compliance[0]['compliance_rate'] ?? 0);
        $day28_compliance = floatval($vaccine_compliance[4]['compliance_rate'] ?? 0);
        $dropout_rate = $day0_compliance - $day28_compliance;

        $insights['compliance'] = [
            'initial_compliance' => $day0_compliance,
            'final_compliance' => $day28_compliance,
            'dropout_rate' => $dropout_rate,
            'interpretation' => "Treatment adherence analysis reveals a " . number_format($dropout_rate, 1) . "% dropout rate from initial vaccination (Day 0: {$day0_compliance}%) to final dose completion (Day 28: {$day28_compliance}%). This attrition pattern highlights the need for enhanced patient follow-up and education mechanisms."
        ];
    }

    // Animal Type Pattern
    $animal_types = $analytics_report['animal_types'];
    if (!empty($animal_types)) {
        $dominant_animal = $animal_types[0];
        $animal_percentage = ($total_cases > 0) ? (intval($dominant_animal['count']) / $total_cases) * 100 : 0;

        $insights['animal'] = [
            'dominant_type' => $dominant_animal['animal_type'],
            'percentage' => number_format($animal_percentage, 1),
            'interpretation' => "{$dominant_animal['animal_type']} accounts for " . number_format($animal_percentage, 1) . "% of all animal bite cases, establishing it as the primary vector of concern. This dominance necessitates species-specific intervention strategies including population control and vaccination campaigns."
        ];
    }

    return $insights;
}

$insights = generate_insights($analytics_report, $selected_year);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descriptive Analysis & Insights - <?php echo $selected_year; ?></title>
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
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #4a8f5f;
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
            background: white;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #4a8f5f;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
            gap: 20px;
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
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(74, 143, 95, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            padding: 30px;
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(45, 95, 63, 0.3);
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .dashboard-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .year-selector {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .year-selector label {
            color: white;
            font-weight: 600;
        }

        .year-selector select {
            padding: 10px 20px;
            border: 2px solid white;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            color: #2d5f3f;
            font-weight: 600;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .year-selector select:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.2);
        }

        .section-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #2d5f3f;
        }

        .section-header h2 {
            color: #2d5f3f;
            font-size: 1.8rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-header h2 i {
            color: #4a8f5f;
        }

        .section-header p {
            color: #555;
            font-size: 1rem;
            line-height: 1.8;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .insight-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #2d5f3f;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .insight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(45, 95, 63, 0.15);
        }

        .insight-card.temporal {
            border-left-color: #3498db;
        }

        .insight-card.geographic {
            border-left-color: #e74c3c;
        }

        .insight-card.demographic {
            border-left-color: #f39c12;
        }

        .insight-card.risk {
            border-left-color: #c0392b;
        }

        .insight-card.compliance {
            border-left-color: #27ae60;
        }

        .insight-card.animal {
            border-left-color: #8e44ad;
        }

        .insight-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .insight-icon {
            font-size: 2.5rem;
            color: #2d5f3f;
        }

        .insight-card.temporal .insight-icon {
            color: #3498db;
        }

        .insight-card.geographic .insight-icon {
            color: #e74c3c;
        }

        .insight-card.demographic .insight-icon {
            color: #f39c12;
        }

        .insight-card.risk .insight-icon {
            color: #c0392b;
        }

        .insight-card.compliance .insight-icon {
            color: #27ae60;
        }

        .insight-card.animal .insight-icon {
            color: #8e44ad;
        }

        .insight-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }

        .insight-metric {
            font-size: 2rem;
            font-weight: bold;
            color: #2d5f3f;
            margin: 15px 0;
        }

        .insight-card.temporal .insight-metric {
            color: #3498db;
        }

        .insight-card.geographic .insight-metric {
            color: #e74c3c;
        }

        .insight-card.demographic .insight-metric {
            color: #f39c12;
        }

        .insight-card.risk .insight-metric {
            color: #c0392b;
        }

        .insight-card.compliance .insight-metric {
            color: #27ae60;
        }

        .insight-card.animal .insight-metric {
            color: #8e44ad;
        }

        .insight-interpretation {
            color: #555;
            line-height: 1.8;
            font-size: 0.95rem;
            text-align: justify;
        }

        .key-finding {
            background: rgba(45, 95, 63, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #2d5f3f;
        }

        .key-finding strong {
            color: #2d5f3f;
        }

        .analysis-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d5f3f;
            margin-bottom: 30px;
        }

        .analysis-section h3 {
            color: #2d5f3f;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .analysis-section h3 i {
            color: #4a8f5f;
        }

        .pattern-list {
            list-style: none;
            padding: 0;
        }

        .pattern-list li {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8fdf9;
            border-radius: 8px;
            border-left: 4px solid #4a8f5f;
        }

        .pattern-list li strong {
            color: #2d5f3f;
            display: block;
            margin-bottom: 5px;
        }

        .recommendation-box {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(45, 95, 63, 0.3);
        }

        .recommendation-box h4 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recommendation-box ul {
            list-style: none;
            padding: 0;
        }

        .recommendation-box ul li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            line-height: 1.6;
        }

        .recommendation-box ul li:before {
            content: "▸";
            position: absolute;
            left: 10px;
            font-weight: bold;
            color: #a5d6a7;
        }

        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d5f3f;
            margin-bottom: 30px;
        }

        .chart-container h3 {
            color: #2d5f3f;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.4rem;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 400px;
        }

        .trend-indicator {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .trend-up {
            background: #fee2e2;
            color: #c0392b;
        }

        .trend-down {
            background: #d1fae5;
            color: #27ae60;
        }

        .trend-stable {
            background: #fef3c7;
            color: #f39c12;
        }

        @media (max-width: 1200px) {
            .container {
                padding: 20px 15px;
            }

            .insights-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
                justify-content: center;
                gap: 10px;
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

            .dashboard-header {
                padding: 20px;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .year-selector select {
                display: block;
                margin: 10px auto 0;
                width: 100%;
                max-width: 200px;
            }

            .insights-grid {
                grid-template-columns: 1fr;
            }

            .insight-card {
                padding: 20px;
            }

            .insight-metric {
                font-size: 1.6rem;
            }

            .section-header,
            .analysis-section,
            .chart-container {
                padding: 20px;
            }

            .recommendation-box {
                padding: 20px;
            }

            .recommendation-box h4 {
                font-size: 1.1rem;
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
                font-size: 1.6rem;
            }

            .dashboard-header p {
                font-size: 0.95rem;
            }

            .insight-card {
                padding: 15px;
            }

            .insight-icon {
                font-size: 2rem;
            }

            .insight-title {
                font-size: 1.1rem;
            }

            .insight-metric {
                font-size: 1.4rem;
            }

            .pattern-list li {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
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
            <h1><i class="fas fa-brain"></i> Descriptive Analysis & Insights</h1>
            <p>Interpreting historical and current data to identify trends, patterns, and actionable insights</p>
            <div class="year-selector">
                <label for="year" style="color: white; font-weight: 600;">Analysis Period:</label>
                <select id="year" name="year" onchange="changeYear(this.value)">
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="section-header">
            <h2><i class="fas fa-file-alt"></i> Executive Summary</h2>
            <p>
                Analysis of <?php echo intval($analytics_report['summary']['total_cases']); ?> animal bite cases in <?php echo $selected_year; ?>
                reveals critical patterns in temporal distribution, geographic concentration, and demographic vulnerability.
                This descriptive analysis provides evidence-based insights to inform targeted prevention strategies and
                resource allocation decisions for the City Health Office of San Pablo.
            </p>
        </div>

        <!-- Key Insights Grid -->
        <div class="insights-grid">
            <!-- Temporal Pattern Insight -->
            <?php if (isset($insights['temporal'])): ?>
                <div class="insight-card temporal">
                    <div class="insight-header">
                        <i class="fas fa-calendar-alt insight-icon"></i>
                        <div class="insight-title">Temporal Patterns</div>
                    </div>
                    <div class="insight-metric"><?php echo $insights['temporal']['peak_month']; ?></div>
                    <div class="key-finding">
                        <strong>Peak Period:</strong> <?php echo $insights['temporal']['peak_cases']; ?> cases
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['temporal']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Geographic Pattern Insight -->
            <?php if (isset($insights['geographic'])): ?>
                <div class="insight-card geographic">
                    <div class="insight-header">
                        <i class="fas fa-map-marked-alt insight-icon"></i>
                        <div class="insight-title">Geographic Concentration</div>
                    </div>
                    <div class="insight-metric"><?php echo $insights['geographic']['concentration_rate']; ?>%</div>
                    <div class="key-finding">
                        <strong>High-Risk Areas:</strong> <?php echo implode(', ', $insights['geographic']['top_barangays']); ?>
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['geographic']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Demographic Pattern Insight -->
            <?php if (isset($insights['demographic'])): ?>
                <div class="insight-card demographic">
                    <div class="insight-header">
                        <i class="fas fa-users insight-icon"></i>
                        <div class="insight-title">Demographic Vulnerability</div>
                    </div>
                    <div class="insight-metric"><?php echo $insights['demographic']['vulnerable_group']; ?></div>
                    <div class="key-finding">
                        <strong>Cases:</strong> <?php echo $insights['demographic']['cases']; ?> incidents
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['demographic']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Risk Assessment Insight -->
            <?php if (isset($insights['risk'])): ?>
                <div class="insight-card risk">
                    <div class="insight-header">
                        <i class="fas fa-exclamation-triangle insight-icon"></i>
                        <div class="insight-title">Risk Stratification</div>
                    </div>
                    <div class="insight-metric"><?php echo number_format($insights['risk']['high_risk_rate'], 1); ?>%</div>
                    <div class="key-finding">
                        <strong>High-Risk Classification:</strong> Category 3 Exposures
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['risk']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Treatment Compliance Insight -->
            <?php if (isset($insights['compliance'])): ?>
                <div class="insight-card compliance">
                    <div class="insight-header">
                        <i class="fas fa-clipboard-check insight-icon"></i>
                        <div class="insight-title">Treatment Adherence</div>
                    </div>
                    <div class="insight-metric"><?php echo number_format($insights['compliance']['dropout_rate'], 1); ?>%</div>
                    <div class="key-finding">
                        <strong>Dropout Rate:</strong> Initial to Final Dose
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['compliance']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Animal Type Insight -->
            <?php if (isset($insights['animal'])): ?>
                <div class="insight-card animal">
                    <div class="insight-header">
                        <i class="fas fa-paw insight-icon"></i>
                        <div class="insight-title">Primary Vector Analysis</div>
                    </div>
                    <div class="insight-metric"><?php echo $insights['animal']['dominant_type']; ?></div>
                    <div class="key-finding">
                        <strong>Dominance:</strong> <?php echo $insights['animal']['percentage']; ?>% of cases
                    </div>
                    <div class="insight-interpretation">
                        <?php echo $insights['animal']['interpretation']; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quarterly Trend Analysis -->
        <?php if (isset($insights['quarterly'])): ?>
            <div class="analysis-section">
                <h3><i class="fas fa-chart-line"></i> Quarterly Trend Analysis</h3>
                <div class="key-finding">
                    <strong>Peak Quarter:</strong> <?php echo $insights['quarterly']['peak']; ?> with <?php echo $insights['quarterly']['cases']; ?> cases
                </div>
                <p style="margin-top: 15px; color: #555; line-height: 1.8;">
                    <?php echo $insights['quarterly']['interpretation']; ?> Understanding quarterly variations
                    enables proactive resource planning and timely implementation of preventive measures during high-incidence periods.
                </p>
                <div class="chart-container" style="margin-top: 20px;">
                    <h3>Monthly Distribution Pattern</h3>
                    <canvas id="monthlyPatternChart"></canvas>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pattern Recognition -->
        <div class="analysis-section">
            <h3><i class="fas fa-search"></i> Identified Patterns & Correlations</h3>
            <ul class="pattern-list">
                <li>
                    <strong>Seasonal Correlation:</strong>
                    Higher incidence rates correlate with warmer months and school vacation periods, suggesting
                    increased outdoor activities and human-animal interactions.
                </li>
                <li>
                    <strong>Geographic Clustering:</strong>
                    Case concentration in specific barangays indicates localized factors such as stray animal populations,
                    environmental conditions, or socioeconomic determinants requiring targeted interventions.
                </li>
                <li>
                    <strong>Age-Specific Vulnerability:</strong>
                    Younger age groups show disproportionate representation, highlighting the need for
                    school-based education programs and parental awareness campaigns.
                </li>
                <li>
                    <strong>Treatment Completion Gap:</strong>
                    Progressive decline in vaccination compliance across the treatment schedule reveals
                    systemic barriers to patient retention and follow-up effectiveness.
                </li>
            </ul>
        </div>

        <!-- Strategic Recommendations -->
        <div class="recommendation-box">
            <h4><i class="fas fa-lightbulb"></i> Evidence-Based Recommendations</h4>
            <ul>
                <li>Implement intensified prevention campaigns during identified peak months (<?php echo $insights['temporal']['peak_month'] ?? 'high-risk periods'; ?>)</li>
                <li>Deploy mobile vaccination units to high-concentration barangays for improved accessibility</li>
                <li>Develop age-appropriate educational interventions targeting vulnerable demographics</li>
                <li>Establish enhanced follow-up protocols including SMS reminders and community health worker visits</li>
                <li>Collaborate with local government units for animal population management in hotspot areas</li>
                <li>Create quarterly review mechanisms to monitor trend deviations and intervention effectiveness</li>
            </ul>
        </div>

        <!-- Comparative Analysis Charts -->
        <div class="chart-container">
            <h3>Geographic Distribution Heatmap</h3>
            <canvas id="geographicChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Age Group Vulnerability Profile</h3>
            <canvas id="ageVulnerabilityChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Treatment Compliance Funnel</h3>
            <canvas id="complianceFunnelChart"></canvas>
        </div>

        <!-- Conclusion -->
        <div class="section-header">
            <h2><i class="fas fa-flag-checkered"></i> Analytical Conclusion</h2>
            <p>
                The descriptive analysis of <?php echo $selected_year; ?> data reveals actionable patterns across temporal,
                geographic, and demographic dimensions. The identified trends provide a foundation for evidence-based
                decision-making in resource allocation, targeted prevention strategies, and policy formulation.
                Continuous monitoring and periodic re-analysis are recommended to track intervention effectiveness
                and adapt strategies to emerging patterns in animal bite epidemiology.
            </p>
        </div>
    </div>

    <script>
        // Define changeYear function in global scope
        window.changeYear = function(year) {
            window.location.href = '?year=' + year;
        }

        // Monthly Pattern Chart
        const monthlyPatternCtx = document.getElementById('monthlyPatternChart').getContext('2d');
        const monthlyData = <?php echo json_encode($analytics_report['monthly_trends']); ?>;

        new Chart(monthlyPatternCtx, {
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
                    tension: 0.4,
                    borderWidth: 3
                }, {
                    label: 'High Risk Cases',
                    data: monthlyData.map(item => parseInt(item.category3_cases) || 0),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' cases';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Cases'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Geographic Distribution Chart
        const geographicCtx = document.getElementById('geographicChart').getContext('2d');
        const barangayData = <?php echo json_encode($analytics_report['barangay_analysis']); ?>;
        const topBarangays = barangayData.slice(0, 10);

        new Chart(geographicCtx, {
            type: 'bar',
            data: {
                labels: topBarangays.map(item => item.barangay),
                datasets: [{
                    label: 'Total Cases',
                    data: topBarangays.map(item => parseInt(item.count) || 0),
                    backgroundColor: '#667eea',
                    borderRadius: 5
                }, {
                    label: 'High-Risk Cases',
                    data: topBarangays.map(item => parseInt(item.high_risk_cases) || 0),
                    backgroundColor: '#e74c3c',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' cases';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Cases'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Barangay'
                        }
                    }
                }
            }
        });

        // Age Vulnerability Chart
        const ageVulnerabilityCtx = document.getElementById('ageVulnerabilityChart').getContext('2d');
        const ageGroupData = <?php echo json_encode($analytics_report['age_groups']); ?>;

        new Chart(ageVulnerabilityCtx, {
            type: 'bar',
            data: {
                labels: ageGroupData.map(item => item.age_group),
                datasets: [{
                    label: 'Number of Cases',
                    data: ageGroupData.map(item => parseInt(item.count) || 0),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = ageGroupData.reduce((sum, item) => sum + parseInt(item.count || 0), 0);
                                const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                return context.parsed.y + ' cases (' + percentage + '%)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Cases'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Age Group'
                        }
                    }
                }
            }
        });

        // Compliance Funnel Chart
        const complianceFunnelCtx = document.getElementById('complianceFunnelChart').getContext('2d');
        const vaccineComplianceData = <?php echo json_encode($analytics_report['vaccine_compliance']); ?>;

        new Chart(complianceFunnelCtx, {
            type: 'bar',
            data: {
                labels: vaccineComplianceData.map(item => item.vaccine_day),
                datasets: [{
                    label: 'Compliance Rate (%)',
                    data: vaccineComplianceData.map(item => parseFloat(item.compliance_rate) || 0),
                    backgroundColor: ['#27ae60', '#2ecc71', '#f1c40f', '#e67e22', '#e74c3c'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Compliance: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Compliance Rate (%)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Vaccination Schedule'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>