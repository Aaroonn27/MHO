<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Pablo City Health Office - AI Assistant</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .header {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .back-home-btn {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-50%) translateX(-3px);
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            opacity: 0.95;
            font-size: 1rem;
        }

        .quick-actions {
            background: #e8f5e9;
            padding: 20px;
            border-bottom: 2px solid #c8e6c9;
        }

        .quick-actions h3 {
            color: #2d5f3f;
            font-size: 0.95rem;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .quick-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .quick-btn {
            background: white;
            border: 2px solid #a5d6a7;
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #2d5f3f;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .quick-btn:hover {
            background: #2d5f3f;
            color: white;
            border-color: #2d5f3f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(45, 95, 63, 0.2);
        }

        .chat-container {
            height: 500px;
            overflow-y: auto;
            padding: 25px;
            background: linear-gradient(to bottom, #fafafa 0%, #f5f5f5 100%);
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .user .message-avatar {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
        }

        .bot .message-avatar {
            background: linear-gradient(135deg, #4a8f5f 0%, #5aa070 100%);
            color: white;
        }

        .message-content {
            max-width: 70%;
            padding: 14px 18px;
            border-radius: 18px;
            line-height: 1.6;
            white-space: pre-wrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .user .message-content {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            border-bottom-right-radius: 6px;
        }

        .bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 6px;
        }

        .timestamp {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 6px;
        }

        .bot .timestamp {
            color: #999;
        }

        .input-area {
            padding: 20px;
            background: white;
            border-top: 2px solid #e9ecef;
        }

        .input-container {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        #userInput {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 15px;
            resize: none;
            min-height: 20px;
            max-height: 120px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        #userInput:focus {
            outline: none;
            border-color: #4a8f5f;
            box-shadow: 0 0 0 3px rgba(74, 143, 95, 0.1);
            background: white;
        }

        #sendBtn {
            background: linear-gradient(135deg, #2d5f3f 0%, #3d7f4f 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(45, 95, 63, 0.3);
            font-size: 15px;
        }

        #sendBtn:hover:not(:disabled) {
            background: linear-gradient(135deg, #3d7f4f 0%, #2d5f3f 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 95, 63, 0.4);
        }

        #sendBtn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 18px;
            margin-bottom: 20px;
            max-width: 70%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .typing-dots {
            display: flex;
            gap: 5px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #4a8f5f;
            border-radius: 50%;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingAnimation {
            0%, 80%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            40% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        .status-bar {
            background: #f8fdf9;
            padding: 12px 20px;
            font-size: 0.85rem;
            color: #666;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .connection-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4a8f5f;
            box-shadow: 0 0 8px rgba(74, 143, 95, 0.6);
            animation: statusPulse 2s infinite;
        }

        @keyframes statusPulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        .offline .status-dot {
            background: #e53e3e;
            box-shadow: 0 0 8px rgba(229, 62, 62, 0.6);
        }

        .emergency-banner {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #fca5a5;
            border-left: 4px solid #dc3545;
            color: #c53030;
            padding: 15px 20px;
            margin: 20px 25px;
            border-radius: 10px;
            font-size: 0.95rem;
            display: none;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
        }

        .emergency-banner.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        .emergency-banner strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1.05rem;
        }

        /* Scrollbar styling */
        .chat-container::-webkit-scrollbar {
            width: 8px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: #4a8f5f;
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: #2d5f3f;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                margin: 0;
                border-radius: 10px;
            }

            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .header p {
                font-size: 0.9rem;
            }

            .back-home-btn {
                position: static;
                transform: none;
                margin-bottom: 15px;
                width: fit-content;
            }

            .back-home-btn:hover {
                transform: translateX(-3px);
            }

            .chat-container {
                height: 450px;
                padding: 15px;
            }

            .message-content {
                max-width: 85%;
                padding: 12px 15px;
            }

            .message-avatar {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .quick-actions {
                padding: 15px;
            }

            .quick-buttons {
                justify-content: center;
                gap: 8px;
            }

            .quick-btn {
                font-size: 0.8rem;
                padding: 8px 12px;
            }

            .input-area {
                padding: 15px;
            }

            #userInput {
                font-size: 14px;
                padding: 12px 16px;
            }

            #sendBtn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.3rem;
            }

            .quick-btn {
                font-size: 0.75rem;
                padding: 6px 10px;
            }

            .message-content {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php" class="back-home-btn">
                <span>←</span> Back to Home
            </a>
            <h1>🏥 San Pablo City Health Office</h1>
            <p>AI Assistant - Available 24/7 for Health Information</p>
        </div>

        <div class="emergency-banner" id="emergencyBanner">
            <strong>🚨 Emergency Alert</strong>
            For immediate animal bite concerns, go directly to ABTC at CHO Extension, Brgy. San Jose or call 503-3839
        </div>

        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="quick-buttons">
                <button class="quick-btn" onclick="sendQuickMessage('animal bite emergency')">🚨 Animal Bite Emergency</button>
                <button class="quick-btn" onclick="sendQuickMessage('ABTC schedule')">📅 ABTC Schedule</button>
                <button class="quick-btn" onclick="sendQuickMessage('CHO location')">📍 CHO Location</button>
                <button class="quick-btn" onclick="sendQuickMessage('hours')">⏰ Office Hours</button>
                <button class="quick-btn" onclick="sendQuickMessage('programs')">🏥 Programs</button>
                <button class="quick-btn" onclick="sendQuickMessage('PhilHealth')">💳 PhilHealth</button>
            </div>
        </div>

        <div class="chat-container" id="chatContainer">
            <div class="message bot">
                <div class="message-avatar">🤖</div>
                <div class="message-content">
🏥 Welcome to San Pablo City Health Office AI Assistant!

I'm here to help you with:
- 🐕 Animal bite treatment (ABTC services)
- 📅 Appointments and schedules
- 🏥 CHO programs and services  
- 📍 Locations and directions
- 💉 Vaccination information

How can I assist you today?
                    <div class="timestamp" id="welcomeTime"></div>
                </div>
            </div>
        </div>

        <div class="typing-indicator" id="typingIndicator">
            <div class="message-avatar">🤖</div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #666; font-weight: 500;">CHO Assistant is typing</span>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>

        <div class="input-area">
            <div class="input-container">
                <div class="input-wrapper">
                    <textarea id="userInput" placeholder="Ask about animal bites, appointments, services..." rows="1"></textarea>
                </div>
                <button id="sendBtn">Send</button>
            </div>
        </div>

        <div class="status-bar">
            <div class="connection-status">
                <div class="status-dot"></div>
                <span>Connected to CHO Assistant</span>
            </div>
            <div id="sessionInfo">Session: Active</div>
        </div>
    </div>

    <script>
        class CHOChatbot {
            constructor() {
                this.chatContainer = document.getElementById('chatContainer');
                this.userInput = document.getElementById('userInput');
                this.sendBtn = document.getElementById('sendBtn');
                this.typingIndicator = document.getElementById('typingIndicator');
                this.emergencyBanner = document.getElementById('emergencyBanner');
                this.sessionId = null;
                this.messageCount = 0;

                this.init();
            }

            init() {
                // Set welcome timestamp
                document.getElementById('welcomeTime').textContent = this.formatTime(new Date());

                // Event listeners
                this.sendBtn.addEventListener('click', () => this.sendMessage());
                this.userInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });

                // Auto-resize textarea
                this.userInput.addEventListener('input', () => {
                    this.userInput.style.height = 'auto';
                    this.userInput.style.height = Math.min(this.userInput.scrollHeight, 120) + 'px';
                });

                // Check for emergency keywords
                this.userInput.addEventListener('input', () => {
                    const text = this.userInput.value.toLowerCase();
                    const emergencyKeywords = ['emergency', 'urgent', 'bite', 'bleeding', 'attacked', 'help'];
                    const hasEmergency = emergencyKeywords.some(keyword => text.includes(keyword));
                    
                    if (hasEmergency) {
                        this.emergencyBanner.classList.add('show');
                    } else {
                        this.emergencyBanner.classList.remove('show');
                    }
                });
            }

            formatTime(date) {
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            appendMessage(content, sender, timestamp = null) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.textContent = sender === 'user' ? '👤' : '🤖';

                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                contentDiv.textContent = content;

                if (timestamp) {
                    const timestampDiv = document.createElement('div');
                    timestampDiv.className = 'timestamp';
                    timestampDiv.textContent = this.formatTime(timestamp);
                    contentDiv.appendChild(timestampDiv);
                }

                messageDiv.appendChild(avatar);
                messageDiv.appendChild(contentDiv);

                this.chatContainer.appendChild(messageDiv);
                this.scrollToBottom();
                this.messageCount++;

                // Hide emergency banner after first bot response
                if (sender === 'bot' && this.messageCount > 1) {
                    setTimeout(() => {
                        this.emergencyBanner.classList.remove('show');
                    }, 3000);
                }
            }

            showTyping() {
                this.typingIndicator.style.display = 'flex';
                this.scrollToBottom();
            }

            hideTyping() {
                this.typingIndicator.style.display = 'none';
            }

            scrollToBottom() {
                this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
            }

            async sendMessage(message = null) {
                const text = message || this.userInput.value.trim();
                if (!text) return;

                // Clear input
                if (!message) {
                    this.userInput.value = '';
                    this.userInput.style.height = 'auto';
                }

                // Add user message
                this.appendMessage(text, 'user', new Date());

                // Show typing indicator
                this.showTyping();
                this.sendBtn.disabled = true;
                this.sendBtn.textContent = 'Sending...';

                try {
                    const response = await fetch('enhanced_chatbot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            message: text,
                            session_id: this.sessionId
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        // Update session ID
                        if (data.session_id) {
                            this.sessionId = data.session_id;
                            document.getElementById('sessionInfo').textContent = `Session: ${data.session_id.substring(0, 8)}...`;
                        }

                        // Add bot response
                        this.appendMessage(data.message, 'bot', new Date());
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }

                } catch (error) {
                    console.error('Chat error:', error);
                    this.appendMessage(
                        `❌ Connection error. Please try again or contact CHO directly:\n\n🏥 Main Office: City Governance Building, A. Mabini Extension, Brgy. V-A\n🐕 ABTC: CHO Extension, Brgy. San Jose (503-3839)\n⏰ Mon-Fri, 8AM-5PM`,
                        'bot',
                        new Date()
                    );
                    
                    // Update connection status
                    document.querySelector('.connection-status').innerHTML = `
                        <div class="status-dot" style="background: #e53e3e;"></div>
                        <span>Connection Error</span>
                    `;
                } finally {
                    // Hide typing and re-enable input
                    this.hideTyping();
                    this.sendBtn.disabled = false;
                    this.sendBtn.textContent = 'Send';
                    this.userInput.focus();
                }
            }
        }

        // Initialize chatbot
        const chatbot = new CHOChatbot();

        // Quick message function
        function sendQuickMessage(message) {
            chatbot.sendMessage(message);
        }

        // Add some CSS animations and enhanced UX
        document.addEventListener('DOMContentLoaded', function() {
            // Fade in animation
            document.querySelector('.container').style.opacity = '0';
            document.querySelector('.container').style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                document.querySelector('.container').style.transition = 'all 0.6s ease';
                document.querySelector('.container').style.opacity = '1';
                document.querySelector('.container').style.transform = 'translateY(0)';
            }, 100);

            // Focus input after load
            setTimeout(() => {
                document.getElementById('userInput').focus();
            }, 1000);
        });

        // Handle visibility change to show user if tab is inactive
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                document.title = '💬 CHO Assistant - New Messages';
            } else {
                document.title = 'San Pablo City Health Office - AI Assistant';
            }
        });
    </script>
</body>
</html>