<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database connection
require_once 'db_conn.php';

// OpenRouter AI configuration
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL', 'deepseek/deepseek-chat-v3-0324:free');

// System prompt for City Health Office context with rabies program focus
$systemPrompt = "You are a helpful chatbot for the Municipal Health Office (MHO). You specialize in providing information about:

1. Rabies exposure and prevention
2. Post-exposure prophylaxis (PEP) procedures
3. Vaccination schedules for rabies
4. Animal bite first aid and wound washing
5. When to seek immediate medical attention for animal bites
6. Rabies immunoglobulin (RIG) administration
7. Health appointments and services
8. General health inquiries

You should be professional, empathetic, and informative. When discussing rabies exposure, emphasize the importance of immediate medical attention and proper wound care. If you don't know specific information about local health services, acknowledge this and suggest contacting the Municipal Health Office directly.";

function logChatMessage($userMessage, $botResponse) {
    try {
        $conn = connect_db();
        
        // Create chat_logs table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS chat_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_message TEXT NOT NULL,
            bot_response TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            INDEX idx_timestamp (timestamp)
        )";
        $conn->query($createTable);
        
        $stmt = $conn->prepare("INSERT INTO chat_logs (user_message, bot_response, timestamp, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt->bind_param("ssss", $userMessage, $botResponse, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Database logging error: " . $e->getMessage());
    }
}

function callOpenRouterAPI($message) {
    global $systemPrompt;
    
    $data = [
        'model' => AI_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $headers = [
        'Authorization: Bearer ' . OPENROUTER_API_KEY,
        'HTTP-Referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
        'X-Title: City Health Office Chatbot',
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, OPENROUTER_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception("API Error: HTTP " . $httpCode);
    }

    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception("Invalid API response format");
    }

    return $responseData['choices'][0]['message']['content'];
}

// Main request handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get user message
        $userMessage = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        if (empty($userMessage)) {
            throw new Exception("No message provided");
        }

        // Basic input validation and sanitization
        $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
        
        if (strlen($userMessage) > 1000) {
            throw new Exception("Message too long. Please keep it under 1000 characters.");
        }

        // Call OpenRouter AI API
        $botResponse = callOpenRouterAPI($userMessage);
        
        // Log the conversation to database
        logChatMessage($userMessage, $botResponse);

        // Return successful response
        echo json_encode([
            'success' => true,
            'message' => $botResponse
        ]);

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Chatbot Error: " . $e->getMessage());
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'I apologize, but I encountered an error. Please try again later or contact the City Health Office directly.',
            'error' => $e->getMessage() // Remove this in production
        ]);
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

// Close database connection if it exists
// Note: Database connections are handled within individual functions using connect_db()
?>