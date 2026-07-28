<?php
// chatbot.php - Halaman Chatbot StayNest
$page_title = "Chatbot - StayNest Assistant ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayNest Chatbot - Virtual Assistant ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .chat-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            height: 85vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
            text-align: center;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            animation: fadeInUp 0.3s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .bot-message {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .user-message .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .bot-message .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .message-time {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 5px;
            display: block;
        }
        
        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .chat-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        
        .quick-reply {
            display: inline-block;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quick-reply:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
        }
        
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
            background: white;
            border-radius: 20px;
            width: fit-content;
        }
        
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.4;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
        }
        
        ::-webkit-scrollbar {
            width: 5px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        @media (max-width: 768px) {
            .chat-container {
                height: 100vh;
                border-radius: 0;
            }
            
            .message-bubble {
                max-width: 85%;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-robot text-purple-600 text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h2 class="font-bold text-lg">StayNest Assistant</h2>
                        <div class="status-badge">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span>Online - Siap membantu</span>
                        </div>
                    </div>
                </div>
                <button onclick="window.location.href='index.php'" class="text-white hover:bg-white/20 rounded-full p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-sm opacity-90 mt-1">Hi! Saya StayNest Assistant! Ada yang bisa saya bantu? 😊</p>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                <div class="message-bubble">
                    Halo! 👋 Selamat datang di StayNest Support!<br><br>
                    Saya siap membantu Anda mencari kontrakan impian! 🏠<br><br>
                    💬 *Saya bisa bantu:*<br>
                    • Info harga kontrakan<br>
                    • Lokasi properti<br>
                    • Fasilitas yang tersedia<br>
                    • Cara booking<br>
                    • Ketersediaan unit<br>
                    • Dan info lainnya!<br><br>
                    Silakan ketik pertanyaan Anda atau pilih quick reply di bawah! 😊
                    <span class="message-time">Just now</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Replies -->
        <div class="px-5 py-2 bg-gray-50 border-t border-gray-100">
            <div class="flex flex-wrap">
                <span class="quick-reply" data-message="View Properties">📋 Lihat Properti</span>
                <span class="quick-reply" data-message="How to Book">📝 Cara Booking</span>
                <span class="quick-reply" data-message="Price">💰 Harga</span>
                <span class="quick-reply" data-message="Location">📍 Lokasi</span>
                <span class="quick-reply" data-message="Fasilitas">🏠 Fasilitas</span>
                <span class="quick-reply" data-message="Tersedia">✅ Ketersediaan</span>
                <span class="quick-reply" data-message="VIP">👑 Unit VIP</span>
                <span class="quick-reply" data-message="Help">🆘 Bantuan</span>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input-container">
            <div class="input-group">
                <input type="text" id="messageInput" class="chat-input" placeholder="Ketik pesan Anda disini..." autocomplete="off">
                <button id="sendBtn" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <p class="text-xs text-gray-400 text-center mt-3">
                <i class="fas fa-lock"></i> Pesan Anda aman dan terenkripsi
            </p>
        </div>
    </div>
</div>

<script>
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    
    let isTyping = false;
    
    // Fungsi untuk scroll ke bawah
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Fungsi untuk menampilkan typing indicator
    function showTypingIndicator() {
        isTyping = true;
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        chatMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    // Fungsi untuk menghapus typing indicator
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
        isTyping = false;
    }
    
    // Fungsi untuk menambahkan pesan ke chat
    function addMessage(text, isUser) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        // Konversi teks dengan emoji dan bold
        let formattedText = text;
        formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');
        
        messageDiv.innerHTML = `
            <div class="message-bubble">
                ${formattedText}
                <span class="message-time">${timeString}</span>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Fungsi untuk mengirim pesan ke API
    async function sendMessage(message) {
        if (!message.trim()) return;
        
        // Tampilkan pesan user
        addMessage(message, true);
        messageInput.value = '';
        
        // Tampilkan typing indicator
        showTypingIndicator();
        
        try {
            const response = await fetch('api/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            
            // Hapus typing indicator
            removeTypingIndicator();
            
            // Tampilkan respons bot
            addMessage(data.response, false);
        } catch (error) {
            console.error('Error:', error);
            removeTypingIndicator();
            addMessage('Maaf, terjadi kesalahan. Silakan coba lagi nanti. 😅', false);
        }
    }
    
    // Event listener untuk send button
    sendBtn.addEventListener('click', () => {
        const message = messageInput.value.trim();
        if (message) {
            sendMessage(message);
        }
    });
    
    // Event listener untuk enter key
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const message = messageInput.value.trim();
            if (message) {
                sendMessage(message);
            }
        }
    });
    
    // Event listener untuk quick reply (LENGKAP)
    document.querySelectorAll('.quick-reply').forEach(btn => {
        btn.addEventListener('click', () => {
            const message = btn.getAttribute('data-message');
            if (message) {
                // Konversi pesan quick reply ke bahasa Indonesia
                let indonesianMessage = message;
                if (message === 'View Properties') indonesianMessage = 'Lihat properti';
                if (message === 'How to Book') indonesianMessage = 'Cara booking';
                if (message === 'Price') indonesianMessage = 'Harga';
                if (message === 'Location') indonesianMessage = 'Lokasi';
                if (message === 'Fasilitas') indonesianMessage = 'Fasilitas';
                if (message === 'Tersedia') indonesianMessage = 'Ketersediaan unit';
                if (message === 'VIP') indonesianMessage = 'Info unit VIP';
                if (message === 'Help') indonesianMessage = 'Bantuan';
                
                sendMessage(indonesianMessage);
            }
        });
    });
    
    // Auto focus ke input
    messageInput.focus();
</script>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>