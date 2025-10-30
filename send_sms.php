<?php
// send_sms.php - Handles SMS sending requests
include 'sms_service.php';
include 'db_conn.php';

header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            
            // Fetch appointment details from database
            $appointment = getAppointmentById($appointment_id);
            
            if (!$appointment) {
                echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                exit;
            }
            
            // Extract date and time from datetime
            $datetime = new DateTime($appointment['appointment_date']);
            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');
            
            // Send SMS using the single confirmation function
            $result = $sms->sendAppointmentConfirmation(
                $appointment['name'],
                $appointment['contact'],
                $appointment['program'],
                $date,
                $time
            );
            
            if ($result['success']) {
                // Update database to mark SMS as sent
                updateSMSStatus($appointment_id, 'sent');
                echo json_encode([
                    'success' => true, 
                    'message' => 'SMS sent successfully to ' . $appointment['name']
                ]);
            } else {
                // Update status to failed
                updateSMSStatus($appointment_id, 'failed');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to send SMS: ' . $result['message']
                ]);
            }
            break;
            
        case 'send_all':
            // Get all upcoming appointments in the next 24 hours that haven't been sent SMS
            $now = new DateTime();
            $next_24h = new DateTime();
            $next_24h->modify('+24 hours');
            
            $appointments = getUpcomingAppointments($now->format('Y-m-d H:i:s'), $next_24h->format('Y-m-d H:i:s'), 'pending');
            
            $sent_count = 0;
            $failed_count = 0;
            $total_appointments = count($appointments);
            
            if ($total_appointments === 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'No pending appointments found for the next 24 hours'
                ]);
                break;
            }
            
            foreach ($appointments as $appointment) {
                $datetime = new DateTime($appointment['appointment_date']);
                $date = $datetime->format('Y-m-d');
                $time = $datetime->format('H:i:s');
                
                // Send SMS using the single confirmation function
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
                usleep(500000); // 0.5 second delay
            }
            
            if ($sent_count > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "SMS sent: $sent_count successful" . ($failed_count > 0 ? ", $failed_count failed" : "") . " out of $total_appointments appointments"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to send SMS. All $failed_count attempts failed."
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
?>