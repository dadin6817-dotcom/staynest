<?php
// welcome.php - Halaman Welcome StayNest
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = "Welcome to StayNest - Find Your Cozy Home";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek file database
$database_file = dirname(__FILE__) . '/config/database.php';
if (!file_exists($database_file)) {
    die("<h1>Error</h1><p>File config/database.php tidak ditemukan!</p><p>Buat file tersebut terlebih dahulu.</p>");
}
require_once $database_file;

// Cek file header
$header_file = dirname(__FILE__) . '/includes/header.php';
if (!file_exists($header_file)) {
    die("<h1>Error</h1><p>File includes/header.php tidak ditemukan!</p>");
}
require_once $header_file;
?>

<!-- ========== HERO SECTION ========== -->
<section class="relative bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white py-24">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
            Welcome to <span class="text-yellow-300">StayNest</span> 🏠
        </h1>
        <p class="text-xl text-purple-100 mb-8">
            Find a place you'll love to call home
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="index.php" class="bg-white text-purple-600 px-8 py-4 rounded-full font-bold hover:shadow-xl transition">
                Get Started Now
            </a>
            <a href="properties.php" class="border-2 border-white text-white px-8 py-4 rounded-full font-bold hover:bg-white hover:text-purple-600 transition">
                Explore Properties
            </a>
        </div>
    </div>
</section>

<!-- ========== FEATURES ========== -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose StayNest?</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-gray-50 rounded-2xl p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">100% Verified</h3>
                <p class="text-gray-500 text-sm">All properties checked directly</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Instant Booking</h3>
                <p class="text-gray-500 text-sm">Fast & easy booking process</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">24/7 Support</h3>
                <p class="text-gray-500 text-sm">Customer service ready to help</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA ========== -->
<section class="bg-gradient-to-r from-purple-600 to-purple-800 py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Find Your New Home? 🏠</h2>
        <p class="text-xl text-purple-100 mb-8">Join thousands of happy tenants</p>
        <a href="index.php" class="inline-block bg-white text-purple-600 px-8 py-4 rounded-full font-bold hover:shadow-xl transition">
            Get Started Now
        </a>
    </div>
</section>

<?php 
$footer_file = dirname(__FILE__) . '/includes/footer.php';
if (file_exists($footer_file)) {
    require_once $footer_file;
} else {
    echo "<p style='text-align:center;padding:20px;color:red;'>Footer file not found!</p>";
}
?>