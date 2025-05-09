<?php
require_once 'db_conn.php';

function get_descriptive_analytics($year) {
    $conn = connect_db();
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
    $stmt->bind_param("i", $year);
    $stmt->execute();
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
}

function generate_analytics_report($year) {
    $analytics = get_descriptive_analytics($year);
    
    // Format the data for display
    $report = array(
        'summary' => array(
            'total_cases' => number_format($analytics['overall']['total_cases']),
            'active_months' => $analytics['overall']['active_months'],
            'category1_percentage' => number_format($analytics['overall']['category1_percentage'], 1) . '%',
            'category2_percentage' => number_format($analytics['overall']['category2_percentage'], 1) . '%',
            'category3_percentage' => number_format($analytics['overall']['category3_percentage'], 1) . '%',
            'rig_completion_rate' => number_format($analytics['overall']['rig_completion_rate'], 1) . '%',
            'complete_outcome_rate' => number_format($analytics['overall']['complete_outcome_rate'], 1) . '%'
        ),
        'monthly_trends' => $analytics['monthly_trends'],
        'animal_types' => $analytics['animal_types'],
        'outcomes' => $analytics['outcomes'],
        'bite_sites' => $analytics['bite_sites']
    );
    
    return $report;
}
?> 