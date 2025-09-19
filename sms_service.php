<?php
// mocean_sms_service.php - FIXED VERSION with Bearer token authentication

class SMSService {
    private $api_token = 'apit-tRaNRyrsaIUYn6wIuzAa0L7GEKey80Es-HC3oY';
    private $sender_id = 'SPCITYHEALTH';
    
    public function sendSMS($number, $message) {
        // Format phone number for Mocean
        $formatted_number = $this->formatPhoneNumber($number);
        
        $url = 'https://rest.moceanapi.com/rest/2/sms';
        
        $data = array(
            'mocean-from' => $this->sender_id,
            'mocean-to' => $formatted_number,
            'mocean-text' => $message,
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_token,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
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
        
        $response_data = json_decode($response, true);
        
        // Check if SMS was sent successfully
        if ($httpcode >= 200 && $httpcode < 300) {
            if (isset($response_data['messages']) && is_array($response_data['messages']) && count($response_data['messages']) > 0) {
                $message_status = $response_data['messages'][0];
                
                if (isset($message_status['status']) && $message_status['status'] == '0') {
                    return array(
                        'success' => true,
                        'message' => 'SMS sent successfully',
                        'response' => $response_data,
                        'message_id' => $message_status['message-id'] ?? 'unknown'
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => 'SMS failed: ' . ($message_status['err_msg'] ?? 'Unknown error'),
                        'response' => $response_data,
                        'error' => $response
                    );
                }
            } else {
                return array(
                    'success' => false,
                    'message' => 'Invalid response format from Mocean',
                    'response' => $response_data,
                    'error' => $response
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => 'HTTP Error: ' . $httpcode,
                'response' => $response_data,
                'error' => $response
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
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_token
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($output, true);
        
        if ($httpcode === 200) {
            // Check for successful response (status 0 = success in Mocean)
            if (isset($response['status']) && $response['status'] == 0 && isset($response['value'])) {
                return array(
                    'credits' => number_format($response['value'], 2) . ' credits',
                    'currency' => $response['currency'] ?? 'USD'
                );
            } else if (isset($response['value'])) {
                // Sometimes value exists without status field
                return array(
                    'credits' => number_format($response['value'], 2) . ' credits',
                    'currency' => $response['currency'] ?? 'USD'
                );
            }
        }
        
        return array(
            'credits' => 'Error loading balance',
            'error' => $output
        );
    }
    
    // Test connection to Mocean API
    public function testConnection() {
        $url = 'https://rest.moceanapi.com/rest/2/account/pricing/outbound/sms';
        
        $data = array(
            'mocean-resp-format' => 'json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_token
        ));
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