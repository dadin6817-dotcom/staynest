<?php
// index.php - Halaman Home Utama
$page_title = "Home - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/functions.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Ambil properti dari database
$properties = [];

try {
    $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY is_vip DESC, id DESC LIMIT 6");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $properties = [];
}

// Fallback data
if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0, 'description' => 'Cozy boarding house'],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Tambun, Bekasi', 'total_doors' => 4, 'available_rooms' => 2, 'occupied_rooms' => 2, 'price_per_month' => 700000, 'is_vip' => 1, 'description' => 'Luxury boarding house'],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 7, 'occupied_rooms' => 5, 'price_per_month' => 800000, 'is_vip' => 1, 'description' => 'Spacious boarding house']
    ];
}
?>

<!-- HERO SECTION - HOME -->
<section class="relative bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-600 text-white py-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-4">
                🏠 Find Your <span class="text-yellow-300">Perfect Stay</span>
            </h1>
            <p class="text-xl md:text-2xl text-purple-100 max-w-2xl mx-auto mb-10">
                Discover cozy spaces that match your lifestyle
            </p>
            
            <!-- Search Form -->
            <form action="properties.php" method="GET" class="max-w-2xl mx-auto flex flex-col md:flex-row gap-3 bg-white rounded-full p-2 shadow-2xl">
                <input type="text" 
                       name="search" 
                       placeholder="Search location... Babelan, Alamanda, VIP" 
                       class="flex-1 px-6 py-3 rounded-full focus:outline-none text-gray-800">
                <button type="submit" 
                        class="bg-purple-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-purple-700 transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </form>
            
            <!-- Popular Locations -->
            <div class="flex flex-wrap gap-2 justify-center mt-6">
                <span class="text-sm text-purple-200">Popular:</span>
                <a href="properties.php?location=Babelan" class="text-sm px-3 py-1 bg-white/20 rounded-full hover:bg-white/30 transition">📍 Babelan</a>
                <a href="properties.php?location=Alamanda" class="text-sm px-3 py-1 bg-white/20 rounded-full hover:bg-white/30 transition">📍 Alamanda</a>
                <a href="properties.php?location=VIP" class="text-sm px-3 py-1 bg-white/20 rounded-full hover:bg-white/30 transition">📍 VIP Village</a>
            </div>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center p-6 bg-gray-50 rounded-2xl hover:bg-purple-50 transition">
                <div class="text-4xl font-bold text-purple-600">5000+</div>
                <div class="text-gray-500 text-sm mt-1">Happy Tenants</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-2xl hover:bg-purple-50 transition">
                <div class="text-4xl font-bold text-purple-600">50+</div>
                <div class="text-gray-500 text-sm mt-1">Properties</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-2xl hover:bg-purple-50 transition">
                <div class="text-4xl font-bold text-purple-600">4.9⭐</div>
                <div class="text-gray-500 text-sm mt-1">Rating</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-2xl hover:bg-purple-50 transition">
                <div class="text-4xl font-bold text-purple-600">1000+</div>
                <div class="text-gray-500 text-sm mt-1">Reviews</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED PROPERTIES SECTION -->
<section id="properties" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <span class="inline-block bg-pink-100 text-pink-600 text-sm font-semibold px-4 py-1 rounded-full mb-2">🔥 Hot Picks</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Featured Properties</h2>
            </div>
            <a href="properties.php" class="text-purple-600 hover:text-purple-800 font-semibold">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($properties as $property): 
                $property_image = getPropertyImage($property['id']);
                $price_display = formatRupiah($property['price_per_month']);
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
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
                        <a href="bookings/book_now.php?property_id=<?php echo $property['id']; ?>" 
                           class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition flex items-center gap-2 shadow-md">
                            Book Now <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>