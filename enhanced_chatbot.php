<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
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

// OpenRouter AI configuration
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL', 'deepseek/deepseek-chat-v3-0324:free');

// ADVANCED: Intent Detection System
class IntentDetector {
    
    private static $intents = [
        'animal_bite_emergency' => [
            'keywords' => ['bite', 'bitten', 'bit', 'attack', 'attacked', 'scratch', 'wound', 'injured by', 'hurt by'],
            'context' => ['dog', 'cat', 'animal', 'pet', 'puppy', 'kitten', 'stray', 'rabid'],
            'priority' => 10,
            'requires_both' => true
        ],
        'schedule_query' => [
            'keywords' => ['schedule', 'time', 'hour', 'open', 'close', 'when', 'available', 'day 0', 'follow-up'],
            'context' => ['abtc', 'cho', 'office', 'vaccine', 'shot'],
            'priority' => 8,
            'requires_both' => false
        ],
        'location_query' => [
            'keywords' => ['where', 'location', 'address', 'direction', 'find', 'go to', 'get to'],
            'context' => ['abtc', 'cho', 'office', 'building', 'brgy', 'barangay'],
            'priority' => 8,
            'requires_both' => false
        ],
        'cost_insurance' => [
            'keywords' => ['cost', 'price', 'fee', 'pay', 'free', 'philhealth', 'insurance', 'how much'],
            'context' => [],
            'priority' => 7,
            'requires_both' => false
        ],
        'vaccine_info' => [
            'keywords' => ['vaccine', 'vaccination', 'shot', 'dose', 'rabies', 'anti-rabies', 'immunization'],
            'context' => [],
            'priority' => 7,
            'requires_both' => false
        ],
        'greeting' => [
            'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'kumusta'],
            'context' => [],
            'priority' => 5,
            'requires_both' => false
        ]
    ];
    
    public static function detectIntent($message) {
        $messageLower = strtolower($message);
        $detectedIntents = [];
        
        foreach (self::$intents as $intentName => $intentData) {
            $keywordMatch = false;
            $contextMatch = false;
            
            // Check keywords
            foreach ($intentData['keywords'] as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    $keywordMatch = true;
                    break;
                }
            }
            
            // Check context (if any)
            if (empty($intentData['context'])) {
                $contextMatch = true; // No context required
            } else {
                foreach ($intentData['context'] as $context) {
                    if (strpos($messageLower, $context) !== false) {
                        $contextMatch = true;
                        break;
                    }
                }
            }
            
            // Determine if intent matches
            if ($intentData['requires_both']) {
                if ($keywordMatch && $contextMatch) {
                    $detectedIntents[] = [
                        'intent' => $intentName,
                        'priority' => $intentData['priority']
                    ];
                }
            } else {
                if ($keywordMatch || $contextMatch) {
                    $detectedIntents[] = [
                        'intent' => $intentName,
                        'priority' => $intentData['priority']
                    ];
                }
            }
        }
        
        // Sort by priority
        usort($detectedIntents, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return !empty($detectedIntents) ? $detectedIntents[0]['intent'] : 'general_query';
    }
}

// ADVANCED: Context-Aware Response Cache
class ResponseCache {
    private static $cache = [];
    private static $maxCacheSize = 50;
    
    public static function get($key) {
        $key = md5(strtolower($key));
        if (isset(self::$cache[$key])) {
            $cached = self::$cache[$key];
            // Cache valid for 1 hour
            if (time() - $cached['timestamp'] < 3600) {
                return $cached['response'];
            }
        }
        return null;
    }
    
    public static function set($key, $response) {
        $key = md5(strtolower($key));
        
        // Clear old cache if full
        if (count(self::$cache) >= self::$maxCacheSize) {
            self::$cache = array_slice(self::$cache, -25, null, true);
        }
        
        self::$cache[$key] = [
            'response' => $response,
            'timestamp' => time()
        ];
    }
}

// Enhanced Knowledge Base with semantic search
class CHOKnowledgeBase {
    
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
    
    // IMPROVED: Semantic search with intent
    public static function searchKnowledge($query, $intent = null) {
        try {
            $conn = connect_db();
            $searchQuery = strtolower(trim($query));
            
            // Intent-based category filtering
            $categoryFilter = "";
            if ($intent === 'animal_bite_emergency') {
                $categoryFilter = " AND (category = 'ABTC' OR category = 'Emergency')";
            } elseif ($intent === 'schedule_query') {
                $categoryFilter = " AND (category = 'ABTC' OR category = 'CHO')";
            } elseif ($intent === 'location_query') {
                $categoryFilter = " AND category = 'Location'";
            }
            
            // Extract key terms with better tokenization
            $terms = ['bite', 'bitten', 'bit', 'dog', 'cat', 'animal', 'attack', 'attacked', 'scratch', 'scratched',
                     'emergency', 'urgent', 'schedule', 'hours', 'time', 'location', 'where', 'address',
                     'abtc', 'cho', 'philhealth', 'vaccine', 'vaccination', 'treatment', 'rabies',
                     'wound', 'injured', 'hurt', 'first aid', 'help', 'cost', 'free', 'price'];
            
            $foundTerms = [];
            foreach ($terms as $term) {
                if (strpos($searchQuery, $term) !== false) {
                    $foundTerms[] = $term;
                }
            }
            
            if (empty($foundTerms)) {
                $words = explode(' ', $searchQuery);
                $foundTerms = array_filter($words, function($w) { return strlen($w) > 3; });
            }
            
            if (empty($foundTerms)) {
                return [];
            }
            
            // Build dynamic query with category filter
            $conditions = [];
            $params = [];
            $types = "";
            
            foreach ($foundTerms as $term) {
                $conditions[] = "(LOWER(keywords) LIKE ? OR LOWER(question) LIKE ? OR LOWER(answer) LIKE ?)";
                $searchTerm = "%{$term}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }
            
            $whereClause = implode(" OR ", $conditions);
            
            $sql = "SELECT *, 
                    (
                        (CASE WHEN LOWER(keywords) LIKE ? THEN 10 ELSE 0 END) +
                        (CASE WHEN LOWER(question) LIKE ? THEN 8 ELSE 0 END) +
                        (CASE WHEN LOWER(answer) LIKE ? THEN 3 ELSE 0 END)
                    ) as relevance_score
                    FROM chatbot_knowledge 
                    WHERE active = 1 AND ({$whereClause}){$categoryFilter}
                    ORDER BY relevance_score DESC, priority DESC
                    LIMIT 5";
            
            $fullSearchTerm = "%{$searchQuery}%";
            $allParams = array_merge([$fullSearchTerm, $fullSearchTerm, $fullSearchTerm], $params);
            $allTypes = "sss" . $types;
            
            $stmt = $conn->prepare($sql);
            if (!empty($allParams)) {
                $stmt->bind_param($allTypes, ...$allParams);
            }
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
    public static function getByCategory($category) {
        try {
            $conn = connect_db();
            $stmt = $conn->prepare("SELECT * FROM chatbot_knowledge WHERE category = ? AND active = 1 ORDER BY priority DESC LIMIT 1");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            return $row;
        } catch (Exception $e) {
            error_log("Category search error: " . $e->getMessage());
            return null;
        }
    }
}

// ADVANCED: Context-aware system prompt builder
function buildDynamicSystemPrompt($intent = null) {
    $knowledge = CHOKnowledgeBase::getAllKnowledge();
    
    $knowledgeText = "";
    
    // Prioritize knowledge based on intent
    if ($intent === 'animal_bite_emergency') {
        $priorityCategories = ['Emergency', 'ABTC'];
    } elseif ($intent === 'schedule_query') {
        $priorityCategories = ['ABTC', 'CHO'];
    } elseif ($intent === 'location_query') {
        $priorityCategories = ['Location', 'CHO', 'ABTC'];
    } else {
        $priorityCategories = [];
    }
    
    // Add priority knowledge first
    foreach ($priorityCategories as $category) {
        foreach ($knowledge as $item) {
            if ($item['category'] === $category) {
                $knowledgeText .= "\n[{$item['category']}] {$item['question']}\n{$item['answer']}\n";
            }
        }
    }
    
    // Add remaining knowledge
    foreach ($knowledge as $item) {
        if (!in_array($item['category'], $priorityCategories)) {
            $knowledgeText .= "\n[{$item['category']}] {$item['question']}\n{$item['answer']}\n";
        }
    }
    
    $intentContext = "";
    if ($intent === 'animal_bite_emergency') {
        $intentContext = "\n**CURRENT USER INTENT: ANIMAL BITE EMERGENCY**\nPrioritize providing immediate first aid instructions and ABTC contact information.\n";
    } elseif ($intent === 'greeting') {
        $intentContext = "\n**CURRENT USER INTENT: GREETING**\nRespond warmly and offer to help with their needs.\n";
    }
    
    $systemPrompt = "You are the San Pablo City Health Office AI Assistant. You provide accurate, empathetic health information to citizens.
{$intentContext}
KNOWLEDGE BASE:
{$knowledgeText}

RESPONSE GUIDELINES:
- Use the knowledge base to answer questions accurately
- Be conversational, warm, and helpful
- For emergencies: Provide clear step-by-step instructions
- Always include relevant contact information
- Use emojis naturally: 🏥 🐕 📍 ⏰ 📋 🚨 💉 💰
- Keep responses well-structured and easy to read
- If uncertain, guide users to contact CHO directly

KEY CONTACTS:
- ABTC: CHO Extension, Brgy. San Jose (503-3839)
- CHO Main: Ground Floor, City Governance Building, A. Mabini Extension, Brgy. V-A
- Hours: Monday-Friday, 8AM-5PM

Provide a helpful, accurate response based on the knowledge base.";

    return $systemPrompt;
}

// Get conversation history with better context
function getConversationHistory($sessionId, $limit = 4) {
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

// Enhanced AI call with intent awareness
function callAI($message, $sessionId, $intent, &$errorDetails = null) {
    try {
        $systemPrompt = buildDynamicSystemPrompt($intent);
        $history = getConversationHistory($sessionId);
        
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        $messages = array_merge($messages, $history);
        $messages[] = ['role' => 'user', 'content' => $message];
        
        $data = [
            'model' => AI_MODEL,
            'messages' => $messages,
            'max_tokens' => 800,
            'temperature' => 0.7,
            'top_p' => 0.9
        ];

        $headers = [
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: http://localhost/CHO/',
            'X-Title: CHO Chatbot',
            'Content-Type: application/json'
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
            $errorDetails = "CURL Error: {$curlError}";
            throw new Exception("Network connection error");
        }

        if ($httpCode !== 200) {
            $errorDetails = "HTTP {$httpCode}";
            throw new Exception("API error code {$httpCode}");
        }

        $responseData = json_decode($response, true);
        
        if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception("Invalid response format");
        }

        return trim($responseData['choices'][0]['message']['content']);
        
    } catch (Exception $e) {
        error_log("AI API Error: " . $e->getMessage());
        throw $e;
    }
}

// ADVANCED: Intent-based smart response system
function getSmartResponse($message, $sessionId) {
    $startTime = microtime(true);
    $messageLower = strtolower($message);
    
    // Check cache first for common queries
    $cachedResponse = ResponseCache::get($message);
    if ($cachedResponse !== null) {
        error_log("Cache hit for query");
        $responseTime = microtime(true) - $startTime;
        return [
            'response' => $cachedResponse,
            'source' => 'cache',
            'response_time' => $responseTime,
            'intent' => 'cached'
        ];
    }
    
    // Detect intent
    $intent = IntentDetector::detectIntent($message);
    error_log("Detected intent: {$intent}");
    
    // PRIORITY 1: Handle critical intents immediately
    if ($intent === 'animal_bite_emergency') {
        error_log("Priority animal bite emergency triggered");
        
        $matches = CHOKnowledgeBase::searchKnowledge($message, $intent);
        if (!empty($matches)) {
            $response = $matches[0]['answer'];
        } else {
            $response = "🚨 ANIMAL BITE EMERGENCY PROTOCOL:\n\n1️⃣ IMMEDIATE CARE:\n• Wash wound with clean water for 10-15 minutes\n• Do NOT apply any ointments\n• Do NOT cover the wound immediately\n\n2️⃣ OBSERVE THE ANIMAL:\n• Monitor for 14 days\n• Watch for unusual behavior/actions\n• If animal acts differently, seek immediate help\n\n3️⃣ SEEK MEDICAL ATTENTION:\n• Go to ABTC: CHO Extension, Brgy. San Jose\n• Call 503-3839\n• Bring any previous vaccination cards\n\n⏰ Hours: Monday-Friday, 8AM-5PM\n💰 Treatment is FREE";
        }
        
        ResponseCache::set($message, $response);
        $responseTime = microtime(true) - $startTime;
        return [
            'response' => $response,
            'source' => 'priority_emergency',
            'response_time' => $responseTime,
            'intent' => $intent
        ];
    }
    
    // PRIORITY 2: Handle greetings quickly
    if ($intent === 'greeting') {
        $greetings = [
            "🏥 Hello! Welcome to San Pablo City Health Office AI Assistant.\n\nI can help you with:\n• 🐕 Animal bite treatment and ABTC services\n• 📅 Schedules and operating hours\n• 📍 Office locations and directions\n• 💉 Vaccination information\n• 🏥 General health services\n\nWhat would you like to know?",
            "👋 Hi there! I'm your CHO AI Assistant. How can I help you today?\n\nI can assist with:\n• 🚨 Animal bite emergencies\n• ⏰ ABTC schedules\n• 📍 Office locations\n• 💳 Health services\n\nFeel free to ask anything!",
            "🏥 Good day! Welcome to San Pablo City Health Office.\n\nI'm here to help with:\n• Animal bite treatment\n• Office hours and schedules\n• Location information\n• Health services\n\nHow may I assist you?"
        ];
        $response = $greetings[array_rand($greetings)];
        
        $responseTime = microtime(true) - $startTime;
        return [
            'response' => $response,
            'source' => 'greeting',
            'response_time' => $responseTime,
            'intent' => $intent
        ];
    }
    
    // STRATEGY 1: Try AI with intent context
    $errorDetails = null;
    try {
        error_log("Calling AI with intent: {$intent}");
        $response = callAI($message, $sessionId, $intent, $errorDetails);
        
        if (!empty($response) && strlen($response) > 20) {
            ResponseCache::set($message, $response);
            $responseTime = microtime(true) - $startTime;
            
            return [
                'response' => $response,
                'source' => 'ai',
                'response_time' => $responseTime,
                'intent' => $intent
            ];
        }
    } catch (Exception $e) {
        error_log("AI failed: " . $e->getMessage());
    }
    
    // STRATEGY 2: Database with intent filtering
    error_log("Falling back to database with intent: {$intent}");
    $matches = CHOKnowledgeBase::searchKnowledge($message, $intent);
    
    if (!empty($matches)) {
        $responseTime = microtime(true) - $startTime;
        ResponseCache::set($message, $matches[0]['answer']);
        
        return [
            'response' => $matches[0]['answer'],
            'source' => 'database',
            'response_time' => $responseTime,
            'intent' => $intent
        ];
    }
    
    // STRATEGY 3: Intent-based fallback responses
    error_log("Using intent-based fallback");
    
    $fallbackResponses = [
        'schedule_query' => "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n📍 Ground Floor, City Governance Building, A. Mabini Extension, Brgy. V-A\n\n🐕 ABTC: Monday-Friday, 8AM-5PM\n📍 CHO Extension, Brgy. San Jose\n📞 503-3839\n\n📋 Walk-in appointments only\n• Day 0 shots: Mon/Tue/Fri mornings\n• Follow-up shots: Weekday afternoons",
        
        'location_query' => "📍 SAN PABLO CITY HEALTH OFFICE LOCATIONS:\n\n🏥 CHO MAIN OFFICE:\nGround Floor, City Governance Building\nA. Mabini Extension, Brgy. V-A\nSan Pablo City\n\n🐕 ABTC (Animal Bite Treatment Center):\nCHO Extension\nBrgy. San Jose, San Pablo City\n📞 503-3839\n\n⏰ Both offices: Monday-Friday, 8AM-5PM",
        
        'cost_insurance' => "💰 ABTC COSTS & INSURANCE:\n\n✅ FREE SERVICES:\n• All ABTC treatments are FREE\n• No charge for vaccines\n• No consultation fees\n\n💳 INSURANCE ACCEPTED:\n• PhilHealth cards accepted\n• Other health insurance cards accepted\n• Bring valid ID with your health card",
        
        'vaccine_info' => "💉 VACCINATION INFORMATION:\n\n📅 ABTC VACCINE SCHEDULE:\n• Day 0 (First shots): Mon/Tue/Fri mornings\n• Follow-up shots: Weekday afternoons\n• 60 slots per day (come early!)\n\n📍 Location: CHO Extension, Brgy. San Jose\n📞 503-3839\n⏰ Mon-Fri, 8AM-5PM\n💰 FREE",
        
        'general_query' => "I can help you with:\n\n🐕 Animal bite treatment and emergencies\n📅 ABTC schedules and appointments\n📍 CHO office locations\n⏰ Operating hours\n🏥 Health services and programs\n💳 PhilHealth and insurance\n\nWhat specific information do you need?\n\n📞 For urgent concerns:\n• ABTC: 503-3839\n• CHO Main: Ground Floor, City Governance Building"
    ];
    
    $response = $fallbackResponses[$intent] ?? $fallbackResponses['general_query'];
    
    $responseTime = microtime(true) - $startTime;
    return [
        'response' => $response,
        'source' => 'intent_fallback',
        'response_time' => $responseTime,
        'intent' => $intent
    ];
}

// Enhanced logging with intent tracking
function logConversation($userMessage, $botResponse, $sessionId, $intent = null, $source = null) {
    try {
        $conn = connect_db();
        
        // Check if columns exist, if not, log basic info
        $stmt = $conn->prepare("INSERT INTO chat_logs (user_message, bot_response, timestamp, ip_address, user_agent, session_id) VALUES (?, ?, NOW(), ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->bind_param("sssss", $userMessage, $botResponse, $ip, $userAgent, $sessionId);
        $success = $stmt->execute();
        $stmt->close();
        
        // Log analytics (intent and source) to error log for now
        if ($intent || $source) {
            error_log("Chat Analytics - Intent: {$intent}, Source: {$source}");
        }
        
        $conn->close();
        return $success;
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
    }
}

// Natural delay with variation
function addNaturalDelay($messageLength, $responseLength) {
    $baseDelay = 1.2;
    $readingTime = min($messageLength / 70, 1.5);
    $thinkingTime = min($responseLength / 150, 1.0);
    $randomness = (mt_rand(300, 800) / 1000);
    
    $totalDelay = $baseDelay + $readingTime + $thinkingTime + $randomness;
    $totalDelay = max(1.5, min($totalDelay, 3.5));
    
    usleep($totalDelay * 1000000);
}

// Main handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $userMessage = trim($_POST['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? session_id();
        
        if ($sessionId === 'null' || empty($sessionId)) {
            $sessionId = session_id();
        }
        
        if (empty($userMessage)) {
            throw new Exception("Please enter a message");
        }
        
        if (strlen($userMessage) > 2000) {
            throw new Exception("Message too long. Please keep it under 2000 characters.");
        }
        
        $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
        
        // Get response
        $startTime = microtime(true);
        $responseData = getSmartResponse($userMessage, $sessionId);
        $botResponse = $responseData['response'];
        $intent = $responseData['intent'] ?? 'unknown';
        $source = $responseData['source'] ?? 'unknown';
        
        // Add natural delay
        $processingTime = microtime(true) - $startTime;
        $targetDelay = 1.5 + (strlen($userMessage) / 80) + (rand(400, 900) / 1000);
        $targetDelay = max(1.5, min($targetDelay, 3.5));
        
        if ($processingTime < $targetDelay) {
            usleep(($targetDelay - $processingTime) * 1000000);
        }
        
        $totalTime = microtime(true) - $startTime;
        
        // Log conversation
        logConversation($userMessage, $botResponse, $sessionId, $intent, $source);
        
        echo json_encode([
            'success' => true,
            'message' => $botResponse,
            'session_id' => $sessionId,
            'response_time' => round($totalTime, 2),
            'source' => $source,
            'intent' => $intent,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("Main handler error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => "I'm having technical difficulties. For immediate assistance:\n\n🏥 CHO Main: Ground Floor, City Governance Building, A. Mabini Extension, Brgy. V-A\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)\n⏰ Mon-Fri, 8AM-5PM",
            'error' => $e->getMessage()
        ]);
    }
}

// Debug and admin endpoints
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['debug']) && $_GET['debug'] === 'test') {
        $testMessage = $_GET['message'] ?? 'dog bite emergency';
        $testSession = 'debug_' . time();
        
        error_log("=== DEBUG TEST START ===");
        $intent = IntentDetector::detectIntent($testMessage);
        $result = getSmartResponse($testMessage, $testSession);
        error_log("=== DEBUG TEST END ===");
        
        echo json_encode([
            'success' => true,
            'test_message' => $testMessage,
            'detected_intent' => $intent,
            'result' => $result,
            'knowledge_count' => count(CHOKnowledgeBase::getAllKnowledge())
        ]);
    } elseif (isset($_GET['admin'])) {
        if ($_GET['admin'] === 'knowledge') {
            $knowledge = CHOKnowledgeBase::getAllKnowledge();
            echo json_encode([
                'success' => true,
                'knowledge' => $knowledge,
                'count' => count($knowledge)
            ]);
        } elseif ($_GET['admin'] === 'cache_stats') {
            echo json_encode([
                'success' => true,
                'message' => 'Cache is active and operational'
            ]);
        }
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>