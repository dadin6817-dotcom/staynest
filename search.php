<?php
// search.php - Search results page
$page_title = "Search Results - StayNest";
require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

$query = $_GET['q'] ?? '';
$location = $_GET['location'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000000;

$properties = [];
$search_performed = false;

if($query || $location || $min_price || $max_price) {
    $search_performed = true;
    
    try {
        $sql = "SELECT * FROM properties WHERE 1=1";
        $params = [];
        
        if($query) {
            $sql .= " AND (name LIKE ? OR location LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }
        
        if($location) {
            $sql .= " AND location LIKE ?";
            $params[] = "%$location%";
        }
        
        if($min_price > 0) {
            $sql .= " AND price_per_month >= ?";
            $params[] = $min_price;
        }
        
        if($max_price < 10000000) {
            $sql .= " AND price_per_month <= ?";
            $params[] = $max_price;
        }
        
        $sql .= " ORDER BY is_vip DESC, price_per_month ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
    } catch(Exception $e) {
        $error_message = "Search error: " . $e->getMessage();
    }
}
?>

<style>
    .search-card {
        transition: all 0.3s ease;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    .search-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    .filter-sidebar {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
    }
</style>

<section class="pt-32 pb-12 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-4">Search Properties 🔍</h1>
            <p class="text-gray-600">Find your perfect home with our smart search</p>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filter Sidebar -->
            <div class="lg:w-80">
                <div class="filter-sidebar">
                    <h3 class="font-bold text-lg mb-4">Filter Pencarian</h3>
                    
                    <form method="GET" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">📍 Kata Kunci</label>
                            <input type="text" 
                                   name="q" 
                                   value="<?php echo htmlspecialchars($query); ?>"
                                   placeholder="Nama atau lokasi..."
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">📍 Lokasi</label>
                            <select name="location" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Semua Lokasi</option>
                                <option value="Babelan" <?php echo $location == 'Babelan' ? 'selected' : ''; ?>>Babelan</option>
                                <option value="Alamanda" <?php echo $location == 'Alamanda' ? 'selected' : ''; ?>>Alamanda</option>
                                <option value="Jakarta" <?php echo $location == 'Jakarta' ? 'selected' : ''; ?>>Jakarta</option>
                                <option value="Depok" <?php echo $location == 'Depok' ? 'selected' : ''; ?>>Depok</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">💰 Harga Minimal</label>
                            <input type="number" 
                                   name="min_price" 
                                   value="<?php echo $min_price; ?>"
                                   placeholder="0"
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">💰 Harga Maksimal</label>
                            <input type="number" 
                                   name="max_price" 
                                   value="<?php echo $max_price; ?>"
                                   placeholder="10.000.000"
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        
                        <button type="submit" class="btn-gradient w-full py-2 text-center">
                            <i class="fas fa-search mr-2"></i> Cari Properti
                        </button>
                        
                        <a href="search.php" class="block text-center text-gray-500 text-sm hover:text-purple-600">
                            Reset Filter
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Search Results -->
            <div class="flex-1">
                <?php if($search_performed): ?>
                    <!-- Results Count -->
                    <div class="bg-white rounded-lg p-4 mb-6">
                        <p class="text-gray-600">
                            <i class="fas fa-chart-line mr-2"></i>
                            Ditemukan <strong class="text-purple-600"><?php echo count($properties); ?></strong> properti
                        </p>
                    </div>
                    
                    <!-- Results Grid -->
                    <?php if(count($properties) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach($properties as $property): ?>
                            <div class="search-card fade-in-up">
                                <div class="flex flex-col md:flex-row">
                                    <div class="md:w-64 h-48 md:h-auto relative">
                                        <img src="<?php echo htmlspecialchars($property['image_url'] ?? '/api/placeholder/400/300'); ?>" 
                                             alt="<?php echo htmlspecialchars($property['name']); ?>"
                                             class="w-full h-full object-cover">
                                        <?php if($property['is_vip']): ?>
                                            <div class="absolute top-2 left-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-2 py-1 rounded text-xs font-bold">
                                                VIP
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-1 p-6">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($property['name']); ?></h3>
                                                <p class="text-gray-500 text-sm mb-2">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location']); ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-purple-600">
                                                    Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?>
                                                </p>
                                                <p class="text-gray-500 text-sm">/bulan</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-2 mb-3">
                                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                                                Total <?php echo $property['total_doors']; ?> Pintu
                                            </span>
                                            <span class="<?php echo $property['available_rooms'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> text-xs px-2 py-1 rounded">
                                                <?php echo $property['available_rooms']; ?> Kamar Kosong
                                            </span>
                                        </div>
                                        
                                        <div class="flex gap-3">
                                            <a href="/staynest/detail.php?id=<?php echo $property['id']; ?>" 
                                               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                                Lihat Detail
                                            </a>
                                            <?php if($property['available_rooms'] > 0): ?>
                                                <a href="/staynest/bookings/book.php?id=<?php echo $property['id']; ?>" 
                                                   class="border-2 border-purple-600 text-purple-600 px-4 py-2 rounded-lg hover:bg-purple-600 hover:text-white transition">
                                                    Booking Now
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl p-12 text-center">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-search text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">Tidak Ada Properti Ditemukan 😢</h3>
                            <p class="text-gray-500 mb-6">Coba dengan kata kunci atau filter yang berbeda</p>
                            <a href="search.php" class="btn-gradient inline-block">
                                <i class="fas fa-sync-alt mr-2"></i> Reset Pencarian
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Initial State - No Search Yet -->
                    <div class="bg-white rounded-2xl p-12 text-center">
                        <div class="w-24 h-24 bg-gradient-to-r from-purple-100 to-pink-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-home text-4xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">Mulai Cari Properti Impianmu 🏠</h3>
                        <p class="text-gray-500 mb-6">Gunakan filter di samping untuk menemukan properti yang sesuai dengan kebutuhanmu</p>
                        <div class="flex gap-3 justify-center">
                            <div class="bg-gray-100 px-4 py-2 rounded-full text-sm">✨ Filter by location</div>
                            <div class="bg-gray-100 px-4 py-2 rounded-full text-sm">💰 Filter by price</div>
                            <div class="bg-gray-100 px-4 py-2 rounded-full text-sm">🔍 Search by keyword</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>