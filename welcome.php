<?php
// welcome.php - Halaman Welcome StayNest
$page_title = "Welcome to StayNest - Find Your Cozy Home ✨";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/functions.php';
require_once dirname(__FILE__) . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
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
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
            <a href="index.php" class="group bg-white text-purple-600 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-yellow-300 hover:text-purple-800 transition-all duration-300 transform hover:scale-110 shadow-2xl inline-flex items-center justify-center gap-3">
                <i class="fas fa-rocket group-hover:rotate-12 transition"></i>
                Get Started Now
            </a>
            <a href="properties.php" class="group border-2 border-white/50 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-white hover:text-purple-600 transition-all duration-300 transform hover:scale-110 backdrop-blur-sm inline-flex items-center justify-center gap-3">
                <i class="fas fa-search group-hover:scale-125 transition"></i>
                Explore Properties
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition">
                <div class="text-4xl font-black text-white mb-1">5000+</div>
                <div class="text-sm text-white/80">Happy Tenants</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition">
                <div class="text-4xl font-black text-white mb-1">50+</div>
                <div class="text-sm text-white/80">Properties</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition">
                <div class="text-4xl font-black text-white mb-1">4.9⭐</div>
                <div class="text-sm text-white/80">Rating</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition">
                <div class="text-4xl font-black text-white mb-1">1000+</div>
                <div class="text-sm text-white/80">Reviews</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="py-24 bg-gradient-to-b from-white to-purple-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm font-bold px-6 py-2 rounded-full mb-4 shadow-lg">
                ✨ WHY CHOOSE US
            </span>
            <h2 class="text-4xl md:text-5xl font-black text-gray-800 mb-4">
                More Than Just a <span class="bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">Place to Stay</span>
            </h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="group bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border-2 border-transparent hover:border-purple-200">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                    <i class="fas fa-shield-alt text-3xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">100% Verified</h3>
                <p class="text-gray-500 leading-relaxed">All properties have been checked directly for your comfort and safety.</p>
            </div>
            
            <div class="group bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border-2 border-transparent hover:border-yellow-200">
                <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                    <i class="fas fa-bolt text-3xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Instant Booking</h3>
                <p class="text-gray-500 leading-relaxed">Fast & easy booking process. Get confirmation within minutes.</p>
            </div>
            
            <div class="group bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border-2 border-transparent hover:border-pink-200">
                <div class="w-20 h-20 bg-gradient-to-br from-pink-400 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                    <i class="fas fa-headset text-3xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">24/7 Support</h3>
                <p class="text-gray-500 leading-relaxed">Our customer service team is ready to help you anytime.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-24 bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute w-96 h-96 bg-white rounded-full -top-40 -left-40 blur-3xl"></div>
        <div class="absolute w-96 h-96 bg-yellow-300 rounded-full -bottom-40 -right-40 blur-3xl"></div>
    </div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-6xl font-black text-white mb-6 drop-shadow-lg">
            Ready to Find Your <span class="text-yellow-300">New Home?</span> 🏠
        </h2>
        <p class="text-xl md:text-2xl text-white/90 mb-12 max-w-2xl mx-auto">
            Join thousands of happy tenants who already found their cozy space with StayNest
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="properties.php" class="group bg-white text-purple-600 px-12 py-5 rounded-2xl font-bold text-lg hover:bg-yellow-300 hover:text-purple-800 transition-all duration-300 transform hover:scale-110 shadow-2xl inline-flex items-center justify-center gap-3">
                <i class="fas fa-search group-hover:rotate-12 transition"></i>
                Find Property Now
            </a>
            <a href="register.php" class="border-2 border-white text-white px-12 py-5 rounded-2xl font-bold text-lg hover:bg-white hover:text-purple-600 transition-all duration-300 transform hover:scale-110 inline-flex items-center justify-center gap-3">
                <i class="fas fa-user-plus"></i>
                Create Account
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