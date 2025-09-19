<?php
// mocean_single_token_sms_service.php - For single token accounts

class SMSService {
    private $api_token = 'apit-tRaNRyrsaIUYn6wIuzAa0L7GEKey80Es-HC3oY'; // Your full token here
    private $sender_id = 'Aaron'; // Your sender name (max 11 characters)
    
    public function sendSMS($number, $message) {
        // Format phone number for Mocean
        $formatted_number = $this->formatPhoneNumber($number);
        
        $url = 'https://rest.moceanapi.com/rest/2/sms';
        
        $data = array(
            'mocean-api-key' => $this->api_token,
            'mocean-from' => $this->sender_id,
            'mocean-to' => $formatted_number,
            'mocean-text' => $message,
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($curl_error) {
            return array(
                'success' => false,
                'message' => 'cURL Error: ' . $curl_error,
                'response' => null,
                'error' => $curl_error
            );
        }
        
        $response = json_decode($output, true);
        
        // Check if SMS was sent successfully
        if ($httpcode >= 200 && $httpcode < 300) {
            if (isset($response['messages']) && is_array($response['messages']) && count($response['messages']) > 0) {
                $message_status = $response['messages'][0];
                
                if (isset($message_status['status']) && $message_status['status'] == '0') {
                    return array(
                        'success' => true,
                        'message' => 'SMS sent successfully',
                        'response' => $response,
                        'message_id' => $message_status['message-id'] ?? 'unknown'
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => 'SMS failed: ' . ($message_status['err-msg'] ?? 'Unknown error'),
                        'response' => $response,
                        'error' => $output
                    );
                }
            } else {
                return array(
                    'success' => false,
                    'message' => 'Invalid response format from Mocean',
                    'response' => $response,
                    'error' => $output
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => 'HTTP Error: ' . $httpcode,
                'response' => $response,
                'error' => $output
            );
        }
    }
    
    // Format phone number for Mocean (supports Philippine numbers)
    private function formatPhoneNumber($number) {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Handle Philippine numbers
        if (substr($number, 0, 2) === '09') {
            // Convert 09XXXXXXXXX to 639XXXXXXXXX
            return '63' . substr($number, 1);
        } elseif (substr($number, 0, 1) === '9' && strlen($number) === 10) {
            // Convert 9XXXXXXXXX to 639XXXXXXXXX
            return '63' . $number;
        } elseif (substr($number, 0, 2) === '63') {
            // Already in correct format
            return $number;
        } elseif (substr($number, 0, 3) === '+63') {
            // Remove + sign
            return substr($number, 1);
        } else {
            // Assume it's a Philippine number and add 63 prefix
            return '63' . ltrim($number, '0');
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
        $url = 'https://rest.moceanapi.com/rest/2/account/balance';
        
        $data = array(
            'mocean-api-key' => $this->api_token,
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($output, true);
        
        if ($httpcode === 200 && isset($response['value'])) {
            return array(
                'credits' => number_format($response['value'], 2) . ' credits',
                'currency' => $response['currency'] ?? 'USD'
            );
        } else {
            return array(
                'credits' => 'Error loading balance',
                'error' => $output
            );
        }
    }
    
    // Test connection to Mocean API
    public function testConnection() {
        $url = 'https://rest.moceanapi.com/rest/2/account/pricing/outbound/sms';
        
        $data = array(
            'mocean-api-key' => $this->api_token,
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array(
            'success' => $httpcode === 200,
            'http_code' => $httpcode,
            'response' => $output
        );
    }
}
?>