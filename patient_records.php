<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee'];
check_page_access($required_roles);

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
    <title>Rabies Exposure Registry - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>

<body>
    <!-- Header -->
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

    <main>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-user-md"></i> Rabies Exposure Registry</h1>
                <p>Comprehensive patient records and bite incident tracking system</p>
            </div>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <i class="fas fa-<?php echo $_SESSION['message_type'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-container">
            <div class="filter-header">
                <h3><i class="fas fa-filter"></i> Search & Filter Records</h3>
                <div class="filter-actions">
                    <a href="quarterly_report.php" class="filter-btn info">
                        <i class="fas fa-chart-line"></i> Quarterly Report
                    </a>
                    <a href="rabies_form.php" class="filter-btn success">
                        <i class="fas fa-plus"></i> Add New Patient
                    </a>
                </div>
            </div>
            <form method="GET" class="filter-form">
                <input type="hidden" name="search" value="1">
                <div class="filter-group">
                    <label for="date_from"><i class="fas fa-calendar-alt"></i> From Date:</label>
                    <input type="date" class="filter-input" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                </div>
                <div class="filter-group">
                    <label for="date_to"><i class="fas fa-calendar-alt"></i> To Date:</label>
                    <input type="date" class="filter-input" id="date_to" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                </div>
                <div class="filter-group">
                    <label for="name"><i class="fas fa-user"></i> Patient Name:</label>
                    <input type="text" class="filter-input" id="name" name="name" placeholder="Enter patient name..." value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                </div>
                <div class="filter-group">
                    <label for="animal_type"><i class="fas fa-paw"></i> Animal Type:</label>
                    <input type="text" class="filter-input" id="animal_type" name="animal_type" placeholder="e.g., Dog, Cat..." value="<?php echo isset($_GET['animal_type']) ? htmlspecialchars($_GET['animal_type']) : ''; ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="filter-btn primary">
                        <i class="fas fa-search"></i> Search Records
                    </button>
                </div>
            </form>
        </div>

        <!-- Main Content -->
        <div class="content-container">
            <div class="content-header">
                <h2>Patient Records (<?php echo $total_pages > 0 ? (($current_page - 1) * 20 + 1) . '-' . min($current_page * 20, count($patients)) : '0'; ?> of <?php echo count($patients); ?>)</h2>
            </div>

            <div class="table-container">
                <table class="patient-table">
                    <thead class="table-header">
                        <tr>
                            <th><i class="fas fa-hashtag"></i> No.</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-user"></i> Patient Name</th>
                            <th><i class="fas fa-map-marker-alt"></i> Address</th>
                            <th><i class="fas fa-birthday-cake"></i> Age</th>
                            <th><i class="fas fa-venus-mars"></i> Sex</th>
                            <th><i class="fas fa-calendar-times"></i> Date of Bite</th>
                            <th><i class="fas fa-location-arrow"></i> Place of Bite</th>
                            <th><i class="fas fa-paw"></i> Animal Type</th>
                            <th><i class="fas fa-teeth"></i> Bite Type</th>
                            <th><i class="fas fa-crosshairs"></i> Bite Site</th>
                            <th><i class="fas fa-layer-group"></i> Category</th>
                            <th><i class="fas fa-hand-paper"></i> Washing of Bite</th>
                            <th><i class="fas fa-syringe"></i> RIG Date</th>
                            <th><i class="fas fa-route"></i> Vaccine Route</th>
                            <th><i class="fas fa-calendar-day"></i> Day 0</th>
                            <th><i class="fas fa-calendar-day"></i> Day 3</th>
                            <th><i class="fas fa-calendar-day"></i> Day 7</th>
                            <th><i class="fas fa-calendar-day"></i> Day 14</th>
                            <th><i class="fas fa-calendar-day"></i> Day 28-30</th>
                            <th><i class="fas fa-prescription-bottle"></i> Vaccine Brand</th>
                            <th><i class="fas fa-heartbeat"></i> Outcome</th>
                            <th><i class="fas fa-info-circle"></i> Animal Status</th>
                            <th><i class="fas fa-comment"></i> Remarks</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="25">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <h3>No Records Found</h3>
                                        <p>No patient records match your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $index => $patient): ?>
                                <tr>
                                    <td><?php echo (($current_page - 1) * 20) + $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($patient['date_recorded']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($patient['lname'] . ', ' . $patient['fname'] . ' ' . $patient['mname']); ?></strong></td>
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
                                        <!-- <a href="rabies_view.php?id=<?php echo $patient['new_id']; ?>" class="action-btn info" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a> -->
                                        <a href="rabies_form.php?id=<?php echo $patient['new_id']; ?>" class="action-btn warning" title="Edit Record">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="rabies_registry.php?action=delete&id=<?php echo $patient['new_id']; ?>" class="action-btn danger" title="Delete Record" onclick="return confirm('Are you sure you want to delete this record? This action cannot be undone.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?><?php echo !empty($_GET['name']) ? '&name=' . htmlspecialchars($_GET['name']) : ''; ?><?php echo !empty($_GET['animal_type']) ? '&animal_type=' . htmlspecialchars($_GET['animal_type']) : ''; ?>" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-file-alt"></i> Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                            </span>
                        </li>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . htmlspecialchars($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . htmlspecialchars($_GET['date_to']) : ''; ?><?php echo !empty($_GET['name']) ? '&name=' . htmlspecialchars($_GET['name']) : ''; ?><?php echo !empty($_GET['animal_type']) ? '&animal_type=' . htmlspecialchars($_GET['animal_type']) : ''; ?>" aria-label="Next">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </main>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });

            // Add smooth scrolling to form submission
            const form = document.querySelector('.filter-form');
            if (form) {
                form.addEventListener('submit', function() {
                    // Add loading state to search button
                    const searchBtn = form.querySelector('.filter-btn.primary');
                    const originalText = searchBtn.innerHTML;
                    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                    searchBtn.disabled = true;

                    // Re-enable after a short delay (form will submit)
                    setTimeout(() => {
                        searchBtn.innerHTML = originalText;
                        searchBtn.disabled = false;
                    }, 2000);
                });
            }

            // Add confirmation for delete actions
            const deleteLinks = document.querySelectorAll('.action-btn.danger');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Create custom confirmation modal
                    const confirmation = confirm(
                        '⚠️ DELETE CONFIRMATION\n\n' +
                        'Are you sure you want to delete this patient record?\n\n' +
                        'This action will permanently remove:\n' +
                        '• Patient information\n' +
                        '• Bite incident details\n' +
                        '• Vaccination records\n' +
                        '• All related data\n\n' +
                        'This action CANNOT be undone!'
                    );

                    if (confirmation) {
                        // Add loading state
                        link.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                        link.style.pointerEvents = 'none';

                        // Proceed with deletion
                        window.location.href = link.href;
                    }
                });
            });

            // Add tooltips for action buttons
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });

                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Enhance table row interactions
            const tableRows = document.querySelectorAll('.patient-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.1)';
                });

                row.addEventListener('mouseleave', function() {
                    this.style.boxShadow = 'none';
                });
            });

            // Add keyboard navigation for accessibility
            document.addEventListener('keydown', function(e) {
                // Ctrl+F to focus search
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('name').focus();
                }

                // Escape key to clear filters
                if (e.key === 'Escape') {
                    const currentUrl = new URL(window.location);
                    currentUrl.search = '';
                    window.location.href = currentUrl.toString();
                }
            });

            // Add loading animation for page transitions
            const navLinks = document.querySelectorAll('nav a, .filter-btn');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.href && !this.href.includes('#') && !this.onclick) {
                        // Add loading overlay
                        const overlay = document.createElement('div');
                        overlay.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(102, 126, 234, 0.1);
                            backdrop-filter: blur(2px);
                            z-index: 9999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            color: #667eea;
                            font-weight: 600;
                        `;
                        overlay.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i>Loading...';
                        document.body.appendChild(overlay);

                        // Remove overlay if page doesn't change (for same-page actions)
                        setTimeout(() => {
                            if (document.body.contains(overlay)) {
                                overlay.remove();
                            }
                        }, 3000);
                    }
                });
            });
        });

        // Add print functionality
        function printTable() {
            const printWindow = window.open('', '_blank');
            const tableHTML = document.querySelector('.table-container').outerHTML;

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Rabies Exposure Registry - Print</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .action-buttons { display: none; }
                        @media print { .action-buttons { display: none !important; } }
                    </style>
                </head>
                <body>
                    <h1>City Health Office of San Pablo</h1>
                    <h2>Rabies Exposure Registry</h2>
                    <p>Generated on: ${new Date().toLocaleString()}</p>
                    ${tableHTML}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.print();
        }

        // Export to CSV functionality
        function exportToCSV() {
            const table = document.querySelector('.patient-table');
            const rows = Array.from(table.querySelectorAll('tr'));

            const csvContent = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('th, td'));
                return cells.slice(0, -1).map(cell => {
                    // Clean cell content and escape quotes
                    const content = cell.textContent.trim().replace(/"/g, '""');
                    return `"${content}"`;
                }).join(',');
            }).join('\n');

            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `rabies_registry_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>

</html>