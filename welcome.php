<?php
// welcome.php - Halaman Sambutan (Khusus Welcome)
$page_title = "Welcome to StayNest - Your Journey Begins Here ✨";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/functions.php';
require_once dirname(__FILE__) . '/includes/header.php';
?>

<!-- HERO SECTION - WELCOME -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
    <!-- Animated Blobs -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-96 h-96 bg-white/10 rounded-full -top-20 -left-20 animate-blob"></div>
        <div class="absolute w-96 h-96 bg-yellow-300/10 rounded-full top-1/2 -right-20 animate-blob animation-delay-2000"></div>
        <div class="absolute w-96 h-96 bg-pink-300/10 rounded-full -bottom-20 left-1/3 animate-blob animation-delay-4000"></div>
    </div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-20 text-center">
        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md rounded-full px-6 py-3 mb-8 border border-white/30 shadow-2xl">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-white font-semibold text-sm">✨ Trusted by 5000+ Tenants</span>
        </div>
        
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white leading-tight mb-6 drop-shadow-2xl">
            Welcome to
            <span class="block bg-gradient-to-r from-yellow-300 via-orange-300 to-yellow-300 bg-clip-text text-transparent animate-pulse">
                StayNest 🏠
            </span>
        </h1>
        
        <p class="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto mb-4 font-light">
            Find a place you'll <span class="font-bold text-yellow-300">love to call home</span>
        </p>
        
        <p class="text-base md:text-lg text-white/70 max-w-2xl mx-auto mb-12">
            Discover cozy spaces that match your lifestyle. Modern, affordable, and totally instagrammable.
        </p>
        
        <!-- CTA -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="index.php" class="group bg-white text-purple-600 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-yellow-300 hover:text-purple-800 transition-all duration-300 transform hover:scale-110 shadow-2xl inline-flex items-center justify-center gap-3">
                <i class="fas fa-rocket group-hover:rotate-12 transition"></i>
                Masuk ke Home
            </a>
            <a href="properties.php" class="group border-2 border-white/50 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-white hover:text-purple-600 transition-all duration-300 transform hover:scale-110 backdrop-blur-sm inline-flex items-center justify-center gap-3">
                <i class="fas fa-search group-hover:scale-125 transition"></i>
                Explore Properties
            </a>
        </div>
    </div>
</section>

<style>
    @keyframes blob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>