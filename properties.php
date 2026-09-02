<?php
// properties.php - Halaman Properties
$page_title = "Properties - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Fungsi untuk mendapatkan gambar properti
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

// Filter parameters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 2000000;

// Ambil data properti dari database
try {
    $sql = "SELECT * FROM properties WHERE 1=1";
    $params = [];
    
    if (!empty($location)) {
        $sql .= " AND location LIKE :location";
        $params[':location'] = "%$location%";
    }
    if ($min_price > 0) {
        $sql .= " AND price_per_month >= :min_price";
        $params[':min_price'] = $min_price;
    }
    if ($max_price > 0) {
        $sql .= " AND price_per_month <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    $sql .= " ORDER BY is_vip DESC, id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
} catch(Exception $e) {
    $properties = [];
}

// Ambil daftar lokasi untuk filter
try {
    $stmt = $pdo->query("SELECT DISTINCT location FROM properties ORDER BY location");
    $locations = $stmt->fetchAll();
} catch(Exception $e) {
    $locations = [];
}

// Fallback data
if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0, 'image_url' => '/staynest/assets/images/babelan-2.jpeg'],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi', 'total_doors' => 4, 'available_rooms' => 1, 'occupied_rooms' => 3, 'price_per_month' => 700000, 'is_vip' => 1, 'image_url' => '/staynest/assets/images/alamanda-2.jpeg'],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 6, 'occupied_rooms' => 6, 'price_per_month' => 800000, 'is_vip' => 1, 'image_url' => '/staynest/assets/images/Vip-1.jpeg']
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
    <!-- Hero Properties -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
            🏢 Find Your <span class="gradient-text">Perfect Space</span>
        </h1>
        <p class="text-gray-500 text-lg max-w-2xl mx-auto">
            Discover the best boarding houses curated just for you
        </p>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-10">
        <form method="GET" action="properties.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">📍 Location</label>
                <select name="location" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                            <?php echo ($location == $loc['location']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc['location']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">💰 Min Price</label>
                <input type="number" name="min_price" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition" placeholder="0" value="<?php echo $min_price; ?>" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">💰 Max Price</label>
                <input type="number" name="max_price" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition" placeholder="2,000,000" value="<?php echo $max_price; ?>" min="0">
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2.5 rounded-xl hover:shadow-lg transition transform hover:scale-105 font-semibold">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="properties.php" class="flex-1 bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-300 transition font-semibold text-center">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Result Count -->
    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500">
            <span class="font-bold text-purple-600"><?php echo count($properties); ?></span> properties found
        </p>
    </div>

    <!-- Properties Grid -->
    <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ($properties as $property): 
            $img = getPropertyImage($property);
            // Format harga untuk Elora (range)
            if ($property['id'] == 3) {
                $price_display = "Rp 1.000.000 - Rp 1.200.000";
            } else {
                $price_display = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
            }
        ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 group">
            <!-- Gambar -->
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
                <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/50 to-transparent"></div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h3>
                <p class="text-gray-500 text-sm mt-1 flex items-center gap-1">
                    <i class="fas fa-map-marker-alt text-purple-500"></i> 
                    <?php echo htmlspecialchars($property['location']); ?>
                </p>
                
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="text-xs bg-purple-100 text-purple-600 px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-door-open mr-1"></i> Total <?php echo $property['total_doors']; ?> Doors
                    </span>
                    <span class="text-xs bg-green-100 text-green-600 px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-user mr-1"></i> <?php echo $property['occupied_rooms']; ?> Occupied
                    </span>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-2xl font-extrabold text-purple-600"><?php echo $price_display; ?></p>
                        <p class="text-xs text-gray-400">/ month</p>
                    </div>
                    
                    <a href="detail.php?id=<?php echo $property['id']; ?>" 
                       class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
                        View Details <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($properties)): ?>
        <div class="text-center py-16">
            <i class="fas fa-building fa-5x text-gray-300 mb-4 block"></i>
            <h3 class="text-xl font-semibold text-gray-700">No properties found</h3>
            <p class="text-gray-500">Try adjusting your filters</p>
            <a href="properties.php" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-xl hover:bg-purple-700 transition">
                <i class="fas fa-sync-alt mr-2"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>