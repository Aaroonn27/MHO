<?php
require_once 'db_conn.php';

function get_descriptive_analytics($year) {
    try {
        $conn = connect_db();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        $analytics = array();
        
        // 1. Overall Statistics
        $sql = "SELECT 
            COUNT(*) as total_cases,
            COUNT(DISTINCT MONTH(date_recorded)) as active_months,
            AVG(CASE WHEN category = 1 THEN 1 ELSE 0 END) * 100 as category1_percentage,
            AVG(CASE WHEN category = 2 THEN 1 ELSE 0 END) * 100 as category2_percentage,
            AVG(CASE WHEN category = 3 THEN 1 ELSE 0 END) * 100 as category3_percentage,
            AVG(CASE WHEN rig_amount IS NOT NULL AND rig_amount != '' THEN 1 ELSE 0 END) * 100 as rig_completion_rate,
            AVG(CASE WHEN outcome = 'C' THEN 1 ELSE 0 END) * 100 as complete_outcome_rate
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $year);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $analytics['overall'] = $result->fetch_assoc();
        
        // 2. Monthly Trends
        $sql = "SELECT 
            MONTH(date_recorded) as month,
            COUNT(*) as monthly_cases,
            COUNT(CASE WHEN category = 1 THEN 1 END) as category1_cases,
            COUNT(CASE WHEN category = 2 THEN 1 END) as category2_cases,
            COUNT(CASE WHEN category = 3 THEN 1 END) as category3_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY MONTH(date_recorded)
        ORDER BY month";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['monthly_trends'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['monthly_trends'][] = $row;
        }
        
        // 3. Animal Type Analysis
        $sql = "SELECT 
            animal_type,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY animal_type
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['animal_types'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['animal_types'][] = $row;
        }
        
        // 4. Outcome Analysis
        $sql = "SELECT 
            outcome,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY outcome";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['outcomes'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['outcomes'][] = $row;
        }
        
        // 5. Bite Site Analysis
        $sql = "SELECT 
            bite_site,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY bite_site
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['bite_sites'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['bite_sites'][] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $analytics;
    } catch (Exception $e) {
        error_log("Analytics Error: " . $e->getMessage());
        return array(
            'error' => true,
            'message' => $e->getMessage()
        );
    }
}

function generate_analytics_report($year) {
    $analytics = get_descriptive_analytics($year);
    
    // Check if we got an error response
    if (isset($analytics['error'])) {
        return array(
            'error' => true,
            'message' => $analytics['message']
        );
    }
    
    // Format the data for display with null checks
    $report = array(
        'summary' => array(
            'total_cases' => isset($analytics['overall']['total_cases']) ? number_format($analytics['overall']['total_cases']) : '0',
            'active_months' => isset($analytics['overall']['active_months']) ? $analytics['overall']['active_months'] : '0',
            'category1_percentage' => isset($analytics['overall']['category1_percentage']) ? number_format($analytics['overall']['category1_percentage'], 1) . '%' : '0%',
            'category2_percentage' => isset($analytics['overall']['category2_percentage']) ? number_format($analytics['overall']['category2_percentage'], 1) . '%' : '0%',
            'category3_percentage' => isset($analytics['overall']['category3_percentage']) ? number_format($analytics['overall']['category3_percentage'], 1) . '%' : '0%',
            'rig_completion_rate' => isset($analytics['overall']['rig_completion_rate']) ? number_format($analytics['overall']['rig_completion_rate'], 1) . '%' : '0%',
            'complete_outcome_rate' => isset($analytics['overall']['complete_outcome_rate']) ? number_format($analytics['overall']['complete_outcome_rate'], 1) . '%' : '0%'
        ),
        'monthly_trends' => $analytics['monthly_trends'] ?? array(),
        'animal_types' => $analytics['animal_types'] ?? array(),
        'outcomes' => $analytics['outcomes'] ?? array(),
        'bite_sites' => $analytics['bite_sites'] ?? array()
    );
    
    return $report;
}
?> 