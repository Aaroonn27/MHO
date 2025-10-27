<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Pablo City Health Office - AI Assistant</title>
    
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