<?php
// properties.php - Halaman Properties (REVISI)
$page_title = "Properties - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/functions.php';  // ← FUNGSI getPropertyImage DI SINI
require_once dirname(__FILE__) . '/includes/header.php';

// ==============================================
// AMBIL DATA PROPERTI
// ==============================================
$properties = [];
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';

try {
    $sql = "SELECT * FROM properties WHERE status = 'available'";
    $params = [];
    
    if ($search) {
        $sql .= " AND (name LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($location) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$location%";
    }
    
    $sql .= " ORDER BY is_vip DESC, id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $properties = [];
}

// Data fallback
if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Kavling Harapan Manunggal Utara, Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0, 'description' => 'Cozy boarding house with modern facilities'],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Jl. Pandawa 15, Tambun Utara, Bekasi', 'total_doors' => 4, 'available_rooms' => 2, 'occupied_rooms' => 2, 'price_per_month' => 700000, 'is_vip' => 1, 'description' => 'Luxury boarding house with VIP facilities'],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Kavling Bumi Mas 2, Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 7, 'occupied_rooms' => 5, 'price_per_month' => 800000, 'is_vip' => 1, 'description' => 'Spacious boarding house with complete facilities']
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
            🏢 Our <span class="gradient-text">Properties</span>
        </h1>
        <p class="text-gray-500 text-lg max-w-2xl mx-auto">
            Discover the best boarding houses curated just for you
        </p>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <form action="properties.php" method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="text" 
                   name="search" 
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search by name or location..." 
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-600">
            <button type="submit" 
                    class="bg-purple-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <?php if ($search || $location): ?>
                <a href="properties.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="mb-4 text-gray-500">
        Found <?php echo count($properties); ?> properties
    </div>

    <?php if (empty($properties)): ?>
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <i class="fas fa-home text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">No Properties Found</h3>
            <p class="text-gray-500">Try adjusting your search filters.</p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($properties as $property): 
                $img = getPropertyImage($property['id']);
                $price = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                <div class="relative h-56 bg-gradient-to-r from-purple-400 to-blue-400">
                    <img src="<?php echo htmlspecialchars($img); ?>" 
                         alt="<?php echo htmlspecialchars($property['name']); ?>"
                         class="w-full h-full object-cover"
                         onerror="this.src='/staynest/assets/images/default-property.jpg'">
                    
                    <?php if ($property['is_vip']): ?>
                        <div class="absolute top-4 left-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">⭐ VIP</div>
                    <?php endif; ?>
                    
                    <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm text-xs font-bold px-4 py-1.5 rounded-full shadow-lg text-purple-600">
                        🛏 <?php echo $property['available_rooms']; ?> Available
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p class="text-gray-500 text-sm mt-1 flex items-center gap-1">
                        <i class="fas fa-map-marker-alt text-purple-500"></i> 
                        <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    
                    <p class="text-gray-600 text-sm mt-2 line-clamp-2">
                        <?php echo htmlspecialchars(substr($property['description'] ?? '', 0, 100)) . '...'; ?>
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
                        
                        <a href="detail.php?id=<?php echo $property['id']; ?>" 
                           class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg transition transform hover:scale-105 flex items-center gap-2">
                            View Details <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>