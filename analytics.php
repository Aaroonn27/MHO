<?php
require_once 'db_conn.php';


function get_descriptive_analytics($year) {
    try {
        $conn = connect_db();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        // Debug: Check if we have any data for the year
        $debug_sql = "SELECT COUNT(*) as total FROM sheet1 WHERE YEAR(date_recorded) = ?";
        $debug_stmt = $conn->prepare($debug_sql);
        $debug_stmt->bind_param("i", $year);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();
        $debug_row = $debug_result->fetch_assoc();
        
        error_log("Debug: Found {$debug_row['total']} records for year {$year}");
        
        if ($debug_row['total'] == 0) {
            // Return empty structure if no data found
            return array(
                'overall' => array(
                    'total_cases' => 0,
                    'active_months' => 0,
                    'category1_percentage' => 0,
                    'category2_percentage' => 0,
                    'category3_percentage' => 0,
                    'rig_completion_rate' => 0,
                    'complete_outcome_rate' => 0,
                    'bite_percentage' => 0,
                    'washing_rate' => 0,
                    'average_age' => 0,
                    'male_percentage' => 0,
                    'female_percentage' => 0,
                    'avg_days_to_first_vaccine' => 0
                ),
                'monthly_trends' => array(),
                'barangay_analysis' => array(),
                'bite_place_analysis' => array(),
                'age_groups' => array(),
                'animal_types' => array(),
                'outcomes' => array(),
                'bite_sites' => array(),
                'vaccine_compliance' => array(),
                'animal_status' => array(),
                'response_time' => array(
                    'avg_response_days' => 0,
                    'within_24hrs' => 0,
                    'within_72hrs' => 0,
                    'beyond_72hrs' => 0,
                    'total_with_vaccine' => 0
                )
            );
        }
        
        $analytics = array();
        
        // 1. Overall Statistics (Enhanced with better null handling)
        $sql = "SELECT 
            COUNT(*) as total_cases,
            COUNT(DISTINCT MONTH(date_recorded)) as active_months,
            COALESCE(AVG(CASE WHEN category = 1 THEN 1 ELSE 0 END) * 100, 0) as category1_percentage,
            COALESCE(AVG(CASE WHEN category = 2 THEN 1 ELSE 0 END) * 100, 0) as category2_percentage,
            COALESCE(AVG(CASE WHEN category = 3 THEN 1 ELSE 0 END) * 100, 0) as category3_percentage,
            COALESCE(AVG(CASE WHEN rig_amount IS NOT NULL AND rig_amount != '' THEN 1 ELSE 0 END) * 100, 0) as rig_completion_rate,
            COALESCE(AVG(CASE WHEN outcome = 'C' THEN 1 ELSE 0 END) * 100, 0) as complete_outcome_rate,
            COALESCE(AVG(CASE WHEN bite_type = 'B' THEN 1 ELSE 0 END) * 100, 0) as bite_percentage,
            COALESCE(AVG(CASE WHEN washing_of_bite = 'Y' THEN 1 ELSE 0 END) * 100, 0) as washing_rate,
            COALESCE(AVG(age), 0) as average_age,
            COALESCE(COUNT(CASE WHEN sex = 'M' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as male_percentage,
            COALESCE(COUNT(CASE WHEN sex = 'F' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as female_percentage,
            COALESCE(AVG(DATEDIFF(vaccine_day0, bite_date)), 0) as avg_days_to_first_vaccine
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
        
        // Debug log the overall results
        error_log("Overall analytics: " . print_r($analytics['overall'], true));
        
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
            TRIM(UPPER(COALESCE(address, 'UNKNOWN'))) as barangay,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases,
            COUNT(CASE WHEN outcome = 'C' THEN 1 END) as completed_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY TRIM(UPPER(COALESCE(address, 'UNKNOWN')))
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
            TRIM(UPPER(COALESCE(bite_place, 'UNKNOWN'))) as bite_place,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY TRIM(UPPER(COALESCE(bite_place, 'UNKNOWN')))
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
                WHEN age IS NULL THEN 'Unknown'
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
                WHEN age IS NULL THEN 'Unknown'
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
                WHEN age IS NULL THEN 8
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
            TRIM(UPPER(COALESCE(animal_type, 'UNKNOWN'))) as animal_type,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases,
            COUNT(CASE WHEN bite_type = 'B' THEN 1 END) as bite_cases,
            COUNT(CASE WHEN animal_status = 'Dead' THEN 1 END) as dead_animals
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY TRIM(UPPER(COALESCE(animal_type, 'UNKNOWN')))
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
            TRIM(UPPER(COALESCE(bite_site, 'UNKNOWN'))) as bite_site,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage,
            COUNT(CASE WHEN category = 3 THEN 1 END) as high_risk_cases
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY TRIM(UPPER(COALESCE(bite_site, 'UNKNOWN')))
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
            CASE COALESCE(outcome, 'Unknown')
                WHEN 'C' THEN 'Complete'
                WHEN 'Inc' THEN 'Incomplete'
                WHEN 'N' THEN 'Not Started'
                WHEN 'D' THEN 'Died'
                ELSE COALESCE(outcome, 'Unknown')
            END as outcome_label,
            COALESCE(outcome, 'Unknown') as outcome,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY COALESCE(outcome, 'Unknown')
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
            COALESCE(COUNT(CASE WHEN vaccine_day0 IS NOT NULL THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 3' as vaccine_day,
            COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) as missed,
            COALESCE(COUNT(CASE WHEN vaccine_day3 IS NOT NULL THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 7' as vaccine_day,
            COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) as missed,
            COALESCE(COUNT(CASE WHEN vaccine_day7 IS NOT NULL THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 14' as vaccine_day,
            COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) as missed,
            COALESCE(COUNT(CASE WHEN vaccine_day14 IS NOT NULL THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as compliance_rate
        FROM sheet1 WHERE YEAR(date_recorded) = ?
        UNION ALL
        SELECT 
            'Day 28-30' as vaccine_day,
            COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) as given,
            COUNT(*) - COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) as missed,
            COALESCE(COUNT(CASE WHEN vaccine_day2830 IS NOT NULL THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0) as compliance_rate
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
            COALESCE(animal_status, 'Unknown') as animal_status,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sheet1 WHERE YEAR(date_recorded) = ?) as percentage
        FROM sheet1 
        WHERE YEAR(date_recorded) = ?
        GROUP BY COALESCE(animal_status, 'Unknown')
        ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['animal_status'] = array();
        while ($row = $result->fetch_assoc()) {
            $analytics['animal_status'][] = $row;
        }
        
        // 11. Response Time Analysis (Fixed null handling)
        $sql = "SELECT 
            COALESCE(AVG(DATEDIFF(vaccine_day0, bite_date)), 0) as avg_response_days,
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
        $response_time_row = $result->fetch_assoc();
        
        // Ensure all values are not null
        $analytics['response_time'] = array(
            'avg_response_days' => floatval($response_time_row['avg_response_days'] ?? 0),
            'within_24hrs' => intval($response_time_row['within_24hrs'] ?? 0),
            'within_72hrs' => intval($response_time_row['within_72hrs'] ?? 0),
            'beyond_72hrs' => intval($response_time_row['beyond_72hrs'] ?? 0),
            'total_with_vaccine' => intval($response_time_row['total_with_vaccine'] ?? 0)
        );
        
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
    
    // Format the data for display with null checks and proper defaults
    $report = array(
        'summary' => array(
            'total_cases' => number_format(intval($analytics['overall']['total_cases'] ?? 0)),
            'active_months' => intval($analytics['overall']['active_months'] ?? 0),
            'category1_percentage' => number_format(floatval($analytics['overall']['category1_percentage'] ?? 0), 1) . '%',
            'category2_percentage' => number_format(floatval($analytics['overall']['category2_percentage'] ?? 0), 1) . '%',
            'category3_percentage' => number_format(floatval($analytics['overall']['category3_percentage'] ?? 0), 1) . '%',
            'rig_completion_rate' => number_format(floatval($analytics['overall']['rig_completion_rate'] ?? 0), 1) . '%',
            'complete_outcome_rate' => number_format(floatval($analytics['overall']['complete_outcome_rate'] ?? 0), 1) . '%',
            'bite_percentage' => number_format(floatval($analytics['overall']['bite_percentage'] ?? 0), 1) . '%',
            'washing_rate' => number_format(floatval($analytics['overall']['washing_rate'] ?? 0), 1) . '%',
            'average_age' => number_format(floatval($analytics['overall']['average_age'] ?? 0), 1),
            'male_percentage' => number_format(floatval($analytics['overall']['male_percentage'] ?? 0), 1) . '%',
            'female_percentage' => number_format(floatval($analytics['overall']['female_percentage'] ?? 0), 1) . '%',
            'avg_days_to_first_vaccine' => floatval($analytics['overall']['avg_days_to_first_vaccine'] ?? 0) > 0 ? 
                number_format(floatval($analytics['overall']['avg_days_to_first_vaccine']), 1) : 'N/A'
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