<?php
// properties.php - Halaman Properties
$page_title = "Properties - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

function getPropertyImage($property) {
    $default = '/staynest/assets/images/default-property.jpg';
    if (!empty($property['image_url']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $property['image_url'])) {
        return $property['image_url'];
    }
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $default)) {
        return $default;
    }
    return '';
}

try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY is_vip DESC, id ASC");
    $properties = $stmt->fetchAll();
} catch(Exception $e) {
    $properties = [];
}

if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0, 'image_url' => '/staynest/assets/images/babelan-2.jpeg'],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Tambun, Bekasi', 'total_doors' => 4, 'available_rooms' => 1, 'occupied_rooms' => 3, 'price_per_month' => 700000, 'is_vip' => 1, 'image_url' => '/staynest/assets/images/alamanda-2.jpeg'],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 6, 'occupied_rooms' => 6, 'price_per_month' => 800000, 'is_vip' => 1, 'image_url' => '/staynest/assets/images/Vip-1.jpeg']
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <!-- Hero Properties -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
            🏢 Our <span class="gradient-text">Properties</span>
        </h1>
        <p class="text-gray-500 text-lg max-w-2xl mx-auto">
            Discover the best boarding houses curated just for you
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ($properties as $property): 
            $img = getPropertyImage($property);
            $price = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
        ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-[1.02] group">
            <div class="relative h-56 overflow-hidden bg-gradient-to-r from-purple-400 to-blue-400">
                <?php if (!empty($img)): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" 
                         alt="<?php echo htmlspecialchars($property['name']); ?>"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-white text-5xl\'><i class=\'fas fa-home\'></i></div>';">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-white text-5xl">
                        <i class="fas fa-home"></i>
                    </div>
                <?php endif; ?>
                <?php if ($property['is_vip']): ?>
                    <div class="absolute top-4 left-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">
                        ⭐ VIP
                    </div>
                <?php endif; ?>
                <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm text-xs font-bold px-4 py-1.5 rounded-full shadow-lg text-purple-600">
                    🛏 <?php echo $property['available_rooms']; ?> Available
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/50 to-transparent"></div>
            </div>

            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h3>
                <p class="text-gray-500 text-sm mt-1 flex items-center gap-1">
                    <i class="fas fa-map-marker-alt text-purple-500"></i> 
                    <?php echo htmlspecialchars($property['location']); ?>
                </p>
                
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="text-xs bg-purple-100 text-purple-600 px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-door-open mr-1"></i> <?php echo $property['total_doors']; ?> Doors
                    </span>
                    <span class="text-xs bg-green-100 text-green-600 px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-user mr-1"></i> <?php echo $property['occupied_rooms']; ?> Occupied
                    </span>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-2xl font-extrabold text-purple-600"><?php echo $price; ?></p>
                        <p class="text-xs text-gray-400">/ month</p>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                           class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </a>
                    <?php else: ?>
                        <a href="/staynest/login.php?redirect=bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                           class="bg-gray-200 text-gray-700 px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-300 transition flex items-center gap-2">
                            <i class="fas fa-lock"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>