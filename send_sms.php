<?php
include 'sms_service.php';
include 'db_conn.php';

header('Content-Type: application/json');

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
            
            $result = $sms->sendAppointmentReminder(
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
                    $failed_count++;
                }
                
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 second delay
            }
            
            echo json_encode([
                'success' => true,
                'message' => "SMS sent: $sent_count successful, $failed_count failed"
            ]);
            break;
            
        case 'check_balance':
            $balance = $sms->checkBalance();
            echo json_encode([
                'success' => true,
                'balance' => $balance
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Helper functions
function getAppointmentById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getAppointmentsForDate($date, $sms_status = 'pending') {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE DATE(appointment_date) = ? AND (sms_status = ? OR sms_status IS NULL)");
    $stmt->bind_param("ss", $date, $sms_status);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateSMSStatus($appointment_id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE appointments SET sms_status = ?, sms_sent_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $appointment_id);
    return $stmt->execute();
}
?>