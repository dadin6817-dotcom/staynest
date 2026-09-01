<?php
// properties.php - ALL PROPERTIES PAGE (Dengan Gambar Modern + Book Now)
$page_title = "Properties - StayNest | Find Your Cozy Home ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Get filter parameters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 2000000;

// Initialize variables
$properties = [];
$locations = [];

// Try to fetch from database
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT DISTINCT location FROM properties ORDER BY location");
        $locations = $stmt->fetchAll();
    } catch(Exception $e) {
        $locations = [];
    }
    
    try {
        $sql = "SELECT * FROM properties WHERE 1=1";
        $params = [];
        
        if(!empty($location)) {
            $sql .= " AND location LIKE :location";
            $params[':location'] = "%$location%";
        }
        
        if($min_price > 0) {
            $sql .= " AND price_per_month >= :min_price";
            $params[':min_price'] = $min_price;
        }
        
        if($max_price > 0) {
            $sql .= " AND price_per_month <= :max_price";
            $params[':max_price'] = $max_price;
        }
        
        $sql .= " ORDER BY is_vip DESC, id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
    } catch(Exception $e) {
        $properties = [];
    }
}

// ==============================================
// FUNGSI UNTUK MENDAPATKAN GAMBAR PROPERTI
// ==============================================
function getPropertyImage($property_id, $property_name) {
    $property_images = [
        1 => '/staynest/assets/images/babelan-2.jpeg',
        2 => '/staynest/assets/images/alamanda-2.jpeg',
        3 => '/staynest/assets/images/Vip-1.jpeg',
    ];
    
    if (isset($property_images[$property_id])) {
        return $property_images[$property_id];
    }
    
    return '/staynest/assets/images/default-property.jpg';
}

// DATA PROPERTI FALLBACK
if (empty($properties)) {
    $properties = [
        [
            'id' => 1,
            'name' => 'StayNest Vela',
            'location' => 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi',
            'total_doors' => 2,
            'available_rooms' => 1,
            'occupied_rooms' => 1,
            'price_per_month' => 700000,
            'is_vip' => false
        ],
        [
            'id' => 2,
            'name' => 'StayNest Aera',
            'location' => 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi',
            'total_doors' => 4,
            'available_rooms' => 1,
            'occupied_rooms' => 3,
            'price_per_month' => 700000,
            'is_vip' => true
        ],
        [
            'id' => 3,
            'name' => 'StayNest Elora',
            'location' => 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi',
            'total_doors' => 12,
            'available_rooms' => 6,
            'occupied_rooms' => 6,
            'price_per_month' => 800000,
            'is_vip' => true
        ]
    ];
}

if (empty($locations)) {
    $locations = [
        ['location' => 'Babelan'],
        ['location' => 'Alamanda'],
        ['location' => 'Vip'],
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">🏢 Our Properties</h1>
    <p class="text-gray-600 mb-8">Find your dream property here.</p>
    
    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form method="GET" action="properties.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">📍 Location</label>
                <select name="location" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-purple-500">
                    <option value="">All Locations</option>
                    <?php foreach($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                            <?php echo ($location == $loc['location']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc['location']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">💰 Min Price</label>
                <input type="number" name="min_price" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-purple-500" placeholder="0" value="<?php echo $min_price; ?>" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">💰 Max Price</label>
                <input type="number" name="max_price" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-purple-500" placeholder="2,000,000" value="<?php echo $max_price; ?>" min="0">
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition flex-1">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="properties.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition flex-1 text-center">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach($properties as $property): 
            $property_image = getPropertyImage($property['id'], $property['name']);
            $display_price_text = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
        ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2">
            <div class="property-img h-48 bg-gradient-to-r from-purple-400 to-blue-400 relative">
                <img src="<?php echo htmlspecialchars($property_image); ?>" 
                     alt="<?php echo htmlspecialchars($property['name']); ?>"
                     class="w-full h-full object-cover"
                     onerror="this.src='/staynest/assets/images/default-property.jpg'">
                <?php if(isset($property['is_vip']) && $property['is_vip']): ?>
                    <div class="absolute top-3 left-3 bg-yellow-400 text-xs font-bold px-3 py-1 rounded-full">
                        <i class="fas fa-crown mr-1"></i> VIP
                    </div>
                <?php endif; ?>
                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm text-xs font-bold px-3 py-1 rounded-full text-purple-600">
                    <i class="fas fa-bed mr-1"></i> <?php echo $property['available_rooms'] ?? 0; ?> Available
                </div>
            </div>
            <div class="p-4">
                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($property['name'] ?? 'Property'); ?></h3>
                <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location'] ?? 'Unknown'); ?></p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full"><i class="fas fa-door-open"></i> <?php echo $property['total_doors'] ?? 0; ?> Doors</span>
                    <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full"><i class="fas fa-user"></i> <?php echo $property['occupied_rooms'] ?? 0; ?> Occupied</span>
                </div>
                <p class="text-purple-600 font-bold mt-3"><?php echo $display_price_text; ?> / month</p>
                
                <!-- TOMBOL BOOK NOW -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                       class="block text-center mt-3 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-calendar-plus mr-1"></i> Book Now
                    </a>
                <?php else: ?>
                    <a href="/staynest/login.php?redirect=bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                       class="block text-center mt-3 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-lock mr-1"></i> Login to Book
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if(empty($properties)): ?>
        <div class="text-center py-16">
            <i class="fas fa-building fa-5x text-gray-300 mb-4 block"></i>
            <h3 class="text-xl font-semibold text-gray-700">No properties found</h3>
            <p class="text-gray-500">Try adjusting your filters</p>
            <a href="properties.php" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-sync-alt mr-2"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .property-img { height: 200px; overflow: hidden; }
    .property-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
    .property-card:hover .property-img img { transform: scale(1.05); }
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>