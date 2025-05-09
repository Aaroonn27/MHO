<?php
// Database connection variables
$servername = "localhost";
$username = "";
$password = "";
$dbname = "mhodb";

// Create connection function
function connect_db() {
    global $servername, $username, $password, $dbname;
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Function to fetch and display rabies exposure patients with pagination
function fetch_rabies_patients($filters = array(), $page = 1, $per_page = 25) {
    $conn = connect_db();
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Base SQL query
    $sql = "SELECT * FROM sheet1";
    
    // Add filters if provided
    $where_conditions = array();
    
    if (!empty($filters)) {
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $date_from = $conn->real_escape_string($filters['date_from']);
            $date_to = $conn->real_escape_string($filters['date_to']);
            $where_conditions[] = "date_recorded BETWEEN '$date_from' AND '$date_to'";
        }
        
        if (!empty($filters['name'])) {
            $name = $conn->real_escape_string($filters['name']);
            $where_conditions[] = "(lname LIKE '%$name%' OR fname LIKE '%$name%' OR mname LIKE '%$name%')";
        }
        
        if (!empty($filters['animal_type'])) {
            $animal_type = $conn->real_escape_string($filters['animal_type']);
            $where_conditions[] = "animal_type LIKE '%$animal_type%'";
        }
    }
    
    // Add where clause if conditions exist
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
    
    // Add order by and limit clause
    $sql .= " ORDER BY date_recorded DESC LIMIT $offset, $per_page";
    
    // Execute query
    $result = $conn->query($sql);
    $patients = array();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
    }
    
    $conn->close();
    
    // Return both patients and pagination info
    return array(
        'patients' => $patients,
        'total_records' => $total_records,
        'total_pages' => ceil($total_records / $per_page),
        'current_page' => $page
    );
}

// Function to get a single patient record
function get_patient($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("SELECT * FROM sheet1 WHERE new_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    } else {
        $patient = null;
    }
    
    $stmt->close();
    $conn->close();
    
    return $patient;
}

// Function to save a new rabies exposure patient
function save_rabies_patient() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = connect_db();
        
        // Generate patient ID based on date if not provided
        $id = !empty($_POST['id']) ? $_POST['id'] : date('YmdHis');
        
        // Basic patient information
        $date_recorded = $conn->real_escape_string($_POST['date_recorded']);
        $lname = $conn->real_escape_string($_POST['lname']);
        $fname = $conn->real_escape_string($_POST['fname']);
        $mname = $conn->real_escape_string($_POST['mname']);
        $address = $conn->real_escape_string($_POST['address']);
        $age = $conn->real_escape_string($_POST['age']);
        $sex = $conn->real_escape_string($_POST['sex']);
        
        // Bite information
        $bite_date = $conn->real_escape_string($_POST['bite_date']);
        $bite_place = $conn->real_escape_string($_POST['bite_place']);
        $animal_type = $conn->real_escape_string($_POST['animal_type']);
        $bite_type = $conn->real_escape_string($_POST['bite_type']);
        $bite_site = $conn->real_escape_string($_POST['bite_site']);
        
        // PEP information
        $category = $conn->real_escape_string($_POST['category']);
        $washing_of_bite = $conn->real_escape_string($_POST['washing_of_bite']);
        $rig_date_given = $conn->real_escape_string($_POST['rig_date_given']);
        $rig_amount = $conn->real_escape_string($_POST['rig_amount']);
        $vaccine_route = $conn->real_escape_string($_POST['vaccine_route']);
        
        // Vaccine information
        $vaccine_generic = $conn->real_escape_string($_POST['vaccine_generic']);
        $vaccine_brand = $conn->real_escape_string($_POST['vaccine_brand']);
        
        // Vaccine dates
        $vaccine_day0 = !empty($_POST['vaccine_day0']) ? $conn->real_escape_string($_POST['vaccine_day0']) : null;
        $vaccine_day3 = !empty($_POST['vaccine_day3']) ? $conn->real_escape_string($_POST['vaccine_day3']) : null;
        $vaccine_day7 = !empty($_POST['vaccine_day7']) ? $conn->real_escape_string($_POST['vaccine_day7']) : null;
        $vaccine_day14 = !empty($_POST['vaccine_day14']) ? $conn->real_escape_string($_POST['vaccine_day14']) : null;
        $vaccine_day2830 = !empty($_POST['vaccine_day2830']) ? $conn->real_escape_string($_POST['vaccine_day2830']) : null;
        
        // Additional information
        $abc_name = $conn->real_escape_string($_POST['abc_name']);
        $outcome = $conn->real_escape_string($_POST['outcome']);
        $animal_status = $conn->real_escape_string($_POST['animal_status']);
        $remarks = $conn->real_escape_string($_POST['remarks']);
        
        // Prepare SQL statement
        $sql = "INSERT INTO sheet1 (id, date_recorded, lname, fname, mname, address, age, sex, 
                bite_date, bite_place, animal_type, bite_type, bite_site, category, washing_of_bite, 
                rig_date_given, rig_amount, vaccine_route, vaccine_generic, vaccine_brand, vaccine_day0, vaccine_day3, vaccine_day7, vaccine_day14, 
                vaccine_day2830, abc_name, outcome, animal_status, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssisssssssssssssss", 
            $id, $date_recorded, $lname, $fname, $mname, $address, $age, $sex, 
            $bite_date, $bite_place, $animal_type, $bite_type, $bite_site, $category, 
            $washing_of_bite, $rig_date_given, $rig_amount, $vaccine_route, $vaccine_generic, $vaccine_brand, $vaccine_day0, $vaccine_day3, 
            $vaccine_day7, $vaccine_day14, $vaccine_day2830, $abc_name, $outcome, $animal_status, $remarks);
        
        if ($stmt->execute()) {
            header("Location: rabies_registry.php?success=1");
            exit();
        } else {
            header("Location: rabies_registry.php?error=1&msg=" . urlencode($stmt->error));
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Function to update an existing rabies exposure patient
function update_rabies_patient() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_id'])) {
        $conn = connect_db();
        
        $new_id = $conn->real_escape_string($_POST['new_id']);
        
        // Basic patient information
        $id = $conn->real_escape_string($_POST['id']);
        $date_recorded = $conn->real_escape_string($_POST['date_recorded']);
        $lname = $conn->real_escape_string($_POST['lname']);
        $fname = $conn->real_escape_string($_POST['fname']);
        $mname = $conn->real_escape_string($_POST['mname']);
        $address = $conn->real_escape_string($_POST['address']);
        $age = $conn->real_escape_string($_POST['age']);
        $sex = $conn->real_escape_string($_POST['sex']);
        
        // Bite information
        $bite_date = $conn->real_escape_string($_POST['bite_date']);
        $bite_place = $conn->real_escape_string($_POST['bite_place']);
        $animal_type = $conn->real_escape_string($_POST['animal_type']);
        $bite_type = $conn->real_escape_string($_POST['bite_type']);
        $bite_site = $conn->real_escape_string($_POST['bite_site']);
        
        // PEP information
        $category = $conn->real_escape_string($_POST['category']);
        $washing_of_bite = $conn->real_escape_string($_POST['washing_of_bite']);
        $rig_date_given = $conn->real_escape_string($_POST['rig_date_given']);
        $rig_amount = $conn->real_escape_string($_POST['rig_amount']);
        $vaccine_route = $conn->real_escape_string($_POST['vaccine_route']);
        
        // Vaccine information
        $vaccine_generic = $conn->real_escape_string($_POST['vaccine_generic']);
        $vaccine_brand = $conn->real_escape_string($_POST['vaccine_brand']);
        
        // Vaccine dates
        $vaccine_day0 = !empty($_POST['vaccine_day0']) ? $conn->real_escape_string($_POST['vaccine_day0']) : null;
        $vaccine_day3 = !empty($_POST['vaccine_day3']) ? $conn->real_escape_string($_POST['vaccine_day3']) : null;
        $vaccine_day7 = !empty($_POST['vaccine_day7']) ? $conn->real_escape_string($_POST['vaccine_day7']) : null;
        $vaccine_day14 = !empty($_POST['vaccine_day14']) ? $conn->real_escape_string($_POST['vaccine_day14']) : null;
        $vaccine_day2830 = !empty($_POST['vaccine_day2830']) ? $conn->real_escape_string($_POST['vaccine_day2830']) : null;
        
        // Additional information
        $abc_name = $conn->real_escape_string($_POST['abc_name']);
        $outcome = $conn->real_escape_string($_POST['outcome']);
        $animal_status = $conn->real_escape_string($_POST['animal_status']);
        $remarks = $conn->real_escape_string($_POST['remarks']);
        
        // Prepare SQL statement
        $sql = "UPDATE sheet1 SET id = ?, date_recorded = ?, lname = ?, fname = ?, mname = ?, 
                address = ?, age = ?, sex = ?, bite_date = ?, bite_place = ?, animal_type = ?, 
                bite_type = ?, bite_site = ?, category = ?, washing_of_bite = ?, rig_date_given = ?, 
                rig_amount = ?, vaccine_route = ?, vaccine_generic = ?, vaccine_brand = ?, vaccine_day0 = ?, vaccine_day3 = ?, vaccine_day7 = ?, 
                vaccine_day14 = ?, vaccine_day2830 = ?, abc_name = ?, outcome = ?, 
                animal_status = ?, remarks = ? WHERE new_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssissssssissssssssssssi", 
            $id, $date_recorded, $lname, $fname, $mname, $address, $age, $sex, 
            $bite_date, $bite_place, $animal_type, $bite_type, $bite_site, $category, 
            $washing_of_bite, $rig_date_given, $rig_amount, $vaccine_route, $vaccine_generic, $vaccine_brand, $vaccine_day0, $vaccine_day3, 
            $vaccine_day7, $vaccine_day14, $vaccine_day2830, $abc_name, $outcome, $animal_status, $remarks, $new_id);
        
        if ($stmt->execute()) {
            header("Location: rabies_registry.php?updated=1");
            exit();
        } else {
            header("Location: rabies_registry.php?error=2&msg=" . urlencode($stmt->error));
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Function to delete a patient record
function delete_patient($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("DELETE FROM sheet1 WHERE new_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: rabies_registry.php?deleted=1");
        exit();
    } else {
        header("Location: rabies_registry.php?error=3&msg=" . urlencode($stmt->error));
        exit();
    }
    
    $stmt->close();
    $conn->close();
}

// Display success/error messages
function display_messages() {
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Patient record successfully created!</div>';
    }
    
    if (isset($_GET['updated'])) {
        echo '<div class="alert alert-success">Patient record successfully updated!</div>';
    }
    
    if (isset($_GET['deleted'])) {
        echo '<div class="alert alert-success">Patient record successfully deleted!</div>';
    }
    
    if (isset($_GET['error'])) {
        $error_msg = isset($_GET['msg']) ? $_GET['msg'] : 'Unknown error';
        
        switch ($_GET['error']) {
            case 1:
                echo '<div class="alert alert-danger">Error creating patient record: ' . htmlspecialchars($error_msg) . '</div>';
                break;
            case 2:
                echo '<div class="alert alert-danger">Error updating patient record: ' . htmlspecialchars($error_msg) . '</div>';
                break;
            case 3:
                echo '<div class="alert alert-danger">Error deleting patient record: ' . htmlspecialchars($error_msg) . '</div>';
                break;
            default:
                echo '<div class="alert alert-danger">An unknown error occurred: ' . htmlspecialchars($error_msg) . '</div>';
        }
    }
}

// Function to get summary statistics
function get_rabies_stats() {
    $conn = connect_db();
    
    $stats = array();
    
    // Total patients
    $sql = "SELECT COUNT(*) as total FROM sheet1";
    $result = $conn->query($sql);
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Patients by bite type
    $sql = "SELECT bite_type, COUNT(*) as count FROM sheet1 GROUP BY bite_type";
    $result = $conn->query($sql);
    $stats['bite_type'] = array();
    while ($row = $result->fetch_assoc()) {
        $stats['bite_type'][$row['bite_type']] = $row['count'];
    }
    
    // Patients by animal type
    $sql = "SELECT animal_type, COUNT(*) as count FROM sheet1 GROUP BY animal_type ORDER BY count DESC LIMIT 5";
    $result = $conn->query($sql);
    $stats['animal_type'] = array();
    while ($row = $result->fetch_assoc()) {
        $stats['animal_type'][$row['animal_type']] = $row['count'];
    }
    
    // Patients by outcome
    $sql = "SELECT outcome, COUNT(*) as count FROM sheet1 GROUP BY outcome";
    $result = $conn->query($sql);
    $stats['outcome'] = array();
    while ($row = $result->fetch_assoc()) {
        $stats['outcome'][$row['outcome']] = $row['count'];
    }
    
    $conn->close();
    return $stats;
}

// For appointments

// Function to fetch and display appointments
function fetch_appointments() {
    $conn = connect_db();
    
    // Get sort parameter
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
    
    // Determine sort order
    switch ($sort) {
        case 'date_asc':
            $order_by = "appointment_date ASC";
            break;
        case 'date_desc':
            $order_by = "appointment_date DESC";
            break;
        case 'name_asc':
            $order_by = "name ASC";
            break;
        case 'name_desc':
            $order_by = "name DESC";
            break;
        default:
            $order_by = "appointment_date DESC";
    }
    
    // SQL query to fetch appointments
    $sql = "SELECT id, program, name, address, contact, appointment_date FROM appointments ORDER BY $order_by";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            echo '<div class="table-row">';
            echo '<div>' . htmlspecialchars($row["name"]) . '</div>';
            echo '<div>' . htmlspecialchars($row["appointment_date"]) . '</div>';
            echo '<div>' . htmlspecialchars($row["contact"]) . '</div>';
            echo '<div>' . htmlspecialchars($row["program"]) . '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-appointments">No appointments found</div>';
    }
    
    $conn->close();
}

// Function to save a new appointment
function save_appointment() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = connect_db();
        
        // Get form data and sanitize inputs
        $program = $conn->real_escape_string($_POST['program']);
        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO appointments (program, name, address, contact, appointment_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $program, $name, $address, $contact, $appointment_date);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back to appointment list with success message
            header("Location: appointment.php?success=1");
            exit();
        } else {
            // Redirect back to appointment list with error message
            header("Location: appointment.php?error=1");
            exit();
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Function to get a single appointment by ID
function get_appointment($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("SELECT id, program, name, address, contact, appointment_date FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
    } else {
        $appointment = null;
    }
    
    $stmt->close();
    $conn->close();
    
    return $appointment;
}

// Function to update an existing appointment
function update_appointment() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $conn = connect_db();
        
        $id = $conn->real_escape_string($_POST['id']);
        $program = $conn->real_escape_string($_POST['program']);
        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
        
        $stmt = $conn->prepare("UPDATE appointments SET program = ?, name = ?, address = ?, contact = ?, appointment_date = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $program, $name, $address, $contact, $appointment_date, $id);
        
        if ($stmt->execute()) {
            header("Location: appointment.php?updated=1");
            exit();
        } else {
            header("Location: appointment.php?error=2");
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Function to delete an appointment
function delete_appointment($id) {
    $conn = connect_db();
    
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: appointment.php?deleted=1");
        exit();
    } else {
        header("Location: appointment.php?error=3");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}

// Fixed functions for quarterly report
// Add these updated functions to your db_conn.php file

// Function to get quarterly report data
function get_quarterly_report_data($year) {
    $conn = connect_db();
    $months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    $categories = [1, 2, 3];
    $report_data = [];
    
    // Initialize data structure for all months and categories
    foreach ($months as $month) {
        foreach ($categories as $cat) {
            $report_data[$month][$cat] = [
                'registered_exposures' => 0,
                'patients_received_rig' => 0,
                'outcome_complete' => 0,
                'outcome_incomplete' => 0,
                'outcome_none' => 0,
                'outcome_died' => 0
            ];
        }
    }
    
    // SQL query with debugging - Make sure we handle null values properly
    $sql = "SELECT 
                MONTH(date_recorded) as month,
                category,
                COUNT(*) as registered_exposures,
                SUM(CASE WHEN rig_amount IS NOT NULL AND rig_amount != '' THEN 1 ELSE 0 END) as patients_received_rig,
                SUM(CASE WHEN outcome = 'C' THEN 1 ELSE 0 END) as outcome_complete,
                SUM(CASE WHEN outcome = 'Inc' THEN 1 ELSE 0 END) as outcome_incomplete,
                SUM(CASE WHEN outcome = 'N' THEN 1 ELSE 0 END) as outcome_none,
                SUM(CASE WHEN outcome = 'D' THEN 1 ELSE 0 END) as outcome_died
            FROM sheet1
            WHERE YEAR(date_recorded) = ? AND category IN (1, 2, 3)
            GROUP BY MONTH(date_recorded), category";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        // Log error for debugging
        error_log("SQL Error in get_quarterly_report_data: " . $conn->error);
        $stmt->close();
        $conn->close();
        return $report_data; // Return empty initialized data
    }
    
    // Process results
    while ($row = $result->fetch_assoc()) {
        $month_num = $row['month'];
        if (!$month_num || $month_num < 1 || $month_num > 12) continue;
        
        $month_name = $months[$month_num - 1];
        $cat = (int)$row['category'];
        
        if (!in_array($cat, $categories)) continue;
        
        $report_data[$month_name][$cat]['registered_exposures'] = (int)$row['registered_exposures'];
        $report_data[$month_name][$cat]['patients_received_rig'] = (int)$row['patients_received_rig'];
        $report_data[$month_name][$cat]['outcome_complete'] = (int)$row['outcome_complete'];
        $report_data[$month_name][$cat]['outcome_incomplete'] = (int)$row['outcome_incomplete'];
        $report_data[$month_name][$cat]['outcome_none'] = (int)$row['outcome_none'];
        $report_data[$month_name][$cat]['outcome_died'] = (int)$row['outcome_died'];
    }
    
    $stmt->close();
    $conn->close();
    return $report_data;
}

// Function to calculate quarterly totals for all categories
function calculate_quarterly_totals($report_data) {
    $quarters = [
        'FIRST QUARTER' => ['January', 'February', 'March'],
        'SECOND QUARTER' => ['April', 'May', 'June'],
        'THIRD QUARTER' => ['July', 'August', 'September'],
        'FOURTH QUARTER' => ['October', 'November', 'December']
    ];
    $categories = [1, 2, 3];
    $quarterly_totals = [];
    
    foreach ($quarters as $quarter_name => $quarter_months) {
        foreach ($categories as $cat) {
            $quarterly_totals[$quarter_name][$cat] = [
                'registered_exposures' => 0,
                'patients_received_rig' => 0,
                'outcome_complete' => 0,
                'outcome_incomplete' => 0,
                'outcome_none' => 0,
                'outcome_died' => 0
            ];
            
            foreach ($quarter_months as $month) {
                if (isset($report_data[$month][$cat])) {
                    foreach ($report_data[$month][$cat] as $key => $value) {
                        $quarterly_totals[$quarter_name][$cat][$key] += $value;
                    }
                }
            }
        }
    }
    
    return $quarterly_totals;
}

// Function to generate the CSV export for all categories
function generate_csv($report_data, $quarterly_totals, $year) {
    $filename = "Quarterly_Report_$year.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    
    // Use numeric key values for categories
    $categories = [1 => 'Category 1', 2 => 'Category 2', 3 => 'Category 3'];
    
    $header = ['Quarter & Year'];
    foreach ($categories as $cat_num => $cat_label) {
        $header = array_merge($header, [
            $cat_label . ' - No. of Registered Exposures',
            $cat_label . ' - No. of patients who received RIG',
            $cat_label . ' - Outcome Complete',
            $cat_label . ' - Outcome Incomplete',
            $cat_label . ' - Outcome None',
            $cat_label . ' - Outcome Died'
        ]);
    }
    
    fputcsv($output, $header);
    
    // Add monthly data
    foreach ($report_data as $month => $cat_data) {
        $row = ["$month $year"];
        
        foreach ([1, 2, 3] as $cat) {
            $row[] = $cat_data[$cat]['registered_exposures'];
            $row[] = $cat_data[$cat]['patients_received_rig'];
            $row[] = $cat_data[$cat]['outcome_complete'];
            $row[] = $cat_data[$cat]['outcome_incomplete'];
            $row[] = $cat_data[$cat]['outcome_none'];
            $row[] = $cat_data[$cat]['outcome_died'];
        }
        
        fputcsv($output, $row);
    }
    
    // Add quarterly totals
    foreach ($quarterly_totals as $quarter => $cat_data) {
        $row = ["$quarter $year"];
        
        foreach ([1, 2, 3] as $cat) {
            $row[] = $cat_data[$cat]['registered_exposures'];
            $row[] = $cat_data[$cat]['patients_received_rig'];
            $row[] = $cat_data[$cat]['outcome_complete'];
            $row[] = $cat_data[$cat]['outcome_incomplete'];
            $row[] = $cat_data[$cat]['outcome_none'];
            $row[] = $cat_data[$cat]['outcome_died'];
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Function to get available years for the dropdown
function get_available_years() {
    $conn = connect_db();
    
    $sql = "SELECT DISTINCT YEAR(date_recorded) as year FROM sheet1 ORDER BY year DESC";
    $result = $conn->query($sql);
    
    $years = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }
    }
    
    // If no years found, add current year
    if (empty($years)) {
        $years[] = date('Y');
    }
    
    $conn->close();
    return $years;
}
?>