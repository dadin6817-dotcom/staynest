<?php
// includes/footer.php - Footer Global dengan Chatbot
// Tidak perlu require database di sini karena sudah di header.php
?>

<footer class="bg-gray-900 text-white pt-16 pb-8 mt-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <!-- Brand Column -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                        <i class="fas fa-home text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold">StayNest</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Find your perfect place to call home. Cozy spaces that match your lifestyle.
                </p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="/index.php" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-purple-500"></i> Home</a></li>
                    <li><a href="/properties.php" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-purple-500"></i> Properties</a></li>
                    <li><a href="/bookings/my_bookings.php" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-purple-500"></i> My Bookings</a></li>
                    <li><a href="/admin/login.php" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-purple-500"></i> Admin Panel</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h3 class="text-lg font-bold mb-4">Contact Us</h3>
                <ul class="space-y-3 text-gray-400">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-purple-500 mt-1"></i>
                        <span>Bekasi, Indonesia</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-phone text-purple-500"></i>
                        <span>+62 858 1117 7617</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-envelope text-purple-500"></i>
                        <span>adinda.auliap24@gmail.com</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-clock text-purple-500"></i>
                        <span>24/7 Customer Support</span>
                    </li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div>
                <h3 class="text-lg font-bold mb-4">Stay Updated</h3>
                <p class="text-gray-400 text-sm mb-3">Get the latest property updates and offers</p>
                <form class="flex flex-col gap-3" id="newsletterForm">
                    <input type="email" placeholder="Your email address" class="px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:outline-none focus:border-purple-500">
                    <button type="submit" class="gradient-bg text-white px-4 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                        Subscribe <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; 2026 StayNest. All rights reserved. Designed with <i class="fas fa-heart text-red-500"></i> for Gen Z</p>
        </div>
    </div>
</footer>

<script>
    // Newsletter form handler
    var newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var emailInput = this.querySelector('input[type="email"]');
            var email = emailInput ? emailInput.value : '';
            if (email) {
                alert('🎉 Thank you for subscribing! You\'ll receive updates from StayNest.');
                this.reset();
            }
        });
    }
</script>

<!-- Chatbot Component -->
<?php 
// Cek apakah file chatbot.php ada sebelum di-include
$chatbot_path = dirname(__FILE__) . '/chatbot.php';
if (file_exists($chatbot_path)) {
    include_once $chatbot_path;
}
?>
</body>
</html>
