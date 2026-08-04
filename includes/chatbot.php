<?php
// includes/chatbot.php - Widget Chatbot Component (Floating Button)
// Jam Operasional: Senin - Sabtu (08:00 - 17:00 WIB) | Minggu TUTUP
// Kontak: WA 0858-1117-7617 | Email: adinda.auliap24@gmail.com
?>

<style>
    .chatbot-button {
        position: fixed;
        bottom: 90px;
        right: 30px;
        z-index: 9999;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .chatbot-button .main-btn {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(102,126,234,0.4);
        transition: all 0.3s ease;
        position: relative;
    }
    .chatbot-button .main-btn:hover { 
        transform: scale(1.1); 
        box-shadow: 0 15px 35px rgba(102,126,234,0.5);
    }
    .chatbot-button .main-btn i { 
        font-size: 28px; 
        color: white; 
    }
    
    .chatbot-notification {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 1.5s infinite;
    }
    
    .status-online {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239,68,68,0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239,68,68,0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239,68,68,0); }
    }
    
    .chatbot-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 400px;
        height: 600px;
        background: white;
        border-radius: 25px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: slideUp 0.3s ease-out;
        font-family: 'Inter', sans-serif;
    }
    .chatbot-window.active { display: flex; }
    
    @keyframes slideUp { 
        from { opacity: 0; transform: translateY(20px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    .chatbot-header { 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        padding: 20px; 
        color: white; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }
    
    .chatbot-avatar { 
        width: 45px; 
        height: 45px; 
        background: rgba(255,255,255,0.2); 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 24px; 
        position: relative;
    }
    
    .chatbot-messages { 
        flex: 1; 
        overflow-y: auto; 
        padding: 20px; 
        background: #f8fafc; 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
    }
    
    .chatbot-messages::-webkit-scrollbar {
        width: 5px;
    }
    .chatbot-messages::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }
    
    .message-chat.bot .message-bubble-chat { 
        background: white; 
        color: #1e293b; 
        border-bottom-left-radius: 4px; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
    }
    
    .message-chat.user .message-bubble-chat { 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        color: white; 
        border-bottom-right-radius: 4px; 
    }
    
    .message-bubble-chat { 
        max-width: 85%; 
        padding: 12px 16px; 
        border-radius: 20px; 
        font-size: 13px; 
        line-height: 1.5;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .message-chat { 
        display: flex; 
        animation: fadeInMessage 0.3s ease-out;
    }
    
    @keyframes fadeInMessage {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .message-chat.bot { justify-content: flex-start; }
    .message-chat.user { justify-content: flex-end; }
    
    .chatbot-input-area { 
        padding: 15px 20px; 
        background: white; 
        border-top: 1px solid #e2e8f0; 
        display: flex; 
        gap: 10px; 
        align-items: center; 
    }
    
    .chatbot-input-area input { 
        flex: 1; 
        border: 1px solid #e2e8f0; 
        padding: 12px 16px; 
        border-radius: 50px; 
        outline: none; 
        font-size: 14px; 
        transition: all 0.3s ease;
    }
    
    .chatbot-input-area input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }
    
    .chatbot-input-area button { 
        width: 45px; 
        height: 45px; 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        border: none; 
        border-radius: 50%; 
        color: white; 
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chatbot-input-area button:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102,126,234,0.4);
    }
    
    .quick-reply-btn { 
        background: #f1f5f9; 
        border: 1px solid #e2e8f0; 
        padding: 8px 16px; 
        border-radius: 50px; 
        font-size: 12px; 
        cursor: pointer; 
        display: inline-block; 
        margin: 5px;
        transition: all 0.3s ease;
    }
    
    .quick-reply-btn:hover { 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
    }
    
    .offline-notice {
        background: #fef2f2;
        border-left: 3px solid #ef4444;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        font-size: 11px;
    }
    
    .typing-indicator-chat {
        display: inline-flex;
        gap: 4px;
        padding: 12px 16px;
        background: white;
        border-radius: 20px;
    }
    
    .typing-indicator-chat span {
        width: 8px;
        height: 8px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typingChat 1.4s infinite;
    }
    
    .typing-indicator-chat span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator-chat span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typingChat {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-10px); opacity: 1; }
    }
    
    .operational-info {
        background: #e0e7ff;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 11px;
        color: #4338ca;
        margin-top: 8px;
        text-align: center;
    }
    
    @media (max-width: 480px) { 
        .chatbot-window { 
            width: calc(100vw - 40px); 
            right: 20px; 
            bottom: 100px; 
            height: 550px; 
        } 
        .chatbot-button { 
            bottom: 20px; 
            right: 20px; 
        }
        .chatbot-button .main-btn { 
            width: 50px; 
            height: 50px; 
        }
        .chatbot-button .main-btn i { 
            font-size: 24px; 
        }
        .message-bubble-chat {
            max-width: 90%;
            font-size: 12px;
        }
        .quick-reply-btn {
            font-size: 10px;
            padding: 6px 12px;
        }
    }
</style>

<div class="chatbot-button" id="chatbotButtonStayNest">
    <div class="main-btn">
        <i class="fas fa-comment-dots"></i>
        <div class="chatbot-notification" id="chatbotNotification" style="display: none;">1</div>
        <span class="status-online" id="statusOnline"></span>
    </div>
</div>

<div class="chatbot-window" id="chatbotWindowStayNest">
    <div class="chatbot-header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="chatbot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h3 style="font-size:16px; font-weight:bold; margin:0">StayNest Support</h3>
                <p style="font-size:11px; opacity:0.8; margin:0" id="statusText">🟢 Online - Siap membantu</p>
            </div>
        </div>
        <button id="chatbotCloseStayNest" style="background:none;border:none;color:white;font-size:28px;cursor:pointer; line-height:1">&times;</button>
    </div>
    <div class="chatbot-messages" id="chatbotMessagesStayNest">
        <div class="message-chat bot">
            <div class="message-bubble-chat">
                <strong>👋 Halo! Saya StayNest Assistant!</strong><br><br>
                Ada yang bisa saya bantu tentang kontrakan? 😊<br><br>
                💬 <strong>Saya bisa bantu:</strong><br>
                • Info harga kontrakan<br>
                • Lokasi properti<br>
                • Fasilitas yang tersedia<br>
                • Cara booking<br>
                • Ketersediaan unit<br>
                • Info unit VIP<br><br>
                <strong>Pilih pertanyaan di bawah ini:</strong>
                <div style="margin-top: 10px;">
                    <div class="quick-reply-btn" data-message="Lihat properti">🏠 Lihat Properti</div>
                    <div class="quick-reply-btn" data-message="Cara booking">📝 Cara Booking</div>
                    <div class="quick-reply-btn" data-message="Harga">💰 Harga</div>
                    <div class="quick-reply-btn" data-message="Lokasi">📍 Lokasi</div>
                    <div class="quick-reply-btn" data-message="Fasilitas">🏠 Fasilitas</div>
                    <div class="quick-reply-btn" data-message="Ketersediaan">✅ Ketersediaan</div>
                    <div class="quick-reply-btn" data-message="VIP">👑 Unit VIP</div>
                    <div class="quick-reply-btn" data-message="Kontak">📞 Kontak</div>
                    <div class="quick-reply-btn" data-message="Jam operasional">⏰ Jam Operasional</div>
                    <div class="quick-reply-btn" data-message="Bantuan">🆘 Bantuan</div>
                </div>
                <div class="operational-info">
                    ⏰ Jam Operasional: Senin - Sabtu (08:00 - 17:00 WIB) | Minggu TUTUP
                </div>
            </div>
        </div>
    </div>
    <div class="chatbot-input-area">
        <input type="text" id="chatbotInputStayNest" placeholder="Ketik pesan Anda disini..." autocomplete="off">
        <button id="chatbotSendStayNest">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
(function() {
    const chatButton = document.getElementById('chatbotButtonStayNest');
    const chatWindow = document.getElementById('chatbotWindowStayNest');
    const chatClose = document.getElementById('chatbotCloseStayNest');
    const chatMessages = document.getElementById('chatbotMessagesStayNest');
    const chatInput = document.getElementById('chatbotInputStayNest');
    const chatSend = document.getElementById('chatbotSendStayNest');
    const notification = document.getElementById('chatbotNotification');
    const statusText = document.getElementById('statusText');
    const statusOnline = document.getElementById('statusOnline');
    
    let isOpen = false;
    let isOperational = true;
    
    const baseUrl = window.location.origin;
    const apiUrl = baseUrl + '/api/chatbot.php';
    
    function getCurrentDayName() {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const today = new Date();
        return days[today.getDay()];
    }
    
    async function checkOperationalStatus() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();
            if (data.operational_hours) {
                isOperational = data.operational_hours.current_status === 'Online';
                const currentDay = getCurrentDayName();
                
                if (isOperational) {
                    if (statusText) statusText.innerHTML = '🟢 Online - Siap membantu (Senin-Sabtu 08:00-17:00)';
                    if (statusOnline) statusOnline.style.background = '#22c55e';
                } else {
                    if (currentDay === 'Minggu') {
                        if (statusText) statusText.innerHTML = '❌ Offline - Hari Minggu TUTUP';
                    } else {
                        if (statusText) statusText.innerHTML = '⏰ Offline - Diluar jam operasional (08:00-17:00)';
                    }
                    if (statusOnline) statusOnline.style.background = '#ef4444';
                }
            }
        } catch (error) {
            console.error('Error checking status:', error);
            if (statusText) statusText.innerHTML = '🟢 Online - Siap membantu';
            if (statusOnline) statusOnline.style.background = '#22c55e';
        }
    }
    
    checkOperationalStatus();
    setInterval(checkOperationalStatus, 300000);
    
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message-chat bot';
        typingDiv.id = 'typingIndicatorChat';
        typingDiv.innerHTML = `
            <div class="typing-indicator-chat">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        chatMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicatorChat');
        if (indicator) indicator.remove();
    }
    
    function addMessage(text, sender) {
        const div = document.createElement('div');
        div.className = `message-chat ${sender}`;
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble-chat';
        let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');
        bubble.innerHTML = formattedText;
        div.appendChild(bubble);
        chatMessages.appendChild(div);
        scrollToBottom();
    }
    
    async function sendMessage(message) {
        if (!message.trim()) return;
        
        addMessage(message, 'user');
        const sentMessage = message;
        chatInput.value = '';
        
        showTypingIndicator();
        
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: sentMessage })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            removeTypingIndicator();
            
            if (data.response) {
                addMessage(data.response, 'bot');
                if (data.is_operational !== undefined) {
                    isOperational = data.is_operational;
                    const currentDay = getCurrentDayName();
                    if (!isOperational && statusText) {
                        if (currentDay === 'Minggu') {
                            statusText.innerHTML = '❌ Offline - Hari Minggu TUTUP';
                        } else {
                            statusText.innerHTML = '⏰ Offline - Diluar jam operasional (08:00-17:00)';
                        }
                        if (statusOnline) statusOnline.style.background = '#ef4444';
                    }
                }
            } else {
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi. 😅', 'bot');
            }
        } catch (error) {
            console.error('Error:', error);
            removeTypingIndicator();
            addMessage('Maaf, terjadi kesalahan koneksi. Silakan coba lagi nanti. 😅\n\n📞 Atau hubungi kami di:\nWA: 0858-1117-7617\nEmail: adinda.auliap24@gmail.com', 'bot');
        }
    }
    
    if (chatButton) {
        chatButton.onclick = (e) => {
            e.stopPropagation();
            chatWindow.classList.toggle('active');
            isOpen = !isOpen;
            if (isOpen) {
                if (notification) notification.style.display = 'none';
                if (chatInput) {
                    setTimeout(() => chatInput.focus(), 100);
                }
                checkOperationalStatus();
                setTimeout(scrollToBottom, 100);
            }
        };
    }
    
    if (chatClose) {
        chatClose.onclick = () => {
            chatWindow.classList.remove('active');
            isOpen = false;
        };
    }
    
    if (chatSend) {
        chatSend.onclick = () => {
            sendMessage(chatInput.value);
        };
    }
    
    if (chatInput) {
        chatInput.onkeypress = (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage(chatInput.value);
            }
        };
    }
    
    function bindQuickReplies() {
        const quickReplyBtns = document.querySelectorAll('.quick-reply-btn');
        quickReplyBtns.forEach(btn => {
            const oldListener = btn._listener;
            if (oldListener) btn.removeEventListener('click', oldListener);
            
            const listener = (e) => {
                e.stopPropagation();
                const msg = btn.getAttribute('data-message');
                sendMessage(msg);
            };
            btn.addEventListener('click', listener);
            btn._listener = listener;
        });
    }
    
    bindQuickReplies();
    
    const observer = new MutationObserver(() => bindQuickReplies());
    observer.observe(chatMessages, { childList: true, subtree: true });
    
    document.addEventListener('click', function(event) {
        if (isOpen && chatWindow && !chatWindow.contains(event.target) && !chatButton.contains(event.target)) {
            chatWindow.classList.remove('active');
            isOpen = false;
        }
    });
    
    if (chatWindow) {
        chatWindow.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    console.log('Chatbot widget loaded successfully!');
})();
</script>
