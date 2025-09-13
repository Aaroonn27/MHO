<?php
session_start();
require_once 'auth.php';

$required_roles = ['admin', 'abtc_employee']; 
check_page_access($required_roles);

require_once('db_conn.php');

$patient = array(
    'new_id' => '',
    'id' => '',
    'date_recorded' => date('Y-m-d'),
    'lname' => '',
    'fname' => '',
    'mname' => '',
    'address' => '',
    'age' => '',
    'sex' => '',
    'bite_date' => '',
    'bite_place' => '',
    'animal_type' => '',
    'bite_type' => '',
    'bite_site' => '',
    'category' => '',
    'washing_of_bite' => '',
    'rig_date_given' => '',
    'rig_amount' => '',
    'vaccine_generic' => '',
    'vaccine_brand' => '',
    'vaccine_route' => '',
    'vaccine_day0' => '',
    'vaccine_day3' => '',
    'vaccine_day7' => '',
    'vaccine_day14' => '',
    'vaccine_day2830' => '',
    'abc_name' => '',
    'outcome' => '',
    'animal_status' => '',
    'remarks' => ''
);

$is_edit = false;
$page_title = "Add New Patient";

// Check if we're editing an existing record
if (isset($_GET['id'])) {
    $patient_id = $_GET['id'];
    $patient_data = get_patient($patient_id);
    
    if ($patient_data) {
        $patient = array_merge($patient, $patient_data);
        $is_edit = true;
        $page_title = "Edit Patient Record";
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($is_edit) {
        update_rabies_patient();
    } else {
        save_rabies_patient();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Rabies Exposure Registry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="Style/patient_record.css">
    <style>
        .form-container {
            background-color: #f0f8ff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-section {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background-color: #fff;
        }
        
        .form-section-title {
            font-weight: bold;
            color: #3366cc;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .btn-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .exposure-category label {
            display: inline-block;
            margin-right: 15px;
        }
        
        .washing-options label {
            display: inline-block;
            margin-right: 15px;
        }
        
        .pep-section {
            background-color: #e6f2ff;
        }
        
        .vaccination-schedule {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container main-content">
        <div class="header">
            <h1 class="header-title"><?php echo $page_title; ?></h1>
        </div>

        <form method="POST" class="form-container">
            <?php if ($is_edit): ?>
                <input type="hidden" name="new_id" value="<?php echo htmlspecialchars($patient['new_id']); ?>">
            <?php endif; ?>

            <!-- Patient Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">Patient Information</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="fname" class="form-label">First Name:</label>
                        <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($patient['fname']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="mname" class="form-label">Middle Name:</label>
                        <input type="text" class="form-control" id="mname" name="mname" value="<?php echo htmlspecialchars($patient['mname']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="lname" class="form-label">Last Name:</label>
                        <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($patient['lname']); ?>" required>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="address" class="form-label">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($patient['address']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="age" class="form-label">Age:</label>
                        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($patient['age']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="sex" class="form-label">Sex:</label>
                        <select class="form-select" id="sex" name="sex" required>
                            <option value="" disabled <?php echo empty($patient['sex']) ? 'selected' : ''; ?>>Select</option>
                            <option value="Male" <?php echo $patient['sex'] == 'M' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $patient['sex'] == 'F' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label for="date_recorded" class="form-label">Date Recorded:</label>
                        <input type="date" class="form-control" id="date_recorded" name="date_recorded" value="<?php echo htmlspecialchars($patient['date_recorded']); ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label for="id" class="form-label">Patient ID (generated if left blank):</label>
                        <input type="text" class="form-control" id="id" name="id" value="<?php echo htmlspecialchars($patient['id']); ?>">
                    </div>
                </div>
            </div>

            <!-- Exposure Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">History of Exposure</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="bite_date" class="form-label">Date of Exposure:</label>
                        <input type="date" class="form-control" id="bite_date" name="bite_date" value="<?php echo htmlspecialchars($patient['bite_date']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="bite_type" class="form-label">Type of Exposure:</label>
                        <select class="form-select" id="bite_type" name="bite_type" required>
                            <option value="" disabled <?php echo empty($patient['bite_type']) ? 'selected' : ''; ?>>Select</option>
                            <option value="B" <?php echo $patient['bite_type'] == 'B' ? 'selected' : ''; ?>>Bite</option>
                            <option value="NB" <?php echo $patient['bite_type'] == 'NB' ? 'selected' : ''; ?>>Non-Bite</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="bite_place" class="form-label">Place of Exposure:</label>
                        <input type="text" class="form-control" id="bite_place" name="bite_place" value="<?php echo htmlspecialchars($patient['bite_place']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="animal_type" class="form-label">Source of Exposure:</label>
                        <select class="form-select" id="animal_type" name="animal_type" required>
                            <option value="" disabled <?php echo empty($patient['animal_type']) ? 'selected' : ''; ?>>Select</option>
                            <option value="Dog" <?php echo $patient['animal_type'] == 'Dog' ? 'selected' : ''; ?>>Dog</option>
                            <option value="Cat" <?php echo $patient['animal_type'] == 'Cat' ? 'selected' : ''; ?>>Cat</option>
                            <option value="Bat" <?php echo $patient['animal_type'] == 'Bat' ? 'selected' : ''; ?>>Bat</option>
                            <option value="Monkey" <?php echo $patient['animal_type'] == 'Monkey' ? 'selected' : ''; ?>>Monkey</option>
                            <option value="Rat" <?php echo $patient['animal_type'] == 'Rat' ? 'selected' : ''; ?>>Rat</option>
                            <option value="Other" <?php echo $patient['animal_type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <label for="bite_site" class="form-label">Site of Exposure:</label>
                        <input type="text" class="form-control" id="bite_site" name="bite_site" value="<?php echo htmlspecialchars($patient['bite_site']); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Category and Washing Section -->
            <div class="form-section">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h4 class="form-section-title">Category of Exposure</h4>
                        <div class="exposure-category">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="category" id="category1" value="I" <?php echo $patient['category'] == 'I' ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="category1">I</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="category" id="category2" value="II" <?php echo $patient['category'] == 'II' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="category2">II</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="category" id="category3" value="III" <?php echo $patient['category'] == 'III' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="category3">III</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="form-section-title">Washing of Bite Wound</h4>
                        <div class="washing-options">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="washing_of_bite" id="washingYes" value="YES" <?php echo $patient['washing_of_bite'] == 'YES' ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="washingYes">YES</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="washing_of_bite" id="washingNo" value="NO" <?php echo $patient['washing_of_bite'] == 'NO' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="washingNo">NO</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Exposure Prophylaxis Section -->
            <div class="form-section pep-section">
                <h3 class="form-section-title">Post Exposure Prophylaxis</h3>
                
                <!-- RIG Information -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="rig_date_given" class="form-label">Date Given:</label>
                        <input type="date" class="form-control" id="rig_date_given" name="rig_date_given" value="<?php echo htmlspecialchars($patient['rig_date_given']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="rig_amount" class="form-label">RIG:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="rig_amount" name="rig_amount" value="<?php echo htmlspecialchars($patient['rig_amount']); ?>">
                            <span class="input-group-text">/ml</span>
                        </div>
                    </div>
                </div>
                
                <!-- Vaccine Information -->
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label for="vaccine_generic" class="form-label">Generic Name:</label>
                        <input type="text" class="form-control" id="vaccine_generic" name="vaccine_generic" value="<?php echo htmlspecialchars($patient['vaccine_generic']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="vaccine_brand" class="form-label">Brand Name:</label>
                        <input type="text" class="form-control" id="vaccine_brand" name="vaccine_brand" value="<?php echo htmlspecialchars($patient['vaccine_brand']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="vaccine_route" class="form-label">Route of Administration:</label>
                        <select class="form-select" id="vaccine_route" name="vaccine_route">
                            <option value="" <?php echo empty($patient['vaccine_route']) ? 'selected' : ''; ?>>Select</option>
                            <option value="IM" <?php echo $patient['vaccine_route'] == 'IM' ? 'selected' : ''; ?>>Intramuscular (IM)</option>
                            <option value="ID" <?php echo $patient['vaccine_route'] == 'ID' ? 'selected' : ''; ?>>Intradermal (ID)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Vaccination Schedule -->
                <h4 class="mt-4">Anti-Rabies Vaccine Schedule</h4>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="vaccine_day0" class="form-label">Day 0:</label>
                        <input type="date" class="form-control" id="vaccine_day0" name="vaccine_day0" value="<?php echo htmlspecialchars($patient['vaccine_day0']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="vaccine_day3" class="form-label">Day 3:</label>
                        <input type="date" class="form-control" id="vaccine_day3" name="vaccine_day3" value="<?php echo htmlspecialchars($patient['vaccine_day3']); ?>">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="vaccine_day7" class="form-label">Day 7:</label>
                        <input type="date" class="form-control" id="vaccine_day7" name="vaccine_day7" value="<?php echo htmlspecialchars($patient['vaccine_day7']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="vaccine_day14" class="form-label">Day 14:</label>
                        <input type="date" class="form-control" id="vaccine_day14" name="vaccine_day14" value="<?php echo htmlspecialchars($patient['vaccine_day14']); ?>">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="vaccine_day2830" class="form-label">Day 28/30:</label>
                        <input type="date" class="form-control" id="vaccine_day2830" name="vaccine_day2830" value="<?php echo htmlspecialchars($patient['vaccine_day2830']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="abc_name" class="form-label">Name of ABC:</label>
                        <input type="text" class="form-control" id="abc_name" name="abc_name" placeholder="Private Animal Bite Center" value="<?php echo htmlspecialchars($patient['abc_name']); ?>">
                    </div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">Additional Information</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="animal_status" class="form-label">Animal Status:</label>
                        <select class="form-select" id="animal_status" name="animal_status">
                            <option value="" <?php echo empty($patient['animal_status']) ? 'selected' : ''; ?>>Select</option>
                            <option value="Alive" <?php echo $patient['animal_status'] == 'Alive' ? 'selected' : ''; ?>>Alive</option>
                            <option value="Dead" <?php echo $patient['animal_status'] == 'Dead' ? 'selected' : ''; ?>>Dead</option>
                            <option value="Lost" <?php echo $patient['animal_status'] == 'Lost' ? 'selected' : ''; ?>>Lost</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="outcome" class="form-label">Patient Outcome:</label>
                        <select class="form-select" id="outcome" name="outcome">
                            <option value="" <?php echo empty($patient['outcome']) ? 'selected' : ''; ?>>Select</option>
                            <option value="C" <?php echo $patient['outcome'] == 'C' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Inc" <?php echo $patient['outcome'] == 'Inc' ? 'selected' : ''; ?>>Incomplete</option>
                            <option value="N" <?php echo $patient['outcome'] == 'N' ? 'selected' : ''; ?>>Not Started</option>
                            <option value="D" <?php echo $patient['outcome'] == 'D' ? 'selected' : ''; ?>>Discontinued</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <label for="remarks" class="form-label">Remarks:</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($patient['remarks']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="btn-section">
                <a href="rabies_registry.php" class="btn btn-secondary">Back</a>
                <div>
                    <button type="reset" class="btn btn-warning">Reset</button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update' : 'Add'; ?> Patient</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-calculate vaccination dates based on Day 0
        document.getElementById('vaccine_day0').addEventListener('change', function() {
            const day0Date = new Date(this.value);
            if (!isNaN(day0Date.getTime())) {
                // Day 3
                const day3Date = new Date(day0Date);
                day3Date.setDate(day0Date.getDate() + 3);
                document.getElementById('vaccine_day3').value = day3Date.toISOString().split('T')[0];
                
                // Day 7
                const day7Date = new Date(day0Date);
                day7Date.setDate(day0Date.getDate() + 7);
                document.getElementById('vaccine_day7').value = day7Date.toISOString().split('T')[0];
                
                // Day 14
                const day14Date = new Date(day0Date);
                day14Date.setDate(day0Date.getDate() + 14);
                document.getElementById('vaccine_day14').value = day14Date.toISOString().split('T')[0];
                
                // Day 28
                const day28Date = new Date(day0Date);
                day28Date.setDate(day0Date.getDate() + 28);
                document.getElementById('vaccine_day2830').value = day28Date.toISOString().split('T')[0];
            }
        });
    </script>
</body>

</html>