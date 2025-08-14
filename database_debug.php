<?php
// debug.php - Run this file to debug your database issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_conn.php';

echo "<h2>Database Debug Information</h2>";

try {
    $conn = connect_db();
    if (!$conn) {
        die("Database connection failed!");
    }
    
    echo "<h3>✅ Database Connection: SUCCESS</h3>";
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'sheet1'");
    if ($table_check->num_rows > 0) {
        echo "<h3>✅ Table 'sheet1': EXISTS</h3>";
    } else {
        die("<h3>❌ Table 'sheet1': NOT FOUND!</h3>");
    }
    
    // Check total records
    $total_result = $conn->query("SELECT COUNT(*) as total FROM sheet1");
    $total_row = $total_result->fetch_assoc();
    echo "<h3>📊 Total Records: " . $total_row['total'] . "</h3>";
    
    if ($total_row['total'] == 0) {
        echo "<p style='color: red;'><strong>⚠️ WARNING: No records found in the table!</strong></p>";
        echo "<p>Please check if your data has been imported correctly.</p>";
    }
    
    // Check years with data
    $years_result = $conn->query("SELECT DISTINCT YEAR(date_recorded) as year, COUNT(*) as count FROM sheet1 WHERE date_recorded IS NOT NULL GROUP BY YEAR(date_recorded) ORDER BY year DESC");
    
    echo "<h3>📅 Years with Data:</h3>";
    if ($years_result->num_rows > 0) {
        echo "<ul>";
        while ($row = $years_result->fetch_assoc()) {
            echo "<li>Year {$row['year']}: {$row['count']} records</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'><strong>❌ No valid date_recorded entries found!</strong></p>";
    }
    
    // Check current year data
    $current_year = date('Y');
    $current_year_result = $conn->query("SELECT COUNT(*) as count FROM sheet1 WHERE YEAR(date_recorded) = $current_year");
    $current_year_row = $current_year_result->fetch_assoc();
    echo "<h3>📈 Current Year ($current_year) Records: " . $current_year_row['count'] . "</h3>";
    
    // Check sample data
    $sample_result = $conn->query("SELECT * FROM sheet1 WHERE date_recorded IS NOT NULL ORDER BY date_recorded DESC LIMIT 5");
    
    echo "<h3>🔍 Sample Records (Last 5):</h3>";
    if ($sample_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>ID</th><th>Date Recorded</th><th>Name</th><th>Address</th><th>Age</th><th>Sex</th><th>Category</th><th>Outcome</th>";
        echo "</tr>";
        
        while ($row = $sample_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['date_recorded'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars($row['address'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['age'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['sex'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['category'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['outcome'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'><strong>❌ No sample records found!</strong></p>";
    }
    
    // Check for NULL values in important fields
    echo "<h3>🔍 Data Quality Check:</h3>";
    
    $null_checks = [
        'date_recorded' => 'Date Recorded',
        'age' => 'Age', 
        'sex' => 'Sex',
        'category' => 'Category',
        'outcome' => 'Outcome',
        'bite_date' => 'Bite Date',
        'vaccine_day0' => 'Vaccine Day 0'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Field</th><th>NULL Count</th><th>Valid Count</th><th>NULL Percentage</th>";
    echo "</tr>";
    
    foreach ($null_checks as $field => $label) {
        $null_result = $conn->query("SELECT 
            COUNT(*) as total,
            COUNT($field) as valid,
            COUNT(*) - COUNT($field) as null_count
            FROM sheet1");
        
        if ($null_result) {
            $null_row = $null_result->fetch_assoc();
            $null_percentage = $null_row['total'] > 0 ? ($null_row['null_count'] / $null_row['total']) * 100 : 0;
            
            $row_color = $null_percentage > 50 ? 'background-color: #ffcccc;' : ($null_percentage > 25 ? 'background-color: #fff3cd;' : '');
            
            echo "<tr style='$row_color'>";
            echo "<td>$label</td>";
            echo "<td>" . $null_row['null_count'] . "</td>";
            echo "<td>" . $null_row['valid'] . "</td>";
            echo "<td>" . number_format($null_percentage, 1) . "%</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Test specific analytics queries
    echo "<h3>🧪 Analytics Query Tests:</h3>";
    
    // Test 1: Overall statistics for current year
    echo "<h4>Test 1: Overall Statistics Query</h4>";
    $test_sql = "SELECT 
        COUNT(*) as total_cases,
        COUNT(DISTINCT MONTH(date_recorded)) as active_months,
        COALESCE(AVG(CASE WHEN category = 1 THEN 1 ELSE 0 END) * 100, 0) as category1_percentage,
        COALESCE(AVG(CASE WHEN category = 2 THEN 1 ELSE 0 END) * 100, 0) as category2_percentage,
        COALESCE(AVG(CASE WHEN category = 3 THEN 1 ELSE 0 END) * 100, 0) as category3_percentage,
        COALESCE(AVG(age), 0) as average_age
    FROM sheet1 
    WHERE YEAR(date_recorded) = $current_year";
    
    $test_result = $conn->query($test_sql);
    if ($test_result) {
        $test_row = $test_result->fetch_assoc();
        echo "<pre>";
        print_r($test_row);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Query failed: " . $conn->error . "</p>";
    }
    
    // Test 2: Monthly trends
    echo "<h4>Test 2: Monthly Trends Query</h4>";
    $monthly_sql = "SELECT 
        MONTH(date_recorded) as month,
        COUNT(*) as monthly_cases
    FROM sheet1 
    WHERE YEAR(date_recorded) = $current_year
    GROUP BY MONTH(date_recorded)
    ORDER BY month";
    
    $monthly_result = $conn->query($monthly_sql);
    if ($monthly_result) {
        if ($monthly_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f2f2f2;'><th>Month</th><th>Cases</th></tr>";
            while ($row = $monthly_result->fetch_assoc()) {
                echo "<tr><td>" . $row['month'] . "</td><td>" . $row['monthly_cases'] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No monthly data found for current year</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Monthly query failed: " . $conn->error . "</p>";
    }
    
    // Test 3: Response time analysis
    echo "<h4>Test 3: Response Time Analysis</h4>";
    $response_sql = "SELECT 
        COUNT(*) as total_records,
        COUNT(CASE WHEN vaccine_day0 IS NOT NULL AND bite_date IS NOT NULL THEN 1 END) as valid_pairs,
        COALESCE(AVG(DATEDIFF(vaccine_day0, bite_date)), 0) as avg_response_days
    FROM sheet1 
    WHERE YEAR(date_recorded) = $current_year";
    
    $response_result = $conn->query($response_sql);
    if ($response_result) {
        $response_row = $response_result->fetch_assoc();
        echo "<pre>";
        print_r($response_row);
        echo "</pre>";
        
        if ($response_row['valid_pairs'] == 0) {
            echo "<p style='color: red;'>⚠️ No valid vaccine_day0 and bite_date pairs found! This explains the null response time.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Response time query failed: " . $conn->error . "</p>";
    }
    
    // Recommendations
    echo "<h3>💡 Recommendations:</h3>";
    echo "<ul>";
    
    if ($total_row['total'] == 0) {
        echo "<li><strong>Import Data:</strong> Your table is empty. Import your data first.</li>";
    }
    
    if ($current_year_row['count'] == 0) {
        echo "<li><strong>Check Year:</strong> No data for current year ($current_year). Try selecting a different year.</li>";
    }
    
    echo "<li><strong>Check NULL Values:</strong> High NULL percentages in important fields will affect analytics.</li>";
    echo "<li><strong>Date Format:</strong> Ensure date_recorded, bite_date, and vaccine dates are in proper DATE format (YYYY-MM-DD).</li>";
    echo "<li><strong>Data Validation:</strong> Check that category values are 1, 2, or 3, and outcome values are 'C', 'Inc', 'N', or 'D'.</li>";
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Run this debug script to identify issues</li>";
echo "<li>Fix any data problems identified</li>";
echo "<li>Use the corrected analytics.php file I provided</li>";
echo "<li>Test the dashboard again</li>";
echo "</ol>";
?>