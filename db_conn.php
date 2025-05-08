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
?>