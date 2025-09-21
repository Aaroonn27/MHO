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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c5282 0%, #2d3748 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .quick-actions {
            background: #f7fafc;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .quick-actions h3 {
            color: #2d3748;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quick-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .quick-btn {
            background: #e2e8f0;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-btn:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
        }

        .chat-container {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
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
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user .message-avatar {
            background: #3182ce;
            color: white;
        }

        .bot .message-avatar {
            background: #38a169;
            color: white;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .user .message-content {
            background: #3182ce;
            color: white;
            border-bottom-right-radius: 6px;
        }

        .bot .message-content {
            background: white;
            color: #2d3748;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 6px;
        }

        .timestamp {
            font-size: 0.7rem;
            color: #a0aec0;
            margin-top: 5px;
        }

        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        .input-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        #userInput {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 14px;
            resize: none;
            min-height: 20px;
            max-height: 100px;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        #userInput:focus {
            outline: none;
            border-color: #3182ce;
        }

        #sendBtn {
            background: #3182ce;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        #sendBtn:hover:not(:disabled) {
            background: #2c5282;
            transform: translateY(-1px);
        }

        #sendBtn:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            transform: none;
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            margin-bottom: 20px;
            max-width: 70%;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background: #a0aec0;
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
                transform: scale(1);
            }
        }

        .status-bar {
            background: #f7fafc;
            padding: 8px 20px;
            font-size: 0.8rem;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .connection-status {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #38a169;
        }

        .offline .status-dot {
            background: #e53e3e;
        }

        .emergency-banner {
            background: #fed7d7;
            border: 1px solid #feb2b2;
            color: #c53030;
            padding: 10px 15px;
            margin: 15px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            display: none;
        }

        .emergency-banner.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }

            .chat-container {
                height: 400px;
            }

            .message-content {
                max-width: 85%;
            }

            .quick-buttons {
                justify-content: center;
            }

            .header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 San Pablo City Health Office</h1>
            <p>AI Assistant - Available 24/7 for Health Information</p>
        </div>

        <div class="emergency-banner" id="emergencyBanner">
            <strong>🚨 Emergency:</strong> For immediate animal bite concerns, go directly to ABTC at CHO Extension, Brgy. San Jose or call 503-3839
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
• 🐕 Animal bite treatment (ABTC services)
• 📅 Appointments and schedules
• 🏥 CHO programs and services  
• 📍 Locations and directions
• 💉 Vaccination information

How can I assist you today?
                    <div class="timestamp" id="welcomeTime"></div>
                </div>
            </div>
        </div>

        <div class="typing-indicator" id="typingIndicator">
            <div class="message-avatar">🤖</div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span>CHO Assistant is typing</span>
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
                    this.userInput.style.height = Math.min(this.userInput.scrollHeight, 100) + 'px';
                });

                // Check for emergency keywords
                this.userInput.addEventListener('input', () => {
                    const text = this.userInput.value.toLowerCase();
                    const emergencyKeywords = ['emergency', 'urgent', 'bite', 'bleeding', 'attacked'];
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