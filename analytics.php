<?php
require_once 'db_conn.php';

function get_descriptive_analytics($year) {
    try {
        $conn = connect_db();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        $analytics = array();
        
        // 1. Overall Statistics (Enhanced)
        $sql = "SELECT 
            COUNT(*) as total_cases,
            COUNT(DISTINCT MONTH(date_recorded)) as active_months,
            AVG(CASE WHEN category = 1 THEN 1 ELSE 0 END) * 100 as category1_percentage,
            AVG(CASE WHEN category = 2 THEN 1 ELSE 0 END) * 100 as category2_percentage,
            AVG(CASE WHEN category = 3 THEN 1 ELSE 0 END) * 100 as category3_percentage,
            AVG(CASE WHEN rig_amount IS NOT NULL AND rig_amount != '' THEN 1 ELSE 0 END) * 100 as rig_completion_rate,
            AVG(CASE WHEN outcome = 'C' THEN 1 ELSE 0 END) * 100 as complete_outcome_rate,
            AVG(CASE WHEN bite_type = 'B' THEN 1 ELSE 0 END) * 100 as bite_percentage,
            AVG(CASE WHEN washing_of_bite = 'Y' THEN 1 ELSE 0 END) * 100 as washing_rate,
            AVG(age) as average_age,
            COUNT(CASE WHEN sex = 'M' THEN 1 END) * 100.0 / COUNT(*) as male_percentage,
            COUNT(CASE WHEN sex = 'F' THEN 1 END) * 100.0 / COUNT(*) as female_percentage,
            AVG(DATEDIFF(vaccine_day0, bite_date)) as avg_days_to_first_vaccine
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
        
        // 2. Monthly Trends (Enhanced)
        $sql = "SELECT 
            MONTH(date_recorded) as month,
            COUNT(*) as monthly_cases,
            COUNT(CASE WHEN category = 1 THEN 1 END) as category1_cases,
            COUNT(CASE WHEN category = 2 THEN 1 END) as category2_cases,
            COUNT(CASE WHEN category = 3 THEN 1 END) as category3_cases,
            COUNT(CASE WHEN bite_type = 'B' THEN 1 END) as bite_cases,
            COUNT(CASE WHEN outcome = 'C' THEN 1 END) as completed_cases
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
        
        // 3. Barangay/Address Analysis (TOP 10)
        $sql = "SELECT 
            TRIM(UPPER(address)) as barangay,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases,
            COUNT(CASE WHEN outcome = 'C' THEN 1 END) as completed_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND address IS NOT NULL AND TRIM(address) != ''
        GROUP BY TRIM(UPPER(address))
        ORDER BY count DESC
        LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['barangay_analysis'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['barangay_analysis'][] = $row;
        }
        
        // 4. Bite Place Analysis (TOP 10)
        $sql = "SELECT 
            TRIM(UPPER(bite_place)) as bite_place,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND bite_place IS NOT NULL AND TRIM(bite_place) != ''
        GROUP BY TRIM(UPPER(bite_place))
        ORDER BY count DESC
        LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['bite_place_analysis'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['bite_place_analysis'][] = $row;
        }
        
        // 5. Age Group Analysis
        $sql = "SELECT 
            CASE 
                WHEN age < 5 THEN '0-4 years'
                WHEN age BETWEEN 5 AND 14 THEN '5-14 years'
                WHEN age BETWEEN 15 AND 29 THEN '15-29 years'
                WHEN age BETWEEN 30 AND 44 THEN '30-44 years'
                WHEN age BETWEEN 45 AND 59 THEN '45-59 years'
                WHEN age >= 60 THEN '60+ years'
                ELSE 'Unknown'
            END as age_group,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY 
            CASE 
                WHEN age < 5 THEN '0-4 years'
                WHEN age BETWEEN 5 AND 14 THEN '5-14 years'
                WHEN age BETWEEN 15 AND 29 THEN '15-29 years'
                WHEN age BETWEEN 30 AND 44 THEN '30-44 years'
                WHEN age BETWEEN 45 AND 59 THEN '45-59 years'
                WHEN age >= 60 THEN '60+ years'
                ELSE 'Unknown'
            END
        ORDER BY 
            CASE 
                WHEN age < 5 THEN 1
                WHEN age BETWEEN 5 AND 14 THEN 2
                WHEN age BETWEEN 15 AND 29 THEN 3
                WHEN age BETWEEN 30 AND 44 THEN 4
                WHEN age BETWEEN 45 AND 59 THEN 5
                WHEN age >= 60 THEN 6
                ELSE 7
            END";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['age_groups'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['age_groups'][] = $row;
        }
        
        // 6. Animal Type Analysis (Enhanced)
        $sql = "SELECT 
            TRIM(UPPER(animal_type)) as animal_type,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases,
            COUNT(CASE WHEN bite_type = 'B' THEN 1 END) as bite_cases,
            COUNT(CASE WHEN animal_status = 'Dead' THEN 1 END) as dead_animals
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND animal_type IS NOT NULL AND TRIM(animal_type) != ''
        GROUP BY TRIM(UPPER(animal_type))
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['animal_types'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['animal_types'][] = $row;
        }
        
        // 7. Bite Site Analysis (Enhanced)
        $sql = "SELECT 
            TRIM(UPPER(bite_site)) as bite_site,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND bite_site IS NOT NULL AND TRIM(bite_site) != ''
        GROUP BY TRIM(UPPER(bite_site))
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['bite_sites'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['bite_sites'][] = $row;
        }
        
        // 8. Outcome Analysis (Enhanced)
        $sql = "SELECT 
            CASE outcome
                WHEN 'C' THEN 'Complete'
                WHEN 'Inc' THEN 'Incomplete'
                WHEN 'N' THEN 'Not Started'
                WHEN 'D' THEN 'Died'
                ELSE outcome
            END as outcome_label,
            outcome,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY outcome
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['outcomes'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['outcomes'][] = $row;
        }
        
        // 9. Treatment Compliance Analysis
        $sql = "SELECT 
            'Day 0' as vaccine_day,
            COUNT(CASE WHEN vaccine_day0 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day0 IS NOT NULL THEN 1 END) as missed,
            COUNT(CASE WHEN vaccine_day0 IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 3' as vaccine_day,
            COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) as missed,
            COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 7' as vaccine_day,
            COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) as missed,
            COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 14' as vaccine_day,
            COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) as missed,
            COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 28-30' as vaccine_day,
            COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) as missed,
            COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiii", $year, $year, $year, $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['vaccine_compliance'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['vaccine_compliance'][] = $row;
        }
        
        // 10. Animal Status Analysis
        $sql = "SELECT 
            animal_status,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ? AND animal_status IS NOT NULL) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND animal_status IS NOT NULL
        GROUP BY animal_status
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['animal_status'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['animal_status'][] = $row;
        }
        
        // 11. Response Time Analysis
        $sql = "SELECT 
            AVG(DATEDIFF(vaccine_day0, bite_date)) as avg_response_days,
            COUNT(CASE WHEN DATEDIFF(vaccine_day0, bite_date) <= 1 THEN 1 END) as within_24hrs,
            COUNT(CASE WHEN DATEDIFF(vaccine_day0, bite_date) BETWEEN 2 AND 3 THEN 1 END) as within_72hrs,
            COUNT(CASE WHEN DATEDIFF(vaccine_day0, bite_date) > 3 THEN 1 END) as beyond_72hrs,
            COUNT(CASE WHEN vaccine_day0 IS NOT NULL THEN 1 END) as total_with_vaccine
        FROM sheet1 
        WHERE YEAR(date_recorded) = ? AND vaccine_day0 IS NOT NULL AND bite_date IS NOT NULL";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['response_time'] = $result->fetch_assoc();
        
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
            'complete_outcome_rate' => isset($analytics['overall']['complete_outcome_rate']) ? number_format($analytics['overall']['complete_outcome_rate'], 1) . '%' : '0%',
            'bite_percentage' => isset($analytics['overall']['bite_percentage']) ? number_format($analytics['overall']['bite_percentage'], 1) . '%' : '0%',
            'washing_rate' => isset($analytics['overall']['washing_rate']) ? number_format($analytics['overall']['washing_rate'], 1) . '%' : '0%',
            'average_age' => isset($analytics['overall']['average_age']) ? number_format($analytics['overall']['average_age'], 1) : '0',
            'male_percentage' => isset($analytics['overall']['male_percentage']) ? number_format($analytics['overall']['male_percentage'], 1) . '%' : '0%',
            'female_percentage' => isset($analytics['overall']['female_percentage']) ? number_format($analytics['overall']['female_percentage'], 1) . '%' : '0%',
            'avg_days_to_first_vaccine' => isset($analytics['overall']['avg_days_to_first_vaccine']) ? number_format($analytics['overall']['avg_days_to_first_vaccine'], 1) : 'N/A'
        ),
        'monthly_trends' => $analytics['monthly_trends'] ?? array(),
        'barangay_analysis' => $analytics['barangay_analysis'] ?? array(),
        'bite_place_analysis' => $analytics['bite_place_analysis'] ?? array(),
        'age_groups' => $analytics['age_groups'] ?? array(),
        'animal_types' => $analytics['animal_types'] ?? array(),
        'outcomes' => $analytics['outcomes'] ?? array(),
        'bite_sites' => $analytics['bite_sites'] ?? array(),
        'vaccine_compliance' => $analytics['vaccine_compliance'] ?? array(),
        'animal_status' => $analytics['animal_status'] ?? array(),
        'response_time' => $analytics['response_time'] ?? array()
    );
    
    return $report;
}

?>