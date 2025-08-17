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

// Initialize database tables if needed
function initializeTables() {
    try {
        $conn = connect_db();
        
        // Add session_id column to chat_logs if it doesn't exist
        $conn->query("ALTER TABLE chat_logs ADD COLUMN IF NOT EXISTS session_id VARCHAR(255)");
        
        // Create index for better performance
        $conn->query("CREATE INDEX IF NOT EXISTS idx_session_timestamp ON chat_logs(session_id, timestamp)");
        $conn->query("CREATE INDEX IF NOT EXISTS idx_session_activity ON chat_sessions(session_id, last_activity)");
        
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

// Enhanced Knowledge Base with better structure
class CHOKnowledgeBase {
    
    public static function getAllKnowledge() {
        return [
            'cho_info' => [
                'name' => 'San Pablo City Health Office (CHO)',
                'main_office' => [
                    'address' => 'Ground Floor, City Governance Building, A. Mabini Extension',
                    'barangay' => 'Barangay V-A',
                    'city' => 'San Pablo City',
                    'hours' => 'Monday to Friday, 8:00 AM to 5:00 PM',
                    'appointment_type' => 'Walk-in only (first come, first serve)'
                ],
                'services' => [
                    'general_health' => true,
                    'insurance_processing' => ['PhilHealth', 'Health cards'],
                    'pwd_services' => 'Relatives can process requirements'
                ]
            ],
            
            'abtc_info' => [
                'name' => 'Animal Bite Treatment Center (ABTC)',
                'location' => [
                    'address' => 'CHO Extension, Brgy. San Jose, San Pablo City',
                    'contact' => '503-3839'
                ],
                'hours' => 'Monday to Friday, 8:00 AM to 5:00 PM',
                'capacity' => '60 slots per day',
                'cost' => 'Free service',
                'schedule' => [
                    'day_0' => [
                        'days' => ['Monday', 'Tuesday', 'Friday'],
                        'time' => 'Morning only',
                        'shots' => 3,
                        'description' => 'First injection day'
                    ],
                    'follow_up' => [
                        'days' => 'Weekdays',
                        'time' => 'Afternoon',
                        'description' => 'Follow-up injections'
                    ]
                ],
                'bite_categories' => [
                    'category_1' => [
                        'description' => 'No bite, saliva contact only, intact skin',
                        'treatment' => 'Observation only, no vaccine needed',
                        'urgency' => 'Low'
                    ],
                    'category_2' => [
                        'description' => 'Abrasions, small wounds, broken skin',
                        'treatment' => 'Vaccine required within 14 days',
                        'urgency' => 'Moderate'
                    ],
                    'category_3' => [
                        'description' => 'Deep wounds, punctures, wounds on face/hands/feet',
                        'treatment' => 'EMERGENCY - Vaccine within 7 days',
                        'urgency' => 'HIGH PRIORITY'
                    ]
                ],
                'first_aid' => [
                    'immediate' => 'Wash wound with clean water for 10-15 minutes',
                    'avoid' => 'Do not apply ointments or traditional remedies',
                    'observe_animal' => 'Monitor animal for 14 days for behavioral changes'
                ]
            ],
            
            'rhu_locations' => [
                'District I-A' => [
                    'location' => 'Bagong Pook',
                    'barangays' => ['I-A', 'I-B', 'I-C', 'IV-B', 'IV-C', 'V-A', 'V-B', 'V-C', 'V-D', 'VI-A', 'VI-B', 'VI-C', 'VI-D', 'VI-E', 'San Lucas I', 'San Lucas II', 'San Pedro', 'Dolores', 'San Buenaventura', 'Sta. Catalina']
                ],
                'District I-B' => [
                    'location' => 'Barangay 2D',
                    'barangays' => ['II-A', 'II-B', 'II-C', 'II-D', 'II-E', 'II-F', 'VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'San Gabriel', 'San Miguel', 'San Bartolome', 'Santiago I', 'Santiago II', 'Bautista']
                ],
                'District II' => [
                    'location' => 'Conception',
                    'barangays' => ['III-A', 'III-B', 'III-C', 'III-D', 'III-E', 'III-F', 'IV-A', 'Concepcion A', 'Concepcion B', 'San Diego', 'Sta. Isabel', 'San Lorenzo', 'Sto. Angel A', 'Sto. Angel B']
                ],
                'District III' => [
                    'location' => 'Del Remedio',
                    'barangays' => ['Del Remedio A', 'Del Remedio B', 'San Juan', 'Sta. Maria Magdalena', 'San Marcos', 'San Mateo', 'Sta. Filomena', 'San Crispin', 'San Nicolas', 'Sta. Veronica', 'Sta. Monica', 'San Roque', 'San Rafael']
                ],
                'District IV' => [
                    'location' => 'Sta. Maria',
                    'barangays' => ['Sta. Maria', 'Soledad', 'Santisimo Rosario', 'Atisan', 'San Isidro', 'Sta. Ana', 'San Joaquin', 'San Vicente', 'Sta. Cruz', 'San Antonio I', 'San Antonio II', 'San Francisco A', 'San Francisco B']
                ],
                'District V' => [
                    'location' => 'Sto. Cristo',
                    'barangays' => ['Sto. Cristo', 'San Jose', 'San Cristobal', 'San Ignacio', 'San Gregorio', 'Sto. Niño', 'Sta. Elena']
                ]
            ],
            
            'other_programs' => [
                'tb_dots' => [
                    'name' => 'TB-DOTS Program',
                    'location' => 'CHO Extension (same building as ABTC)',
                    'services' => ['Chest X-ray', 'Gene Expert sputum examination'],
                    'process' => 'Visit barangay hall for scheduling, then barangay health center for forms'
                ],
                'social_hygiene' => [
                    'name' => 'Social Hygiene Program',
                    'location' => 'CHO Extension',
                    'details' => 'Reproductive health services available'
                ]
            ]
        ];
    }
    
    public static function findRHUByBarangay($barangay) {
        $knowledge = self::getAllKnowledge();
        $rhu_locations = $knowledge['rhu_locations'];
        
        foreach ($rhu_locations as $district => $info) {
            if (in_array($barangay, $info['barangays'])) {
                return [
                    'district' => $district,
                    'location' => $info['location'],
                    'barangays' => $info['barangays']
                ];
            }
        }
        return null;
    }
}

// Enhanced system prompt with comprehensive knowledge
function getSmartSystemPrompt() {
    $knowledge = CHOKnowledgeBase::getAllKnowledge();
    
    // Get database statistics
    try {
        $conn = connect_db();
        $total_patients = 0;
        $recent_stats = "";
        
        // Check if sheet1 table exists (your patient data table)
        $tables = $conn->query("SHOW TABLES LIKE 'sheet1'");
        if ($tables && $tables->num_rows > 0) {
            $result = $conn->query("SELECT COUNT(*) as total FROM sheet1");
            if ($result) {
                $total_patients = $result->fetch_assoc()['total'];
            }
            
            $result = $conn->query("SELECT animal_type, COUNT(*) as count FROM sheet1 
                                  WHERE date_recorded >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                                  GROUP BY animal_type ORDER BY count DESC LIMIT 3");
            if ($result && $result->num_rows > 0) {
                $recent_animals = [];
                while ($row = $result->fetch_assoc()) {
                    $recent_animals[] = $row['animal_type'] . " (" . $row['count'] . " cases)";
                }
                $recent_stats = "Recent statistics: " . implode(", ", $recent_animals) . ". ";
            }
        }
        $conn->close();
    } catch (Exception $e) {
        error_log("Statistics error: " . $e->getMessage());
    }
    
    $systemPrompt = "You are the official AI assistant for San Pablo City Health Office (CHO). You provide accurate, helpful information about health services.

=== YOUR KNOWLEDGE BASE ===
You have comprehensive information about:
- San Pablo City Health Office services and locations
- Animal Bite Treatment Center (ABTC) procedures and schedules
- Rural Health Units (RHU) by district and barangay
- Other health programs (TB-DOTS, Social Hygiene)
- Current system data: {$total_patients} registered patients. {$recent_stats}

=== KEY INFORMATION ===

🏥 CHO MAIN OFFICE:
📍 Ground Floor, City Governance Building, A. Mabini Extension, Brgy. V-A
⏰ Monday-Friday, 8AM-5PM
📋 Walk-in appointments only (first come, first serve)
💳 Accepts PhilHealth and health insurance

🐕 ABTC (Animal Bite Treatment):
📍 CHO Extension, Brgy. San Jose, San Pablo City
📞 503-3839
⏰ Monday-Friday, 8AM-5PM
🎯 60 slots daily, FREE service

📅 ABTC SCHEDULE:
• Day 0 (First shots): Monday/Tuesday/Friday - MORNINGS ONLY
• Follow-up shots: Any weekday - AFTERNOONS ONLY

🩹 BITE CATEGORIES:
• Category 1: No bite/saliva contact → Observation only
• Category 2: Small wounds → Vaccine within 14 days
• Category 3: Deep/face/hand wounds → EMERGENCY (within 7 days)

🚨 FIRST AID: Wash 10-15 minutes with clean water, NO ointments

=== RURAL HEALTH UNITS ===
• District I-A: RHU Bagong Pook
• District I-B: RHU Barangay 2D
• District II: RHU Conception
• District III: RHU Del Remedio
• District IV: RHU Sta. Maria
• District V: RHU Sto. Cristo

=== OTHER PROGRAMS ===
🫁 TB-DOTS: Chest X-ray, Gene Expert (at CHO Extension)
🏥 Social Hygiene: Reproductive health services (at CHO Extension)

=== COMMUNICATION GUIDELINES ===
✅ Be professional, empathetic, and clear
✅ Use step-by-step instructions when needed
✅ Always prioritize emergency cases (Category 3 bites)
✅ Include contact info and addresses
✅ Use emojis for clarity
⚠️ For emergencies, recommend immediate medical attention
🚫 Never provide specific medical diagnoses
🚫 Cannot access confidential patient records

REMEMBER: You are knowledgeable and should provide helpful responses based on your comprehensive knowledge base, not just pattern matching.";

    return $systemPrompt;
}

// Improved conversation logging
function logConversation($userMessage, $botResponse, $sessionId) {
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
        
        if (!$success) {
            throw new Exception("Failed to log conversation");
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
    }
}

// Get conversation history for context
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

// Smart response system that tries local knowledge first, then AI
function getSmartResponse($message, $sessionId) {
    $message_lower = strtolower(trim($message));
    
    // Quick responses for common queries
    $quickResponses = [
        'hello' => "🏥 Hello! Welcome to San Pablo City Health Office AI Assistant. I can help you with:\n\n• 🐕 Animal bite treatment (ABTC)\n• 📅 Appointments and schedules\n• 🏥 CHO services and programs\n• 📍 Locations and directions\n• 💉 Vaccination information\n\nHow can I assist you today?",
        
        'hi' => "👋 Hi there! I'm your CHO AI Assistant. What can I help you with today?",
        
        'abtc schedule' => "📅 ABTC SCHEDULE:\n\n🐕 Animal Bite Treatment Center\n📍 CHO Extension, Brgy. San Jose (503-3839)\n⏰ Monday-Friday, 8AM-5PM\n\n📋 SCHEDULE DETAILS:\n• Day 0 (First shots): Monday, Tuesday, Friday - MORNING ONLY\n• Follow-up shots: Any weekday - AFTERNOON ONLY\n• Capacity: 60 slots per day\n• Cost: FREE\n\n⚠️ For Category 3 wounds (deep/face/hands): Seek treatment within 7 days!",
        
        'cho location' => "📍 SAN PABLO CITY HEALTH OFFICE LOCATIONS:\n\n🏥 MAIN CHO:\nGround Floor, City Governance Building\nA. Mabini Extension, Brgy. V-A\n⏰ Monday-Friday, 8AM-5PM\n\n🐕 ABTC (Animal Bite):\nCHO Extension, Brgy. San Jose\n📞 503-3839\n⏰ Monday-Friday, 8AM-5PM\n\n📋 Walk-in appointments only (first come, first serve)",
        
        'hours' => "⏰ CHO OPERATING HOURS:\n\n🏥 Main Office: Monday-Friday, 8AM-5PM\n🐕 ABTC: Monday-Friday, 8AM-5PM\n\nDay 0 shots: Mon/Tue/Fri mornings\nFollow-up shots: Weekdays afternoons\n\n📋 Walk-in appointments only",
        
        'programs' => "🏥 CHO PROGRAMS & SERVICES:\n\n🐕 Animal Bite Treatment Center (ABTC)\n🫁 TB-DOTS Program\n🏥 Social Hygiene Program\n👥 PWD Services\n💳 Insurance Processing (PhilHealth)\n\nWhich program would you like to know more about?"
    ];
    
    // Check for exact matches first
    foreach ($quickResponses as $key => $response) {
        if ($message_lower === $key) {
            return $response;
        }
    }
    
    // Check for keyword matches
    foreach ($quickResponses as $key => $response) {
        if (strpos($message_lower, $key) !== false) {
            return $response;
        }
    }
    
    // If no quick response, use AI with conversation history
    try {
        $history = getConversationHistory($sessionId);
        return callOpenRouterAPI($message, $history);
    } catch (Exception $e) {
        error_log("AI API error: " . $e->getMessage());
        return getFallbackResponse($message_lower);
    }
}

// Improved API call with better error handling
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
        'User-Agent: CHO-Chatbot/1.0'
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

// Fallback response when AI is not available
function getFallbackResponse($message_lower) {
    if (strpos($message_lower, 'emergency') !== false || strpos($message_lower, 'bite') !== false) {
        return "🚨 ANIMAL BITE EMERGENCY:\n\n1. Wash wound with clean water (10-15 minutes)\n2. Do NOT apply ointments\n3. Seek immediate medical attention\n\n📍 ABTC: CHO Extension, Brgy. San Jose\n📞 503-3839\n⏰ 8AM-5PM, Mon-Fri";
    }
    
    if (strpos($message_lower, 'location') !== false) {
        return "📍 CHO LOCATIONS:\n🏥 Main: City Governance Building, A. Mabini Extension, Brgy. V-A\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)";
    }
    
    return "I apologize, but I'm experiencing technical difficulties. Please try again in a moment, or contact CHO directly:\n\n🏥 Main Office: City Governance Building, A. Mabini Extension\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)\n⏰ Mon-Fri, 8AM-5PM";
}

// Initialize tables on first run
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
        
        if (empty($userMessage)) {
            throw new Exception("Please enter a message");
        }
        
        if (strlen($userMessage) > 2000) {
            throw new Exception("Message too long. Please keep it under 2000 characters.");
        }
        
        // Sanitize input
        $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
        
        // Get smart response
        $botResponse = getSmartResponse($userMessage, $sessionId);
        
        // Log conversation
        $logged = logConversation($userMessage, $botResponse, $sessionId);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => $botResponse,
            'session_id' => $sessionId,
            'logged' => $logged,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("CHO Chatbot Error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => getFallbackResponse('error'),
            'error' => $e->getMessage(),
            'session_id' => $sessionId ?? null
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