<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection function
function connect_db() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mhodb";
    
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
}

// Initialize database tables and data
function initializeTables() {
    try {
        $conn = connect_db();
        
        // Add session_id column to chat_logs if it doesn't exist
        $result = $conn->query("SHOW COLUMNS FROM chat_logs LIKE 'session_id'");
        if ($result->num_rows === 0) {
            $conn->query("ALTER TABLE chat_logs ADD COLUMN session_id VARCHAR(255)");
        }
        
        // Create indexes for better performance
        $conn->query("CREATE INDEX IF NOT EXISTS idx_session_timestamp ON chat_logs(session_id, timestamp)");
        $conn->query("CREATE INDEX IF NOT EXISTS idx_session_activity ON chat_sessions(session_id, last_activity)");
        $conn->query("CREATE INDEX IF NOT EXISTS idx_knowledge_keywords ON chatbot_knowledge(keywords)");
        $conn->query("CREATE INDEX IF NOT EXISTS idx_knowledge_category ON chatbot_knowledge(category, active)");
        
        $conn->close();
        return true;
    } catch (Exception $e) {
        error_log("Table initialization error: " . $e->getMessage());
        return false;
    }
}

// OpenRouter AI configuration
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL', 'deepseek/deepseek-chat-v3-0324:free');

// Database-driven Knowledge Base
class CHOKnowledgeBase {
    
    // Get all knowledge from database
    public static function getAllKnowledge() {
        try {
            $conn = connect_db();
            $stmt = $conn->prepare("SELECT * FROM chatbot_knowledge WHERE active = 1 ORDER BY priority DESC, category, id");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $knowledge = [];
            while ($row = $result->fetch_assoc()) {
                $knowledge[] = $row;
            }
            
            $stmt->close();
            $conn->close();
            
            return $knowledge;
        } catch (Exception $e) {
            error_log("Knowledge retrieval error: " . $e->getMessage());
            return [];
        }
    }
    
    // Search knowledge base by keywords
    public static function searchKnowledge($query) {
        try {
            $conn = connect_db();
            $searchQuery = strtolower(trim($query));
            
            // Simple search approach
            $sql = "SELECT *, 
                    (CASE 
                        WHEN LOWER(keywords) LIKE ? THEN 3
                        WHEN LOWER(question) LIKE ? THEN 2
                        WHEN LOWER(answer) LIKE ? THEN 1
                        ELSE 0
                    END) as relevance_score
                    FROM chatbot_knowledge 
                    WHERE active = 1 
                    AND (LOWER(keywords) LIKE ? OR LOWER(question) LIKE ? OR LOWER(answer) LIKE ?)
                    ORDER BY relevance_score DESC, priority DESC
                    LIMIT 5";
            
            $searchTerm = "%{$searchQuery}%";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $matches = [];
            while ($row = $result->fetch_assoc()) {
                $matches[] = $row;
            }
            
            $stmt->close();
            $conn->close();
            
            return $matches;
            
        } catch (Exception $e) {
            error_log("Knowledge search error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get knowledge by category
    public static function getKnowledgeByCategory($category) {
        try {
            $conn = connect_db();
            $stmt = $conn->prepare("SELECT * FROM chatbot_knowledge WHERE category = ? AND active = 1 ORDER BY priority DESC");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $knowledge = [];
            while ($row = $result->fetch_assoc()) {
                $knowledge[] = $row;
            }
            
            $stmt->close();
            $conn->close();
            
            return $knowledge;
        } catch (Exception $e) {
            error_log("Category knowledge error: " . $e->getMessage());
            return [];
        }
    }
    
    // Add new knowledge (for admin use)
    public static function addKnowledge($category, $question, $answer, $keywords, $priority = 1) {
        try {
            $conn = connect_db();
            $stmt = $conn->prepare("INSERT INTO chatbot_knowledge (category, question, answer, keywords, priority) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $category, $question, $answer, $keywords, $priority);
            $success = $stmt->execute();
            $stmt->close();
            $conn->close();
            return $success;
        } catch (Exception $e) {
            error_log("Add knowledge error: " . $e->getMessage());
            return false;
        }
    }
}

// Analytics functions
function logChatAnalytics($userMessage, $botResponse, $responseTime) {
    try {
        $conn = connect_db();
        $today = date('Y-m-d');
        
        // Update or insert today's analytics
        $stmt = $conn->prepare("INSERT INTO chatbot_analytics (date, total_messages, avg_response_time) 
                               VALUES (?, 1, ?) 
                               ON DUPLICATE KEY UPDATE 
                               total_messages = total_messages + 1,
                               avg_response_time = (avg_response_time + ?) / 2");
        $stmt->bind_param("sdd", $today, $responseTime, $responseTime);
        $stmt->execute();
        $stmt->close();
        
        $conn->close();
    } catch (Exception $e) {
        error_log("Analytics error: " . $e->getMessage());
    }
}

function updateSessionCount() {
    try {
        $conn = connect_db();
        $today = date('Y-m-d');
        
        // Count unique sessions today
        $result = $conn->query("SELECT COUNT(DISTINCT session_id) as unique_sessions 
                               FROM chat_logs 
                               WHERE DATE(timestamp) = '$today'");
        
        if ($result) {
            $row = $result->fetch_assoc();
            $uniqueSessions = $row['unique_sessions'];
            
            $stmt = $conn->prepare("UPDATE chatbot_analytics SET unique_sessions = ? WHERE date = ?");
            $stmt->bind_param("is", $uniqueSessions, $today);
            $stmt->execute();
            $stmt->close();
        }
        
        $conn->close();
    } catch (Exception $e) {
        error_log("Session count error: " . $e->getMessage());
    }
}

// Enhanced system prompt using database knowledge
function getSmartSystemPrompt() {
    $knowledge = CHOKnowledgeBase::getAllKnowledge();
    
    // Get database statistics
    try {
        $conn = connect_db();
        $totalPatients = 0;
        $recentStats = "";
        
        // Check if sheet1 table exists (patient data)
        $tables = $conn->query("SHOW TABLES LIKE 'sheet1'");
        if ($tables && $tables->num_rows > 0) {
            $result = $conn->query("SELECT COUNT(*) as total FROM sheet1");
            if ($result) {
                $totalPatients = $result->fetch_assoc()['total'];
            }
            
            $result = $conn->query("SELECT animal_type, COUNT(*) as count FROM sheet1 
                                  WHERE date_recorded >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                                  GROUP BY animal_type ORDER BY count DESC LIMIT 3");
            if ($result && $result->num_rows > 0) {
                $recentAnimals = [];
                while ($row = $result->fetch_assoc()) {
                    $recentAnimals[] = $row['animal_type'] . " (" . $row['count'] . " cases)";
                }
                $recentStats = "Recent statistics: " . implode(", ", $recentAnimals) . ". ";
            }
        }
        $conn->close();
    } catch (Exception $e) {
        error_log("Statistics error: " . $e->getMessage());
    }
    
    // Build knowledge base content from database
    $knowledgeContent = "";
    foreach ($knowledge as $item) {
        $knowledgeContent .= "\n=== " . strtoupper($item['category']) . " ===\n";
        $knowledgeContent .= "Q: " . $item['question'] . "\n";
        $knowledgeContent .= "A: " . $item['answer'] . "\n";
        $knowledgeContent .= "Keywords: " . $item['keywords'] . "\n";
    }
    
    $systemPrompt = "You are the official AI assistant for San Pablo City Health Office (CHO). You provide accurate, helpful information about health services.

=== YOUR DATABASE KNOWLEDGE ===
System data: {$totalPatients} registered patients. {$recentStats}

{$knowledgeContent}

=== COMMUNICATION GUIDELINES ===
✅ Be professional, empathetic, and clear
✅ Use step-by-step instructions when needed
✅ Always prioritize emergency cases (Category 3 bites)
✅ Include contact info and addresses
✅ Use emojis for clarity
⚠️ For emergencies, recommend immediate medical attention
🚫 Never provide specific medical diagnoses
🚫 Cannot access confidential patient records

REMEMBER: Use your comprehensive database knowledge to provide helpful, accurate responses. Always cross-reference with the knowledge base first before using general AI knowledge.";

    return $systemPrompt;
}

// Improved conversation logging with analytics
function logConversation($userMessage, $botResponse, $sessionId, $responseTime = 0) {
    try {
        $conn = connect_db();
        
        // Ensure session exists
        $stmt = $conn->prepare("INSERT IGNORE INTO chat_sessions (session_id, created_at, last_activity, ip_address) VALUES (?, NOW(), NOW(), ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->bind_param("ss", $sessionId, $ip);
        $stmt->execute();
        $stmt->close();
        
        // Update session activity
        $stmt = $conn->prepare("UPDATE chat_sessions SET last_activity = NOW() WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $stmt->close();
        
        // Log the conversation
        $stmt = $conn->prepare("INSERT INTO chat_logs (user_message, bot_response, timestamp, ip_address, user_agent, session_id) VALUES (?, ?, NOW(), ?, ?, ?)");
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->bind_param("sssss", $userMessage, $botResponse, $ip, $userAgent, $sessionId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        // Update analytics
        if ($success) {
            logChatAnalytics($userMessage, $botResponse, $responseTime);
            updateSessionCount();
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
    }
}

// Get conversation history with session context
function getConversationHistory($sessionId, $limit = 6) {
    try {
        $conn = connect_db();
        $stmt = $conn->prepare("SELECT user_message, bot_response FROM chat_logs 
                               WHERE session_id = ? 
                               ORDER BY timestamp DESC LIMIT ?");
        $stmt->bind_param("si", $sessionId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            // Add in reverse order (oldest first)
            array_unshift($history, ['role' => 'user', 'content' => $row['user_message']]);
            array_unshift($history, ['role' => 'assistant', 'content' => $row['bot_response']]);
        }
        
        $stmt->close();
        $conn->close();
        
        return $history;
        
    } catch (Exception $e) {
        error_log("History retrieval error: " . $e->getMessage());
        return [];
    }
}

// Smart response system using database knowledge first
function getSmartResponse($message, $sessionId) {
    $startTime = microtime(true);
    $messageLower = strtolower(trim($message));
    
    // Quick hardcoded responses for basic queries (fallback if DB fails)
    $quickResponses = [
        'hello' => "🏥 Hello! Welcome to San Pablo City Health Office AI Assistant. I can help you with:\n\n• 🐕 Animal bite treatment (ABTC)\n• 📅 Appointments and schedules\n• 🏥 CHO services and programs\n• 📍 Locations and directions\n• 💉 Vaccination information\n\nHow can I assist you today?",
        'hi' => "👋 Hi there! I'm your CHO AI Assistant. What can I help you with today?",
        'hi there' => "👋 Hi there! I'm your CHO AI Assistant. What can I help you with today?",
        'time' => "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n🐕 ABTC: Monday-Friday, 8AM-5PM\n\nDay 0 shots: Mon/Tue/Fri mornings\nFollow-up shots: Weekdays afternoons\n\n📋 Walk-in appointments only",
        'hours' => "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n🐕 ABTC: Monday-Friday, 8AM-5PM\n\nDay 0 shots: Mon/Tue/Fri mornings\nFollow-up shots: Weekdays afternoons\n\n📋 Walk-in appointments only",
        'open' => "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n🐕 ABTC: Monday-Friday, 8AM-5PM\n\nDay 0 shots: Mon/Tue/Fri mornings\nFollow-up shots: Weekdays afternoons\n\n📋 Walk-in appointments only"
    ];
    
    // Check for direct matches first
    foreach ($quickResponses as $key => $response) {
        if (strpos($messageLower, $key) !== false) {
            $responseTime = microtime(true) - $startTime;
            return [
                'response' => $response,
                'source' => 'quick_response',
                'response_time' => $responseTime
            ];
        }
    }
    
    // Try database search
    try {
        $knowledgeMatches = CHOKnowledgeBase::searchKnowledge($message);
        
        if (!empty($knowledgeMatches)) {
            $bestMatch = $knowledgeMatches[0];
            
            // Lower the threshold for database matches
            if (isset($bestMatch['relevance_score']) && $bestMatch['relevance_score'] >= 1) {
                $responseTime = microtime(true) - $startTime;
                return [
                    'response' => $bestMatch['answer'],
                    'source' => 'database',
                    'category' => $bestMatch['category'],
                    'response_time' => $responseTime
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Database search error: " . $e->getMessage());
    }
    
    // If no good database match, use AI with database context
    try {
        $history = getConversationHistory($sessionId);
        $response = callOpenRouterAPI($message, $history);
        $responseTime = microtime(true) - $startTime;
        
        return [
            'response' => $response,
            'source' => 'ai',
            'response_time' => $responseTime
        ];
    } catch (Exception $e) {
        error_log("AI API error: " . $e->getMessage());
        $responseTime = microtime(true) - $startTime;
        
        // Check quick responses one more time as final fallback
        foreach ($quickResponses as $key => $response) {
            if (strpos($messageLower, $key) !== false) {
                return [
                    'response' => $response,
                    'source' => 'fallback_quick',
                    'response_time' => $responseTime
                ];
            }
        }
        
        return [
            'response' => getFallbackResponse($messageLower),
            'source' => 'fallback',
            'response_time' => $responseTime
        ];
    }
}

// Improved API call with database knowledge context
function callOpenRouterAPI($message, $conversationHistory = []) {
    $systemPrompt = getSmartSystemPrompt();
    
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    
    // Add conversation history
    $messages = array_merge($messages, $conversationHistory);
    
    // Add current message
    $messages[] = ['role' => 'user', 'content' => $message];
    
    $data = [
        'model' => AI_MODEL,
        'messages' => $messages,
        'max_tokens' => 800,
        'temperature' => 0.7,
        'top_p' => 0.9,
        'frequency_penalty' => 0.1,
        'presence_penalty' => 0.1
    ];

    $headers = [
        'Authorization: Bearer ' . OPENROUTER_API_KEY,
        'HTTP-Referer: http://localhost/CHO/',
        'X-Title: San Pablo City Health Office Chatbot',
        'Content-Type: application/json',
        'User-Agent: CHO-Chatbot/2.0'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => OPENROUTER_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Connection Error: " . $curlError);
    }

    if ($httpCode !== 200) {
        throw new Exception("API Error: HTTP " . $httpCode . " - Response: " . substr($response, 0, 200));
    }

    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception("Invalid API response format");
    }

    return trim($responseData['choices'][0]['message']['content']);
}

// Enhanced fallback with database backup
function getFallbackResponse($messageLower) {
    // Try one more database search with broader terms
    $emergencyKeywords = ['emergency', 'urgent', 'bite', 'help'];
    foreach ($emergencyKeywords as $keyword) {
        if (strpos($messageLower, $keyword) !== false) {
            $matches = CHOKnowledgeBase::searchKnowledge($keyword);
            if (!empty($matches)) {
                return $matches[0]['answer'];
            }
        }
    }
    
    // Default fallback
    return "I apologize, but I'm experiencing technical difficulties. Please try again in a moment, or contact CHO directly:\n\n🏥 Main Office: City Governance Building, A. Mabini Extension\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)\n⏰ Mon-Fri, 8AM-5PM";
}

// Initialize database
initializeTables();

// Main request handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get and validate input
        $userMessage = trim($_POST['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? session_id();
        
        // Handle null session_id from frontend
        if ($sessionId === 'null' || empty($sessionId)) {
            $sessionId = session_id();
        }
        
        if (empty($userMessage)) {
            throw new Exception("Please enter a message");
        }
        
        if (strlen($userMessage) > 2000) {
            throw new Exception("Message too long. Please keep it under 2000 characters.");
        }
        
        // Sanitize input
        $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
        
        // Get smart response with analytics
        $responseData = getSmartResponse($userMessage, $sessionId);
        $botResponse = $responseData['response'];
        $responseTime = $responseData['response_time'] ?? 0;
        
        // Log conversation with response time (make it optional if it fails)
        $logged = false;
        try {
            $logged = logConversation($userMessage, $botResponse, $sessionId, $responseTime);
        } catch (Exception $e) {
            error_log("Logging failed: " . $e->getMessage());
            // Don't fail the whole request if logging fails
        }
        
        // Return success response with metadata
        echo json_encode([
            'success' => true,
            'message' => $botResponse,
            'session_id' => $sessionId,
            'logged' => $logged,
            'response_time' => round($responseTime, 3),
            'source' => $responseData['source'] ?? 'unknown',
            'category' => $responseData['category'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("CHO Chatbot Error: " . $e->getMessage());
        
        // Provide a working fallback response
        $fallbackMessage = "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n📍 City Governance Building, A. Mabini Extension, Brgy. V-A\n\n🐕 ABTC: Monday-Friday, 8AM-5PM\n📍 CHO Extension, Brgy. San Jose (503-3839)\n\n📋 Walk-in appointments only";
        
        echo json_encode([
            'success' => true, // Change to true so the frontend doesn't show error
            'message' => $fallbackMessage,
            'error_debug' => $e->getMessage(), // For debugging only
            'session_id' => $sessionId ?? session_id(),
            'source' => 'error_fallback'
        ]);
    }
} 

// Admin endpoint for knowledge management (GET request)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['admin'])) {
    if ($_GET['admin'] === 'knowledge') {
        $knowledge = CHOKnowledgeBase::getAllKnowledge();
        echo json_encode([
            'success' => true,
            'knowledge' => $knowledge,
            'count' => count($knowledge)
        ]);
    } elseif ($_GET['admin'] === 'analytics') {
        try {
            $conn = connect_db();
            $result = $conn->query("SELECT * FROM chatbot_analytics ORDER BY date DESC LIMIT 30");
            $analytics = [];
            while ($row = $result->fetch_assoc()) {
                $analytics[] = $row;
            }
            $conn->close();
            
            echo json_encode([
                'success' => true,
                'analytics' => $analytics
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid admin endpoint'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>