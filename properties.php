<?php
// properties.php - Halaman Properties
$page_title = "Properties - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY id DESC");
    $properties = $stmt->fetchAll();
} catch(Exception $e) {
    $properties = [];
}

if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Babelan, Bekasi', 'price_per_month' => 700000, 'available_rooms' => 1, 'total_doors' => 2],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Tambun, Bekasi', 'price_per_month' => 700000, 'available_rooms' => 1, 'total_doors' => 4],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Babelan, Bekasi', 'price_per_month' => 800000, 'available_rooms' => 6, 'total_doors' => 12],
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">🏢 Our Properties</h1>
    <p class="text-gray-600 mb-8">Find your dream property here.</p>
    
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach($properties as $property): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2">
            <div class="h-48 bg-gradient-to-r from-purple-400 to-blue-400 flex items-center justify-center text-white text-4xl">
                <i class="fas fa-home"></i>
            </div>
            <div class="p-4">
                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($property['name'] ?? 'Property'); ?></h3>
                <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location'] ?? 'Unknown'); ?></p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full"><i class="fas fa-door-open"></i> <?php echo $property['total_doors'] ?? 0; ?> Doors</span>
                    <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full"><i class="fas fa-bed"></i> <?php echo $property['available_rooms'] ?? 0; ?> Available</span>
                </div>
                <p class="text-purple-600 font-bold mt-3">Rp <?php echo number_format($property['price_per_month'] ?? 700000, 0, ',', '.'); ?> / month</p>
                
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
</div>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>