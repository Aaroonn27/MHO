<?php
// Include database connection
require_once('db_conn.php');

// Process form submissions
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    delete_patient($_GET['id']);
}

// Handle pagination
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Handle search/filter
$filters = array();
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
    if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
    if (!empty($_GET['name'])) $filters['name'] = $_GET['name'];
    if (!empty($_GET['animal_type'])) $filters['animal_type'] = $_GET['animal_type'];
}

// Fetch patient data
$result = fetch_rabies_patients($filters, $current_page);
$patients = $result['patients'];
$total_pages = $result['total_pages'];
$current_page = $result['current_page'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rabies Exposure Registry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="Style/patient_record.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container main-content">
        <div class="header">
            <h1 class="header-title">Rabies Exposure Registry</h1>
        </div>

        <?php display_messages(); ?>

        <div class="filter-section">
            <form method="GET" class="row g-3">
                <input type="hidden" name="search" value="1">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date:</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date:</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="name" class="form-label">Patient Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="animal_type" class="form-label">Animal Type:</label>
                    <input type="text" class="form-control" id="animal_type" name="animal_type" value="<?php echo isset($_GET['animal_type']) ? htmlspecialchars($_GET['animal_type']) : ''; ?>">
                </div>
                <div class="col-12 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="rabies_form.php" class="btn btn-success">Add New Patient</a>
                </div>
            </form>
        </div>

        <div class="patient-table">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-header">
                    <tr>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Patient Name</th>
                        <th>Address</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Date of Bite</th>
                        <th>Place of Bite</th>
                        <th>Animal Type</th>
                        <th>Bite Type</th>
                        <th>Bite Site</th>
                        <th>Category</th>
                        <th>Washing of Bite</th>
                        <th>RIG Date</th>
                        <th>Vaccine Route</th>
                        <th>Vaccine Day 0</th>
                        <th>Vaccine Day 3</th>
                        <th>Vaccine Day 7</th>
                        <th>Vaccine Day 14</th>
                        <th>Vaccine Day 28-30</th>
                        <th>Vaccine Brand</th>
                        <th>Outcome</th>
                        <th>Animal Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="25" class="text-center">No patients found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $index => $patient): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($patient['date_recorded']); ?></td>
                                <td><?php echo htmlspecialchars($patient['lname'] . ', ' . $patient['fname'] . ' ' . $patient['mname']); ?></td>
                                <td><?php echo htmlspecialchars($patient['address']); ?></td>
                                <td><?php echo htmlspecialchars($patient['age']); ?></td>
                                <td><?php echo htmlspecialchars($patient['sex']); ?></td>
                                <td><?php echo htmlspecialchars($patient['bite_date']); ?></td>
                                <td><?php echo htmlspecialchars($patient['bite_place']); ?></td>
                                <td><?php echo htmlspecialchars($patient['animal_type']); ?></td>
                                <td><?php echo htmlspecialchars($patient['bite_type']); ?></td>
                                <td><?php echo htmlspecialchars($patient['bite_site']); ?></td>
                                <td><?php echo htmlspecialchars($patient['category']); ?></td>
                                <td><?php echo htmlspecialchars($patient['washing_of_bite']); ?></td>
                                <td><?php echo htmlspecialchars($patient['rig_date_given']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_route']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_day0']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_day3']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_day7']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_day14']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_day2830']); ?></td>
                                <td><?php echo htmlspecialchars($patient['vaccine_brand']); ?></td>
                                <td><?php echo htmlspecialchars($patient['outcome']); ?></td>
                                <td><?php echo htmlspecialchars($patient['animal_status']); ?></td>
                                <td><?php echo htmlspecialchars($patient['remarks']); ?></td>
                                <td class="action-buttons">
                                    <a href="rabies_view.php?id=<?php echo $patient['new_id']; ?>" class="btn btn-info">View</a>
                                    <a href="rabies_form.php?id=<?php echo $patient['new_id']; ?>" class="btn btn-warning">Edit</a>
                                    <a href="rabies_registry.php?action=delete&id=<?php echo $patient['new_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container mt-3">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?><?php echo !empty($_GET['name']) ? '&name=' . htmlspecialchars($_GET['name']) : ''; ?><?php echo !empty($_GET['animal_type']) ? '&animal_type=' . htmlspecialchars($_GET['animal_type']) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo; Previous</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="page-item disabled">
                        <span class="page-link">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                    </li>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?><?php echo !empty($_GET['name']) ? '&name=' . htmlspecialchars($_GET['name']) : ''; ?><?php echo !empty($_GET['animal_type']) ? '&animal_type=' . htmlspecialchars($_GET['animal_type']) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">Next &raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>