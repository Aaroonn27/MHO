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

// Structured Knowledge Base for CHO Services
class CHOKnowledgeBase {
    
    // San Pablo City Health Office Information
    public static function getCHOInfo() {
        return [
            'name' => 'San Pablo City Health Office (CHO)',
            'operating_hours' => [
                'days' => 'Monday to Friday',
                'time' => '8:00 AM to 5:00 PM'
            ],
            'location' => [
                'address' => 'Ground Floor, City Governance Building A. Mabini Extension',
                'barangay' => 'Barangay V-A',
                'city' => 'San Pablo City'
            ],
            'appointment_policy' => [
                'type' => 'Walk-in only',
                'system' => 'First come, first serve',
                'advance_booking' => false
            ],
            'special_services' => [
                'pwd_home_visit' => [
                    'available' => false,
                    'alternative' => 'Relatives can process requirements on behalf of PWD',
                    'barangay_nurses' => 'Available at Barangay halls for PWD monitoring'
                ]
            ],
            'insurance' => [
                'accepts_health_cards' => true,
                'types' => 'Various health insurance accepted'
            ]
        ];
    }

    // Animal Bite Treatment Center Information
    public static function getABTCInfo() {
        return [
            'name' => 'Animal Bite Treatment Center (ABTC)',
            'location' => [
                'address' => 'CHO Extension, Brgy. San Jose, San Pablo City',
                'contact' => '503-3839'
            ],
            'operating_hours' => [
                'days' => 'Monday to Friday',
                'time' => '8:00 AM to 5:00 PM',
                'day_0_schedule' => [
                    'days' => 'Monday, Tuesday, Friday',
                    'time' => 'Morning only',
                    'description' => 'First injection day'
                ],
                'follow_up_schedule' => [
                    'days' => 'Weekdays',
                    'time' => 'Afternoon',
                    'description' => 'Follow-up injections'
                ]
            ],
            'capacity' => '60 slots per day',
            'services' => [
                'cost' => 'Free',
                'insurance' => ['PhilHealth accepted'],
                'day_0_shots' => 3
            ],
            'bite_categories' => [
                'category_1' => [
                    'description' => 'Non-bite exposure (saliva contact, closed wound)',
                    'treatment' => 'Observation only, no vaccine needed',
                    'urgency' => 'low'
                ],
                'category_2' => [
                    'description' => 'Abrasion, small wounds',
                    'treatment' => 'Vaccine required within 14 days',
                    'urgency' => 'moderate'
                ],
                'category_3' => [
                    'description' => 'Punctured wounds, wounds on foot/face/hands',
                    'treatment' => 'Emergency - vaccine within 7 days',
                    'urgency' => 'high'
                ]
            ],
            'first_aid' => [
                'immediate_care' => 'Wash wound with clean water for 10-15 minutes',
                'avoid' => 'Do not apply ointments',
                'animal_observation' => 'Observe animal for 14 days for behavioral changes'
            ],
            'side_effects' => [
                'common' => ['fever', 'pain at injection site'],
                'management' => 'Apply warm compress or take paracetamol',
                'emergency' => 'For allergic reactions, proceed to ER immediately'
            ]
        ];
    }

    // Rural Health Units by District
    public static function getRuralHealthUnits() {
        return [
            'district_1a' => [
                'rhu_location' => 'Bagong Pook',
                'barangays' => ['I-A', 'I-B', 'I-C', 'IV-B', 'IV-C', 'V-A', 'V-B', 'V-C', 'V-D', 'VI-A', 'VI-B', 'VI-C', 'VI-D', 'VI-E', 'San Lucas I', 'San Lucas II', 'San Pedro', 'Dolores', 'San Buenaventura', 'Sta. Catalina']
            ],
            'district_1b' => [
                'rhu_location' => 'Barangay 2D',
                'barangays' => ['II-A', 'II-B', 'II-C', 'II-D', 'II-E', 'II-F', 'VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E', 'San Gabriel', 'San Miguel', 'San Bartolome', 'Santiago I', 'Santiago II', 'Bautista']
            ],
            'district_2' => [
                'rhu_location' => 'Conception',
                'barangays' => ['III-A', 'III-B', 'III-C', 'III-D', 'III-E', 'III-F', 'IV-A', 'Concepcion A', 'Concepcion B', 'San Diego', 'Sta. Isabel', 'San Lorenzo', 'Sto. Angel A', 'Sto. Angel B']
            ],
            'district_3' => [
                'rhu_location' => 'Del Remedio',
                'barangays' => ['Del Remedio A', 'Del Remedio B', 'San Juan', 'Sta. Maria Magdalena', 'San Marcos', 'San Mateo', 'Sta. Filomena', 'San Crispin', 'San Nicolas', 'Sta. Veronica', 'Sta. Monica', 'San Roque', 'San Rafael']
            ],
            'district_4' => [
                'rhu_location' => 'Sta. Maria',
                'barangays' => ['Sta. Maria', 'Soledad', 'Santisimo Rosario', 'Atisan', 'San Isidro', 'Sta. Ana', 'San Joaquin', 'San Vicente', 'Sta. Cruz', 'San Antonio I', 'San Antonio II', 'San Francisco A', 'San Francisco B']
            ],
            'district_5' => [
                'rhu_location' => 'Sto. Cristo',
                'barangays' => ['Sto. Cristo', 'San Jose', 'San Cristobal', 'San Ignacio', 'San Gregorio', 'Sto. Niño', 'Sta. Elena']
            ]
        ];
    }

    // Other Programs
    public static function getOtherPrograms() {
        return [
            'tb_dots' => [
                'name' => 'TB-DOTS Program',
                'services' => [
                    'chest_xray' => 'Available',
                    'gene_expert' => [
                        'description' => 'Sputum examination',
                        'process' => 'Go to barangay hall for scheduling, then to barangay health center for form and slots'
                    ]
                ],
                'location' => 'CHO Extension (same as ABTC)'
            ],
            'social_hygiene' => [
                'name' => 'Social Hygiene Program',
                'location' => 'CHO Extension (same as ABTC)',
                'details' => 'Program available at CHO Extension'
            ]
        ];
    }

    // Find RHU by barangay
    public static function findRHUByBarangay($barangay) {
        $rhus = self::getRuralHealthUnits();
        
        foreach ($rhus as $district => $data) {
            if (in_array($barangay, $data['barangays'])) {
                return [
                    'district' => $district,
                    'rhu_location' => $data['rhu_location']
                ];
            }
        }
        return null;
    }
}

// Enhanced system prompt with structured knowledge
function getEnhancedSystemPrompt() {
    try {
        $conn = connect_db();
        
        // Get database statistics
        $total_patients = 0;
        $recent_stats = "";
        
        $sql = "SELECT COUNT(*) as total FROM sheet1";
        $result = $conn->query($sql);
        if ($result) {
            $total_patients = $result->fetch_assoc()['total'];
        }
        
        $sql = "SELECT animal_type, COUNT(*) as count FROM sheet1 
                WHERE date_recorded >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                GROUP BY animal_type ORDER BY count DESC LIMIT 3";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $recent_animals = [];
            while ($row = $result->fetch_assoc()) {
                $recent_animals[] = $row['animal_type'] . " (" . $row['count'] . " cases)";
            }
            $recent_stats = "Recent 30-day statistics: " . implode(", ", $recent_animals) . ". ";
        }
        
        $conn->close();
        
        // Get structured knowledge
        $choInfo = CHOKnowledgeBase::getCHOInfo();
        $abtcInfo = CHOKnowledgeBase::getABTCInfo();
        $rhus = CHOKnowledgeBase::getRuralHealthUnits();
        $programs = CHOKnowledgeBase::getOtherPrograms();
        
        $systemPrompt = "You are the official AI assistant for San Pablo City Health Office (CHO). You have comprehensive knowledge about our services and programs.

=== CURRENT SYSTEM STATUS ===
- Database contains {$total_patients} registered patients
- {$recent_stats}

=== SAN PABLO CITY HEALTH OFFICE (CHO) ===
📍 Location: {$choInfo['location']['address']}, {$choInfo['location']['barangay']}
⏰ Hours: {$choInfo['operating_hours']['days']}, {$choInfo['operating_hours']['time']}
📋 Appointments: Walk-in only, first come first serve
💳 Insurance: Accepts various health cards and insurance
👥 PWD Services: Relatives can process requirements; nurses available at barangay halls

=== ANIMAL BITE TREATMENT CENTER (ABTC) ===
📍 Location: {$abtcInfo['location']['address']}
📞 Contact: {$abtcInfo['location']['contact']}
⏰ Hours: {$abtcInfo['operating_hours']['time']}, {$abtcInfo['operating_hours']['days']}
🎯 Capacity: {$abtcInfo['capacity']}
💰 Cost: {$abtcInfo['services']['cost']}

📅 SCHEDULES:
- Day 0 (First injection): Monday, Tuesday, Friday - MORNING ONLY
- Follow-ups: Weekdays - AFTERNOON ONLY

🩹 BITE CATEGORIES & TREATMENT:
- Category 1: No bite/saliva contact → Observation only
- Category 2: Small wounds/abrasions → Vaccine within 14 days  
- Category 3: Deep/face/hand wounds → EMERGENCY - Vaccine within 7 days

🚨 FIRST AID: Wash wound 10-15 minutes with clean water, NO ointments
🐕 Monitor animal for 14 days for behavioral changes

=== RURAL HEALTH UNITS (for PhilHealth processing) ===";

        foreach ($rhus as $district => $data) {
            $systemPrompt .= "\n- " . strtoupper(str_replace('_', ' ', $district)) . ": RHU at {$data['rhu_location']}";
        }

        $systemPrompt .= "\n\n=== OTHER PROGRAMS ===
🫁 TB-DOTS: Chest X-ray, Gene Expert (schedule at barangay hall)
🏥 Social Hygiene: Available at CHO Extension

=== YOUR COMMUNICATION STYLE ===
✅ Be professional, empathetic, and precise
✅ Use clear, simple language
✅ Provide step-by-step instructions when needed
✅ Always emphasize urgency for Category 3 bites
✅ Include relevant contact information and locations
✅ Use emojis to make information clearer
⚠️ For medical emergencies, direct to immediate medical attention
🚫 Never provide specific medical diagnoses
🚫 Cannot access confidential patient records

REMEMBER: Animal bites are medical emergencies. When in doubt, always recommend immediate medical consultation.";

        return $systemPrompt;
        
    } catch (Exception $e) {
        error_log("System prompt generation error: " . $e->getMessage());
        return getFallbackSystemPrompt();
    }
}

function getFallbackSystemPrompt() {
    return "You are the AI assistant for San Pablo City Health Office. You specialize in:
    - Animal bite treatment and rabies prevention
    - CHO services and appointments  
    - Health programs and scheduling
    Always prioritize patient safety and recommend immediate medical attention for emergencies.";
}

// Enhanced conversation logging with session tracking
function logChatMessage($userMessage, $botResponse, $sessionId = null) {
    try {
        $conn = connect_db();
        
        // Generate session ID if not provided
        if (!$sessionId) {
            $sessionId = session_id() ?: uniqid('chat_', true);
        }
        
        // Create or update session
        $stmt = $conn->prepare("INSERT INTO chat_sessions (session_id, ip_address, last_activity) 
                               VALUES (?, ?, NOW()) 
                               ON DUPLICATE KEY UPDATE last_activity = NOW()");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->bind_param("ss", $sessionId, $ip_address);
        $stmt->execute();
        $stmt->close();
        
        // Log the message
        $stmt = $conn->prepare("INSERT INTO chat_logs (user_message, bot_response, timestamp, ip_address, user_agent, session_id) 
                               VALUES (?, ?, NOW(), ?, ?, ?)");
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Add session_id column if it doesn't exist
        $conn->query("ALTER TABLE chat_logs ADD COLUMN IF NOT EXISTS session_id VARCHAR(255)");
        
        $stmt->bind_param("sssss", $userMessage, $botResponse, $ip_address, $user_agent, $sessionId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $sessionId;
        
    } catch (Exception $e) {
        error_log("Chat logging error: " . $e->getMessage());
        return null;
    }
}

// Enhanced quick responses with CHO-specific information
function getEnhancedQuickResponse($message) {
    $message = strtolower(trim($message));
    
    $quickResponses = [
        // Greetings
        'hello' => "🏥 Hello! Welcome to San Pablo City Health Office AI Assistant. I can help you with:

• 🐕 Animal bite treatment (ABTC services)
• 📅 Appointments and schedules  
• 🏥 CHO services and programs
• 📍 Locations and contact information
• 💉 Vaccination schedules

How can I assist you today?",

        'hi' => "👋 Hi there! I'm your CHO AI Assistant. What can I help you with today?

Quick options:
• Animal bite emergency 🚨
• ABTC appointment 📅  
• CHO services 🏥
• Location info 📍",

        // Emergency responses
        'emergency' => "🚨 ANIMAL BITE EMERGENCY PROTOCOL:

1. 🚿 WASH wound with clean water for 10-15 minutes
2. 🚫 DO NOT apply ointments
3. 🏥 SEEK IMMEDIATE MEDICAL ATTENTION

📍 ABTC Location: CHO Extension, Brgy. San Jose
📞 Contact: 503-3839
⏰ Hours: 8AM-5PM, Mon-Fri

⚠️ Category 3 wounds (deep/face/hands): Get vaccine within 7 days!",

        'animal bite' => "🐕 ANIMAL BITE INFORMATION:

📋 BITE CATEGORIES:
• Category 1: No bite/saliva → Observation only
• Category 2: Small wounds → Vaccine within 14 days
• Category 3: Deep wounds → EMERGENCY within 7 days

📍 ABTC: CHO Extension, Brgy. San Jose (503-3839)
📅 Day 0: Mon/Tue/Fri mornings (60 slots/day)
💉 Follow-ups: Weekdays afternoons

Need specific category assessment?",

        // Location queries
        'location' => "📍 SAN PABLO CITY HEALTH OFFICE LOCATIONS:

🏥 MAIN CHO:
Ground Floor, City Governance Building
A. Mabini Extension, Brgy. V-A
⏰ Mon-Fri, 8AM-5PM

🐕 ABTC (Animal Bite):
CHO Extension, Brgy. San Jose  
📞 503-3839
⏰ Mon-Fri, 8AM-5PM

Need directions to a specific location?",

        // Hours
        'hours' => "⏰ OPERATING HOURS:

🏥 CHO Main Office: Mon-Fri, 8AM-5PM
🐕 ABTC: Mon-Fri, 8AM-5PM
   • Day 0: Mon/Tue/Fri mornings only
   • Follow-ups: Weekdays afternoons

📅 Appointments: Walk-in only (first come, first serve)",

        // Programs
        'programs' => "🏥 CHO PROGRAMS & SERVICES:

🐕 Animal Bite Treatment Center (ABTC)
🫁 TB-DOTS Program  
🏥 Social Hygiene Program
👥 PWD Services (relatives can assist)
💳 Insurance processing (PhilHealth, etc.)

Which program interests you?",
    ];
    
    // Check for keyword matches
    foreach ($quickResponses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    // Check for barangay-specific queries
    if (strpos($message, 'barangay') !== false || strpos($message, 'rhu') !== false) {
        return "🏥 RURAL HEALTH UNITS BY DISTRICT:

📍 District I-A: RHU at Bagong Pook
📍 District I-B: RHU at Barangay 2D  
📍 District II: RHU at Conception
📍 District III: RHU at Del Remedio
📍 District IV: RHU at Sta. Maria
📍 District V: RHU at Sto. Cristo

Tell me your barangay and I'll direct you to the right RHU!";
    }
    
    return null;
}

// Enhanced API call function
function callEnhancedOpenRouterAPI($message, $conversationHistory = []) {
    $systemPrompt = getEnhancedSystemPrompt();
    
    // Build conversation context
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    
    // Add conversation history if available
    foreach ($conversationHistory as $msg) {
        $messages[] = $msg;
    }
    
    // Add current message
    $messages[] = ['role' => 'user', 'content' => $message];
    
    $data = [
        'model' => AI_MODEL,
        'messages' => $messages,
        'max_tokens' => 600,
        'temperature' => 0.7,
        'top_p' => 0.9
    ];

    $headers = [
        'Authorization: Bearer ' . OPENROUTER_API_KEY,
        'HTTP-Referer: http://localhost/MHO/',
        'X-Title: San Pablo City Health Office Chatbot',
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
        throw new Exception("Connection Error: " . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception("API Error: HTTP " . $httpCode);
    }

    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception("Invalid API response format");
    }

    return trim($responseData['choices'][0]['message']['content']);
}

// Main request handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start session for conversation tracking
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get and validate user message
        $userMessage = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        if (empty($userMessage)) {
            throw new Exception("Please enter a message");
        }

        // Input validation
        $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
        
        if (strlen($userMessage) > 1500) {
            throw new Exception("Message too long. Please keep it under 1500 characters.");
        }

        // Get session ID
        $sessionId = session_id();

        // Check for quick responses first
        $quickResponse = getEnhancedQuickResponse($userMessage);
        
        if ($quickResponse) {
            $botResponse = $quickResponse;
        } else {
            // Get conversation history for context (last 5 messages)
            $conversationHistory = [];
            try {
                $conn = connect_db();
                $stmt = $conn->prepare("SELECT user_message, bot_response FROM chat_logs 
                                       WHERE session_id = ? 
                                       ORDER BY timestamp DESC LIMIT 5");
                $stmt->bind_param("s", $sessionId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    array_unshift($conversationHistory, ['role' => 'user', 'content' => $row['user_message']]);
                    array_unshift($conversationHistory, ['role' => 'assistant', 'content' => $row['bot_response']]);
                }
                
                $stmt->close();
                $conn->close();
            } catch (Exception $e) {
                error_log("History retrieval error: " . $e->getMessage());
            }

            // Call AI API with context
            $botResponse = callEnhancedOpenRouterAPI($userMessage, $conversationHistory);
        }
        
        // Log conversation
        $loggedSessionId = logChatMessage($userMessage, $botResponse, $sessionId);

        // Return response
        echo json_encode([
            'success' => true,
            'message' => $botResponse,
            'session_id' => $loggedSessionId ?? $sessionId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        error_log("CHO Chatbot Error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => "I apologize for the inconvenience. Please try again or contact San Pablo City Health Office directly at:\n\n🏥 Main Office: City Governance Building, A. Mabini Extension\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)\n⏰ Mon-Fri, 8AM-5PM",
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>