<?php
class SMSService {
    private $apikey = 'f5f812d919b4887be0994f94f7c8c202';
    private $sendername = 'SPCITYHEALTH';
    
    public function sendSMS($number, $message) {
        $ch = curl_init();
        $parameters = array(
            'apikey' => $this->apikey,
            'number' => $number,
            'message' => $message,
            'sendername' => $this->sendername
        );
        
        curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($output, true);
        
        if ($httpcode == 200 && isset($response[0]['status']) && $response[0]['status'] == 'Queued') {
            return array(
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $response
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Failed to send SMS',
                'response' => $response,
                'error' => $output
            );
        }
    }
    
    // Send appointment confirmation SMS
    public function sendAppointmentConfirmation($name, $contact, $program, $date, $time) {
        $formatted_date = date('F j, Y', strtotime($date));
        $formatted_time = date('g:i A', strtotime($time));
        
        $message = "Dear $name, your appointment for $program is confirmed on $formatted_date at $formatted_time. Please arrive 15 minutes early. - City Health Office of San Pablo";
        
        return $this->sendSMS($contact, $message);
    }
    
    // Send appointment reminder SMS
    public function sendAppointmentReminder($name, $contact, $program, $date, $time) {
        $formatted_date = date('F j, Y', strtotime($date));
        $formatted_time = date('g:i A', strtotime($time));
        
        $message = "Reminder: Dear $name, you have an appointment for $program tomorrow ($formatted_date) at $formatted_time. Please don't forget! - City Health Office of San Pablo";
        
        return $this->sendSMS($contact, $message);
    }
    
    // Check SMS balance
    public function checkBalance() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/account?apikey=' . $this->apikey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($output, true);
    }
}
?>