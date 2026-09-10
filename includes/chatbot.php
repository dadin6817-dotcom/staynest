<?php
// includes/chatbot.php - Chatbot Component
?>
<!-- ========================================== -->
<!-- CHATBOT WIDGET -->
<!-- ========================================== -->
<div id="chatbotContainer" class="fixed bottom-24 right-6 z-50">
    <!-- Tombol Chatbot -->
    <button id="chatbotToggle" class="w-16 h-16 rounded-full shadow-2xl hover:shadow-3xl transition transform hover:scale-110 flex items-center justify-center relative" 
            style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; cursor: pointer;">
        <span class="chatbot-pulse"></span>
        <i class="fas fa-robot text-white text-2xl" id="chatbotIcon"></i>
    </button>
    
    <!-- Chatbot Window -->
    <div id="chatbotWindow" class="hidden absolute bottom-20 right-0 bg-white rounded-2xl shadow-2xl w-96 max-w-[calc(100vw-3rem)] overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-robot text-white text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-white font-bold">StayNest Assistant</h3>
                <p class="text-purple-200 text-xs flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                    Online - Siap membantu
                </p>
            </div>
            <button id="chatbotClose" class="text-white/80 hover:text-white transition" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div id="chatMessages" class="p-4 h-80 overflow-y-auto bg-gray-50">
            <!-- Bot Message -->
            <div class="flex gap-2 mb-4">
                <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-purple-800 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-xs"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm max-w-[80%]">
                    <p class="text-sm text-gray-700">Halo! 👋 Saya StayNest Assistant. Ada yang bisa saya bantu?</p>
                </div>
            </div>
            
            <!-- Quick Replies -->
            <div class="flex flex-wrap gap-2 mb-4 ml-10" id="quickReplies">
                <button class="quick-reply bg-white border border-purple-200 text-purple-600 text-xs px-3 py-1.5 rounded-full hover:bg-purple-50 transition" data-message="Cara booking?">
                    📝 Cara booking?
                </button>
                <button class="quick-reply bg-white border border-purple-200 text-purple-600 text-xs px-3 py-1.5 rounded-full hover:bg-purple-50 transition" data-message="Lihat properti">
                    🏠 Lihat properti
                </button>
                <button class="quick-reply bg-white border border-purple-200 text-purple-600 text-xs px-3 py-1.5 rounded-full hover:bg-purple-50 transition" data-message="Harga sewa">
                    💰 Harga sewa
                </button>
                <button class="quick-reply bg-white border border-purple-200 text-purple-600 text-xs px-3 py-1.5 rounded-full hover:bg-purple-50 transition" data-message="Kontak admin">
                    📞 Kontak admin
                </button>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="p-3 bg-white border-t border-gray-200">
            <div class="flex gap-2">
                <input type="text" 
                       id="chatInput" 
                       placeholder="Ketik pesan..." 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-600 text-sm">
                <button id="chatSend" class="w-10 h-10 bg-gradient-to-r from-purple-600 to-purple-800 rounded-full flex items-center justify-center text-white hover:shadow-lg transition" style="border: none; cursor: pointer;">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .chatbot-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.4);
        animation: chatbotPulse 2s ease-in-out infinite;
    }
    
    @keyframes chatbotPulse {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.2); opacity: 0.1; }
    }
    
    #chatbotWindow {
        animation: slideUpChat 0.3s ease-out;
    }
    
    @keyframes slideUpChat {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Custom Scrollbar */
    #chatMessages::-webkit-scrollbar {
        width: 6px;
    }
    #chatMessages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    #chatMessages::-webkit-scrollbar-thumb {
        background: #c7d2fe;
        border-radius: 10px;
    }
    #chatMessages::-webkit-scrollbar-thumb:hover {
        background: #a5b4fc;
    }
</style>

<script>
// ==========================================
// CHATBOT SCRIPT
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chatbot Loaded');
    
    var chatbotToggle = document.getElementById('chatbotToggle');
    var chatbotWindow = document.getElementById('chatbotWindow');
    var chatbotClose = document.getElementById('chatbotClose');
    var chatInput = document.getElementById('chatInput');
    var chatSend = document.getElementById('chatSend');
    var chatMessages = document.getElementById('chatMessages');
    var chatbotIcon = document.getElementById('chatbotIcon');
    var quickReplies = document.querySelectorAll('.quick-reply');
    
    // Toggle Chatbot
    if (chatbotToggle) {
        chatbotToggle.addEventListener('click', function() {
            chatbotWindow.classList.toggle('hidden');
            if (!chatbotWindow.classList.contains('hidden')) {
                if (chatbotIcon) {
                    chatbotIcon.className = 'fas fa-times text-white text-2xl';
                }
                chatInput.focus();
            } else {
                if (chatbotIcon) {
                    chatbotIcon.className = 'fas fa-robot text-white text-2xl';
                }
            }
        });
    }
    
    // Close Chatbot
    if (chatbotClose) {
        chatbotClose.addEventListener('click', function() {
            chatbotWindow.classList.add('hidden');
            if (chatbotIcon) {
                chatbotIcon.className = 'fas fa-robot text-white text-2xl';
            }
        });
    }
    
    // Send Message
    function sendMessage(message) {
        if (!message.trim()) return;
        
        // Add user message
        var userMessage = document.createElement('div');
        userMessage.className = 'flex gap-2 mb-4 justify-end';
        userMessage.innerHTML = `
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-2xl rounded-tr-none p-3 shadow-sm max-w-[80%]">
                <p class="text-sm">${escapeHtml(message)}</p>
            </div>
            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-gray-600 text-xs"></i>
            </div>
        `;
        chatMessages.appendChild(userMessage);
        
        // Clear input
        chatInput.value = '';
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Show typing indicator
        var typingIndicator = document.createElement('div');
        typingIndicator.className = 'flex gap-2 mb-4 typing-indicator';
        typingIndicator.innerHTML = `
            <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-purple-800 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-xs"></i>
            </div>
            <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm">
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                </div>
            </div>
        `;
        chatMessages.appendChild(typingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Get bot response
        setTimeout(function() {
            typingIndicator.remove();
            var botResponse = getBotResponse(message);
            
            var botMessage = document.createElement('div');
            botMessage.className = 'flex gap-2 mb-4';
            botMessage.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-purple-800 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-xs"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm max-w-[80%]">
                    <p class="text-sm text-gray-700">${botResponse}</p>
                </div>
            `;
            chatMessages.appendChild(botMessage);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 1000);
    }
    
    // Escape HTML
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Bot Response Logic
    function getBotResponse(message) {
        var msg = message.toLowerCase();
        
        // Booking
        if (msg.includes('booking') || msg.includes('pesan') || msg.includes('sewa')) {
            return '📝 Untuk booking:<br>1. Pilih properti di halaman <a href="properties.php" class="text-purple-600 underline">Properties</a><br>2. Klik "View Details"<br>3. Isi tanggal check-in & check-out<br>4. Klik "Book Now"<br>5. Upload bukti pembayaran<br><br>Ada yang ingin ditanyakan lagi? 😊';
        }
        
        // Properti
        if (msg.includes('properti') || msg.includes('property') || msg.includes('lihat')) {
            return '🏠 Kami memiliki 3 properti unggulan:<br>• <strong>StayNest Vela</strong> - Babelan<br>• <strong>StayNest Aera</strong> - Tambun<br>• <strong>StayNest Elora</strong> - Babelan<br><br>Lihat semua di <a href="properties.php" class="text-purple-600 underline">halaman Properties</a> ya!';
        }
        
        // Harga
        if (msg.includes('harga') || msg.includes('price') || msg.includes('biaya') || msg.includes('sewa')) {
            return '💰 Harga sewa mulai dari:<br>• Rp 700.000/bulan (Standard)<br>• Rp 800.000/bulan (VIP)<br><br>Harga sudah termasuk:<br>✅ WiFi<br>✅ Listrik<br>✅ Air<br>✅ Keamanan 24 jam';
        }
        
        // Kontak
        if (msg.includes('kontak') || msg.includes('admin') || msg.includes('hubungi') || msg.includes('telp')) {
            return '📞 Hubungi kami:<br>• Email: info@staynest.com<br>• WhatsApp: +62 812 3456 7890<br>• Instagram: @staynest.id<br><br>Kami siap membantu 24/7! 😊';
        }
        
        // Pembayaran
        if (msg.includes('bayar') || msg.includes('payment') || msg.includes('transfer')) {
            return '💳 Metode pembayaran:<br>• Transfer Bank: BCA, BRI, BNI, Mandiri, BSI<br>• Upload bukti transfer di halaman Payment<br>• Konfirmasi otomatis dalam 1x24 jam<br><br>Virtual Account tersedia setelah booking!';
        }
        
        // Fasilitas
        if (msg.includes('fasilitas') || msg.includes('fasilitas') || msg.includes('kamar')) {
            return '🏠 Fasilitas kami:<br>✅ AC & WiFi<br>✅ Kamar mandi dalam<br>✅ Kasur & lemari<br>✅ Parkir luas<br>✅ CCTV 24 jam<br>✅ Dapur bersama<br>✅ Laundry';
        }
        
        // Terima kasih
        if (msg.includes('terima kasih') || msg.includes('makasih') || msg.includes('thanks')) {
            return 'Sama-sama! 😊 Senang bisa membantu. Jika ada pertanyaan lain, jangan ragu untuk bertanya ya! 🙏';
        }
        
        // Halo
        if (msg.includes('halo') || msg.includes('hai') || msg.includes('hi') || msg.includes('hello')) {
            return 'Halo! 👋 Selamat datang di StayNest! Ada yang bisa saya bantu?<br><br>Coba tanya:<br>• "Cara booking?"<br>• "Lihat properti"<br>• "Harga sewa"<br>• "Kontak admin"';
        }
        
        // Default
        return 'Terima kasih atas pertanyaannya! 🙏<br><br>Untuk informasi lebih lanjut, silakan:<br>• Lihat <a href="properties.php" class="text-purple-600 underline">halaman Properties</a><br>• Hubungi admin: info@staynest.com<br>• WhatsApp: +62 812 3456 7890<br><br>Ada yang bisa saya bantu lagi? 😊';
    }
    
    // Send on Enter
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage(this.value);
            }
        });
    }
    
    // Send on Button Click
    if (chatSend) {
        chatSend.addEventListener('click', function() {
            sendMessage(chatInput.value);
        });
    }
    
    // Quick Replies
    quickReplies.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var message = this.getAttribute('data-message');
            sendMessage(message);
        });
    });
    
    console.log('Chatbot Ready!');
});
</script>