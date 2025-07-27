<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>CHO Chatbot</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 30px; }
    #chat { border: 1px solid #ccc; padding: 10px; height: 300px; overflow-y: auto; }
    .message { margin-bottom: 10px; }
    .user { color: blue; }
    .bot { color: green; }
    #inputArea { margin-top: 10px; }
  </style>
</head>
<body>

  <h1>City Health Office Chatbot</h1>

  <div id="chat"></div>

  <div id="inputArea">
    <input id="userInput" type="text" placeholder="Ask a question..." style="width:80%;" />
    <button id="sendBtn">Send</button>
  </div>

  <script>
    const chat = document.getElementById('chat');
    const input = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');

    function appendMessage(text, sender) {
      const div = document.createElement('div');
      div.className = 'message ' + sender;
      div.innerHTML = `<strong>${sender === 'user' ? 'You' : 'Bot'}:</strong> ${text}`;
      chat.appendChild(div);
      chat.scrollTop = chat.scrollHeight;
    }

    async function sendMessage() {
      const msg = input.value.trim();
      if (!msg) return;
      appendMessage(msg, 'user');
      input.value = '';

      try {
        const res = await fetch('chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ message: msg })
        });
        const data = await res.json();
        appendMessage(data.message, 'bot');
      } catch (e) {
        appendMessage('Error contacting chatbot.', 'bot');
      }
    }

    sendBtn.onclick = sendMessage;
    input.addEventListener('keypress', e => {
      if (e.key === 'Enter') sendMessage();
    });
  </script>

</body>
</html>
