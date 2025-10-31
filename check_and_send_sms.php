<?php
$CHECK_INTERVAL = 3600; // Check every 1 hour (in seconds)
$SEND_BEFORE_HOURS = 24; // Send SMS 24 hours before appointment

// File to track last check time
$last_check_file = __DIR__ . '/last_sms_check.txt';

// Function to check if we should run the SMS check
function shouldCheckSMS($last_check_file, $check_interval) {
    if (!file_exists($last_check_file)) {
        return true;
    }
    
    $last_check = (int)file_get_contents($last_check_file);
    $current_time = time();
    
    // Check if enough time has passed since last check
    return ($current_time - $last_check) >= $check_interval;
}

// Function to send SMS for upcoming appointments
function sendUpcomingSMS($send_before_hours) {
    // Use require_once to prevent redeclaring functions
    require_once __DIR__ . '/sms_service.php';
    require_once __DIR__ . '/db_conn.php';
    
    $sms = new SMSService();
    
    // Calculate time window
    $now = new DateTime();
    $future = new DateTime();
    $future->modify("+{$send_before_hours} hours");
    
    // Get appointments in the next X hours that haven't been sent SMS
    $appointments = getUpcomingAppointments(
        $now->format('Y-m-d H:i:s'), 
        $future->format('Y-m-d H:i:s'), 
        'pending'
    );
    
    $sent_count = 0;
    $failed_count = 0;
    
    foreach ($appointments as $appointment) {
        $datetime = new DateTime($appointment['appointment_date']);
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        
        // Send SMS
        $result = $sms->sendAppointmentConfirmation(
            $appointment['name'],
            $appointment['contact'],
            $appointment['program'],
            $date,
            $time
        );
        
        if ($result['success']) {
            updateSMSStatus($appointment['id'], 'sent');
            $sent_count++;
        } else {
            updateSMSStatus($appointment['id'], 'failed');
            $failed_count++;
        }
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 second
    }
    
    return [
        'sent' => $sent_count,
        'failed' => $failed_count,
        'total' => count($appointments)
    ];
}

// Main execution - Only run if not already defined (prevents duplicate execution)
if (!defined('AUTO_SMS_EXECUTED')) {
    define('AUTO_SMS_EXECUTED', true);
    
    if (shouldCheckSMS($last_check_file, $CHECK_INTERVAL)) {
        try {
            // Run the SMS check
            $result = sendUpcomingSMS($SEND_BEFORE_HOURS);
            
            // Update last check time
            file_put_contents($last_check_file, time());
            
            // Optional: Log the results (commented out - uncomment if you want logs)
            // if ($result['total'] > 0) {
            //     error_log("SMS Auto-send: {$result['sent']} sent, {$result['failed']} failed out of {$result['total']} appointments");
            // }
        } catch (Exception $e) {
            // Silently catch errors to prevent breaking the page
            error_log("SMS Auto-send Error: " . $e->getMessage());
        }
    }
}
?>