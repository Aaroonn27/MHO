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

// Enhanced system prompt with rabies program context
function getSystemPrompt() {
    // Get some basic statistics from the database to provide context
    try {
        $conn = connect_db();
        
        // Get basic stats
        $total_patients = 0;
        $recent_stats = "";
        
        $sql = "SELECT COUNT(*) as total FROM sheet1";
        $result = $conn->query($sql);
        if ($result) {
            $total_patients = $result->fetch_assoc()['total'];
        }
        
        // Get recent bite statistics
        $sql = "SELECT animal_type, COUNT(*) as count FROM sheet1 
                WHERE date_recorded >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                GROUP BY animal_type ORDER BY count DESC LIMIT 3";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $recent_animals = [];
            while ($row = $result->fetch_assoc()) {
                $recent_animals[] = $row['animal_type'] . " (" . $row['count'] . " cases)";
            }
            $recent_stats = "Recent 30-day animal bite statistics: " . implode(", ", $recent_animals) . ". ";
        }
        
        $conn->close();
        
        $systemPrompt = "You are a helpful chatbot for the Municipal Health Office (MHO) Rabies Exposure Management System. 

SYSTEM CONTEXT:
- Our database currently tracks {$total_patients} rabies exposure patients
- {$recent_stats}
- We manage rabies exposure cases with categories 1, 2, and 3 based on severity
- We track vaccination schedules (Day 0, 3, 7, 14, 28-30)
- We monitor RIG (Rabies Immunoglobulin) administration
- We record patient outcomes: Complete (C), Incomplete (Inc), None (N), Died (D)

YOUR EXPERTISE:
1. Rabies exposure assessment and categorization
2. Post-exposure prophylaxis (PEP) procedures and schedules
3. Animal bite wound care and first aid
4. When to seek immediate medical attention
5. Rabies vaccination schedules and importance of compliance
6. RIG administration guidelines
7. Health appointments and MHO services
8. Prevention and education about rabies

IMPORTANT GUIDELINES:
- Always emphasize immediate medical attention for any animal bite
- Stress the importance of proper wound washing with soap and water
- Explain that rabies is almost 100% fatal if untreated but 100% preventable with proper PEP
- Be clear about vaccination schedules and the importance of completing the full course
- For specific medical advice, always recommend consulting healthcare professionals
- Provide empathetic support while being informative

Be professional, empathetic, and informative. If asked about specific patient records or confidential information, politely decline and suggest contacting the MHO directly.";

        return $systemPrompt;
        
    } catch (Exception $e) {
        // Fallback system prompt if database query fails
        return "You are a helpful chatbot for the Municipal Health Office (MHO). You specialize in rabies exposure management, post-exposure prophylaxis, vaccination schedules, and general health inquiries. Be professional, empathetic, and informative. Always emphasize the importance of immediate medical attention for animal bites.";
    }
}

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
    $systemPrompt = getSystemPrompt();
    
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
        'HTTP-Referer: http://localhost/MHO/',
        'X-Title: Municipal Health Office Chatbot',
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

// Function to provide quick responses for common queries
function getQuickResponse($message) {
    $message = strtolower(trim($message));
    
    // Common rabies-related quick responses
    $quickResponses = [
        'hello' => "Hello! I'm here to help you with rabies exposure questions, vaccination schedules, and general health inquiries. How can I assist you today?",
        'hi' => "Hi there! I'm the MHO chatbot. I can help you with rabies exposure information, vaccination schedules, and health services. What would you like to know?",
        'help' => "I can help you with:\n• Rabies exposure assessment\n• Vaccination schedules\n• Animal bite first aid\n• When to seek medical attention\n• MHO services and appointments\n\nWhat specific information do you need?",
        'emergency' => "⚠️ EMERGENCY: If you or someone has been bitten by an animal, please:\n1. Wash the wound immediately with soap and water for 10-15 minutes\n2. Apply antiseptic\n3. Seek immediate medical attention at the nearest health facility\n4. Do not delay - rabies prevention is time-sensitive!\n\nFor urgent cases, contact the MHO immediately.",
    ];
    
    foreach ($quickResponses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    return null;
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

        // Check for quick responses first
        $quickResponse = getQuickResponse($userMessage);
        
        if ($quickResponse) {
            $botResponse = $quickResponse;
        } else {
            // Call OpenRouter AI API for complex queries
            $botResponse = callOpenRouterAPI($userMessage);
        }
        
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
            'message' => 'I apologize, but I encountered an error. Please try again later or contact the Municipal Health Office directly for assistance.',
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
?>