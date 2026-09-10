<?php
// index.php - Homepage StayNest (REVISI)
$page_title = "StayNest - Find Your Cozy Home ✨";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/functions.php';  // ← FUNGSI getPropertyImage DI SINI
require_once dirname(__FILE__) . '/includes/header.php';

// ==============================================
// AMBIL DATA PROPERTI DARI DATABASE
// ==============================================
$featured_properties = [];

try {
    $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY is_vip DESC, id DESC LIMIT 6");
    $featured_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $featured_properties = [];
}

// Data fallback
if (empty($featured_properties)) {
    $featured_properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0, 'description' => 'Cozy boarding house'],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Tambun, Bekasi', 'total_doors' => 4, 'available_rooms' => 2, 'occupied_rooms' => 2, 'price_per_month' => 700000, 'is_vip' => 1, 'description' => 'Luxury boarding house'],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 7, 'occupied_rooms' => 5, 'price_per_month' => 800000, 'is_vip' => 1, 'description' => 'Spacious boarding house']
    ];
}
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
            Find Your <span class="block bg-gradient-to-r from-yellow-300 via-orange-300 to-yellow-300 bg-clip-text text-transparent">Perfect Stay 🏠</span>
        </h1>
        
        <p class="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto mb-12">
            Discover cozy spaces that match your lifestyle
        </p>
        
        <form action="properties.php" method="GET" class="max-w-2xl mx-auto flex flex-col md:flex-row gap-3 bg-white rounded-full p-2 shadow-2xl mb-12">
            <input type="text" name="search" placeholder="Search location..." 
                   class="flex-1 px-6 py-4 rounded-full focus:outline-none text-gray-800">
            <button type="submit" class="bg-purple-600 text-white px-8 py-4 rounded-full font-semibold hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i> Search
            </button>
        </form>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                <div class="text-4xl font-black text-white mb-1">5000+</div>
                <div class="text-sm text-white/80">Happy Tenants</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                <div class="text-4xl font-black text-white mb-1">50+</div>
                <div class="text-sm text-white/80">Properties</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                <div class="text-4xl font-black text-white mb-1">4.9⭐</div>
                <div class="text-sm text-white/80">Rating</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                <div class="text-4xl font-black text-white mb-1">1000+</div>
                <div class="text-sm text-white/80">Reviews</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED PROPERTIES -->
<section id="properties" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm font-bold px-6 py-2 rounded-full mb-4 shadow-lg">
                🔥 Hot Picks
            </span>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                Featured <span class="gradient-text">Properties</span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Handpicked just for you. The most popular choices among our tenants
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($featured_properties as $property): 
                $property_image = getPropertyImage($property['id']);
                $price_display = "Rp " . number_format($property['price_per_month'], 0, ',', '.');
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2 cursor-pointer" 
                 onclick="window.location.href='detail.php?id=<?php echo $property['id']; ?>'">
                <div class="relative h-56 bg-gray-200">
                    <img src="<?php echo htmlspecialchars($property_image); ?>" 
                         alt="<?php echo htmlspecialchars($property['name']); ?>" 
                         class="w-full h-full object-cover"
                         onerror="this.src='/staynest/assets/images/default-property.jpg'">
                    <?php if($property['is_vip']): ?>
                        <div class="absolute top-3 left-3 bg-gradient-to-r from-yellow-400 to-yellow-500 text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            ⭐ VIP
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm text-xs font-bold px-3 py-1 rounded-full shadow-lg text-purple-600">
                        🛏 <?php echo $property['available_rooms']; ?> left
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p class="text-gray-500 text-sm mt-1 flex items-center gap-1">
                        <i class="fas fa-map-marker-alt text-purple-500"></i> 
                        <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="text-xs bg-purple-100 text-purple-600 px-3 py-1 rounded-full font-medium">
                            <i class="fas fa-door-open mr-1"></i> Total <?php echo $property['total_doors']; ?> Doors
                        </span>
                        <span class="text-xs bg-green-100 text-green-600 px-3 py-1 rounded-full font-medium">
                            <i class="fas fa-bed mr-1"></i> <?php echo $property['available_rooms']; ?> Available
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-2xl font-bold text-purple-600"><?php echo $price_display; ?></p>
                            <p class="text-xs text-gray-400">/ month</p>
                        </div>
                        <a href="detail.php?id=<?php echo $property['id']; ?>" 
                           class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition flex items-center gap-2 shadow-md"
                           onclick="event.stopPropagation()">
                            View Details <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="properties.php" class="border-2 border-purple-600 text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-600 hover:text-white transition inline-flex items-center gap-2">
                View All Properties <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-purple-100 text-purple-600 text-sm font-semibold px-4 py-1 rounded-full mb-4">✨ Why Choose Us</span>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                More than just a <span class="gradient-text">place to stay</span>
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">100% Verified</h3>
                <p class="text-gray-500">All properties have been checked directly for your comfort</p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Instant Booking</h3>
                <p class="text-gray-500">Fast & easy booking process, get confirmation within minutes</p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">24/7 Support</h3>
                <p class="text-gray-500">Our customer service team is ready to help you anytime</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-gradient-to-r from-purple-600 to-purple-800 py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to Find Your New Home? 🏠</h2>
        <p class="text-xl text-purple-100 mb-8">Join thousands of happy tenants who already found their cozy space</p>
        <a href="properties.php" class="inline-block bg-white text-purple-600 px-8 py-4 rounded-full font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
            Get Started Now <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</section>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
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