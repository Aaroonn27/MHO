<?php
// Cleaned send_sms.php - Remove duplicate functions since they exist in db_conn.php
include 'sms_service.php';
include 'db_conn.php';

header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $sms = new SMSService();
    
    switch ($action) {
        case 'send_individual':
            $appointment_id = $_POST['appointment_id'] ?? '';
            
            if (empty($appointment_id)) {
                echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
                exit;
            }
            
            // Fetch appointment details from database using existing function
            $appointment = getAppointmentById($appointment_id);
            
            if (!$appointment) {
                echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                exit;
            }
            
            // Extract date and time from datetime
            $datetime = new DateTime($appointment['appointment_date']);
            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');
            
            $result = $sms->sendAppointmentReminder(
                $appointment['name'],
                $appointment['contact'],
                $appointment['program'],
                $date,
                $time
            );
            
            if ($result['success']) {
                // Update database to mark SMS as sent using existing function
                updateSMSStatus($appointment_id, 'sent');
                echo json_encode([
                    'success' => true, 
                    'message' => 'SMS reminder sent successfully to ' . $appointment['name']
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to send SMS: ' . $result['message']
                ]);
            }
            break;
            
        case 'send_all':
            // Get all appointments for tomorrow that haven't been sent SMS
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $appointments = getAppointmentsForDate($tomorrow, 'pending');
            
            $sent_count = 0;
            $failed_count = 0;
            $total_appointments = count($appointments);
            
            if ($total_appointments === 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'No appointments found for tomorrow'
                ]);
                break;
            }
            
            foreach ($appointments as $appointment) {
                $datetime = new DateTime($appointment['appointment_date']);
                $date = $datetime->format('Y-m-d');
                $time = $datetime->format('H:i:s');
                
                $result = $sms->sendAppointmentReminder(
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
                usleep(500000); // 0.5 second delay
            }
            
            if ($sent_count > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "SMS reminders sent: $sent_count successful" . ($failed_count > 0 ? ", $failed_count failed" : "") . " out of $total_appointments appointments"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to send SMS reminders. All $failed_count attempts failed."
                ]);
            }
            break;
            
        case 'check_balance':
            $balance = $sms->checkBalance();
            
            // Handle the balance response correctly
            if (isset($balance['credits']) && $balance['credits'] !== 'Error loading balance') {
                echo json_encode([
                    'success' => true,
                    'balance' => $balance
                ]);
            } else {
                // Even if it shows "Error loading balance", check if we have actual balance data
                if (isset($balance['error'])) {
                    $error_data = json_decode($balance['error'], true);
                    if (isset($error_data['status']) && $error_data['status'] == 0 && isset($error_data['value'])) {
                        // Status 0 means success in Mocean API
                        echo json_encode([
                            'success' => true,
                            'balance' => [
                                'credits' => number_format($error_data['value'], 2) . ' credits',
                                'currency' => 'USD'
                            ]
                        ]);
                        break;
                    }
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to retrieve balance',
                    'balance' => $balance
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Note: Helper functions are now removed from here since they exist in db_conn.php
// If the functions don't exist in db_conn.php, you'll need to add them there or check the function names
?>