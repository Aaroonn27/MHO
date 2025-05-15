<?php
$services = [
    [
        "name" => "Issuance of Health Certification for Workers of Business Establishments",
        "content" => "<strong>Pursuant to The Code on Sanitation of the Philippines</strong> (P.D. 856 Chapter III, Section 15) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br><strong>Office or Division:</strong> City Health Office<br><strong>Who May Avail:</strong> Workers of food and non-food establishments<br><strong>Requirements:</strong><ol><li><strong>Valid Laboratory Exam Result</strong> (Click “More Details” For Clarification)</li><li><strong>1x1 ID Picture</strong></li><li><strong>Community Tax Certificate for the current Year</strong></li><li><strong>Identification Card</strong></li><a href='https://drive.google.com/file/d/1bzcWDB02_oAagntv52JUbiM8wipUbGpi/view?usp=drive_link'>More Details</a></ol>"
    ],
    [
        "name" => "Issuance of Medical Certificates for Employment, On-the-job-training, Loans, Scholarships, School Entrants",
        "content" => "As required by employers, schools and financial institutions. Fees collected is pursuant to Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br><strong>Office or Division:</strong> City Health Office<br><strong>Who May Avail:</strong> Applicants for employment, on-the-job-training, loans, scholarships, school entrant<br><strong>Requirements:</strong><ol><li><strong>Identification Card</strong></li><li><strong>Valid Laboratory Exam Results (Click “More Details” For Clarification)</strong></li><a href='https://drive.google.com/file/d/1uhFaIHCb0K7ewsj14dj1Rci1DTqsPKHh/view?usp=drive_link'>More Details</a></ol>"
    ],
    [
        "name" => "Issuance of Medical Certificate for Tricycle Drivers (Tricycle Franchise)",
        "content" => "<strong>Pursuant to Local Ordinance No. 2011-01</strong> (The 2011 Revised Comprehensive Traffic Code of the City of San Pablo, and Creating a Comprehensive and Integrated Traffic Management System/Traffic Assessment Plan in the City of San Pablo)<br><strong>Office or Division:</strong> City Health Office<br><strong>Who May Avail:</strong> Tricycle Drivers<br><strong>Requirements:</strong><ol><li><strong>Driver's License</strong></li><li><strong>Unified Clearance</strong></li><a href='https://drive.google.com/file/d/11Hb-Sz7ivHctu_TFhpitSPvH8cOfbVI9/view?usp=drive_link'>More Details</a></ol>"
    ],
    [
        "name" => "Issuance of Medical Certificate for Leave of Absence",
        "content" => "As required by private employers and pursuant to CSC MC No. 41, s. 1998<br><strong>Office or Division:</strong> City Health Office<br><strong>Who May Avail:</strong> Government Employees and General Public<br><strong>Requirements:</strong><ol><li><strong>Consultation within the first three (3) days of illness</strong></li><li><strong>Laboratory Test (If Available)</strong></li><a href='https://drive.google.com/file/d/1ER8_ky-ooMjzNFNTmLxnoOxmfpjQvlGS/view?usp=drive_link'>More Details</a></ol>"
    ],
    [
        "name" => "Issuance of Medical Certificates for Persons with Disabilities (PWDs)",
        "content" => "<strong>Pursuant to National Council on Disability Affairs Administrative Order No. 001, s. 2008</strong><br><strong>Office or Division:</strong> City Health Office<br><strong>Who May Avail:</strong> Persons with Disabilities and/or their relatives<br><strong>Requirements:</strong><ol><li><strong>Philippine Registry Form for Persons with Disabilities</strong></li><li><strong>Certification from a Specialist if the disability is uncertain (e.g. Psychiatrist for Psychosocial Disability)</strong></li><li><strong>Proof of the disability if client is unable to report for physical examination and assessment</strong></li><a href='https://drive.google.com/file/d/173ZpJwyo8AVjci1erlrxOwzfsJIOG9rS/view?usp=drive_link'>More Details</a></ol>"
    ]
];
$programs = [
    [
        "name" => "Dengue Prevention Campaign",
        "content" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras quis nulla ac nunc mollis aliquam. Proin vitae velit vitae leo tincidunt ultrices. Integer vel rutrum dui, at tempus metus."
    ],
    [
        "name" => "Nutrition Month Activities",
        "content" => "Nulla facilisi. Vivamus venenatis, turpis at pulvinar euismod, nisi tortor eleifend elit, non finibus sem nisi sed turpis. Donec eu interdum erat, vel convallis lacus."
    ],
    [
        "name" => "Community Wellness Program",
        "content" => "Sed vitae tincidunt dolor. Aliquam erat volutpat. Integer sed eros at dolor vehicula vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae."
    ],
    [
        "name" => "Health Screening Days",
        "content" => "Fusce id molestie libero. Duis venenatis, neque id gravida tincidunt, metus lacus pretium nisi, ac tincidunt odio justo in arcu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas."
    ],
    [
        "name" => "Senior Health Initiative",
        "content" => "Morbi consequat accumsan leo, eu dictum urna tempor ut. In hac habitasse platea dictumst. Praesent vehicula, nisi in cursus dapibus, tellus tellus accumsan quam, vel facilisis metus enim vel tortor."
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Style/homee.css">
    <link rel="stylesheet" href="Style/headerstyles.css">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="banner">
        <h1>CITY HEALTH OFFICE OF SAN PABLO</h1>
        <p>Providing quality healthcare services to our community</p>
    </div>

    <main>
        <div class="column">
            <h2>Services</h2>
            <div class="content-box">
                <?php foreach ($services as $index => $service): ?>
                    <div class="dropdown-item">
                        <div class="dropdown-header">
                            <span><?php echo $service['name']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-content">
                            <?php echo $service['content']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="column">
            <h2>Programs</h2>
            <div class="content-box">
                <?php foreach ($programs as $index => $program): ?>
                    <div class="dropdown-item">
                        <div class="dropdown-header">
                            <span><?php echo $program['name']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-content">
                            <p><?php echo $program['content']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <h2>About Us</h2>
        <p>The City Health Office of San Pablo is dedicated to providing quality healthcare services to the residents of San Pablo City. Our mission is to promote health, prevent disease, and protect the well-being of our community through accessible and responsive healthcare programs.</p>
    </footer>

    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                const header = item.querySelector('.dropdown-header');

                header.addEventListener('click', function() {
                    // Close all other dropdowns
                    dropdownItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Toggle current dropdown
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>

</html>