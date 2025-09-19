<?php
// sms_test.php - Create this file to test your SMS API
include 'sms_service.php';

echo "<h2>SMS API Test</h2>";

$sms = new SMSService();

// Test 1: Check Balance
echo "<h3>1. Testing Balance Check...</h3>";
$balance = $sms->checkBalance();
echo "<pre>";
print_r($balance);
echo "</pre>";

// Test 2: Send a test SMS (replace with your phone number)
echo "<h3>2. Testing SMS Send...</h3>";
$test_number = '09996291059'; // Replace with your actual number
$test_message = 'Test message from City Health Office SMS system';

$result = $sms->sendSMS($test_number, $test_message);
echo "<pre>";
print_r($result);
echo "</pre>";

// Test 3: Test appointment reminder format
echo "<h3>3. Testing Appointment Reminder...</h3>";
$reminder_result = $sms->sendAppointmentReminder(
    'Test Patient', 
    $test_number, 
    'ABTC Vaccination', 
    date('Y-m-d', strtotime('+1 day')), 
    '10:00:00'
);
echo "<pre>";
print_r($reminder_result);
echo "</pre>";

// Test 4: cURL Information
echo "<h3>4. Server cURL Information...</h3>";
if (function_exists('curl_version')) {
    $curl_info = curl_version();
    echo "cURL Version: " . $curl_info['version'] . "<br>";
    echo "SSL Version: " . $curl_info['ssl_version'] . "<br>";
    echo "Protocols: " . implode(', ', $curl_info['protocols']) . "<br>";
} else {
    echo "❌ cURL is not installed!<br>";
}

echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Enabled' : '❌ Disabled') . "<br>";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? '✅ Enabled' : '❌ Disabled') . "<br>";
?>