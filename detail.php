<?php
// detail.php - Halaman Detail Properti
$page_title = "Property Detail - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
} catch(Exception $e) {}

if (!$property) {
    header('Location: properties.php');
    exit;
}

// Fungsi gambar
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

$img = getPropertyImage($property);
$price_display = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
?>

<div class="max-w-6xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <!-- Back Button -->
    <a href="properties.php" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to Properties
    </a>

    <!-- VIP Badge -->
    <?php if ($property['is_vip']): ?>
        <div class="mb-4">
            <span class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-bold px-4 py-1.5 rounded-full text-sm shadow-lg">
                ⭐ VIP PROPERTY
            </span>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Left: Image -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <?php if (!empty($img)): ?>
                <img src="<?php echo htmlspecialchars($img); ?>" 
                     alt="<?php echo htmlspecialchars($property['name']); ?>"
                     class="w-full h-96 object-cover"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'w-full h-96 flex items-center justify-center text-white text-6xl bg-gradient-to-r from-purple-400 to-blue-400\'><i class=\'fas fa-home\'></i></div>';">
            <?php else: ?>
                <div class="w-full h-96 flex items-center justify-center text-white text-6xl bg-gradient-to-r from-purple-400 to-blue-400">
                    <i class="fas fa-home"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Info -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h1>
            <p class="text-gray-500 mt-2 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-purple-500"></i>
                <?php echo htmlspecialchars($property['location']); ?>
            </p>

            <div class="mt-4 flex flex-wrap gap-4">
                <div class="bg-purple-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Total Units</p>
                    <p class="text-xl font-bold text-purple-600"><?php echo $property['total_doors']; ?></p>
                </div>
                <div class="bg-green-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Available</p>
                    <p class="text-xl font-bold text-green-600"><?php echo $property['available_rooms']; ?></p>
                </div>
                <div class="bg-red-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Occupied</p>
                    <p class="text-xl font-bold text-red-600"><?php echo $property['occupied_rooms']; ?></p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-3xl font-extrabold text-purple-600"><?php echo $price_display; ?></p>
                <p class="text-xs text-gray-400">/ month</p>
            </div>

            <!-- Booking Button -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                   class="mt-6 w-full block text-center bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                    <i class="fas fa-calendar-plus mr-2"></i> Book Now
                </a>
            <?php else: ?>
                <a href="/staynest/login.php?redirect=bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                   class="mt-6 w-full block text-center bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                    <i class="fas fa-lock mr-2"></i> Login to Book
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-10 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📋 Property Description</h2>
        <p class="text-gray-600 leading-relaxed">
            <?php echo htmlspecialchars($property['description'] ?? 'Rumah kontrakan yang baru direnovasi sehingga lebih bersih dan nyaman, dilengkapi dapur, listrik token, air jetpump, akses mobil mudah, bebas banjir, dan lokasi strategis dekat berbagai tempat penting.'); ?>
        </p>
    </div>

    <!-- Facilities & Advantages -->
    <div class="grid md:grid-cols-2 gap-6 mt-6">
        <!-- Facilities -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🛋️ Facilities</h2>
            <ul class="space-y-3">
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-check-circle text-green-500"></i> <?php echo $property['total_doors']; ?> Sekat
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-check-circle text-green-500"></i> Dapur (Wastafel)
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-check-circle text-green-500"></i> Listrik Token (800 kWh)
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-check-circle text-green-500"></i> Air Tanah Jetpump
                </li>
            </ul>
        </div>

        <!-- Advantages -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">⭐ Advantages</h2>
            <ul class="space-y-3">
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> Baru Direnovasi
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> Akses Mobil Depan Kontrakan
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> 50 m dari Jalan Raya
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> Bebas Banjir
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> 2 km dari KCM Wisata Asri
                </li>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> 2 km dari McD Gading Terrace
                </li>
            </ul>
        </div>
    </div>

    <!-- Available Units -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">🏠 Available Units</h2>
        <p class="text-gray-500 mb-6">Choose your preferred room from <?php echo $property['available_rooms']; ?> available units</p>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php for ($i = 1; $i <= min($property['available_rooms'], 4); $i++): ?>
                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition">
                    <h4 class="font-bold text-gray-800">Unit 0<?php echo $i; ?></h4>
                    <p class="text-sm text-gray-500">Unit <?php echo $i; ?> Teras</p>
                    <p class="text-purple-600 font-bold mt-2"><?php echo $price_display; ?></p>
                    <span class="text-xs <?php echo $i <= 3 ? 'text-red-500' : 'text-green-500'; ?>">
                        <?php echo $i <= 3 ? '🔴 Not Available' : '🟢 Available'; ?>
                    </span>
                    <?php if ($i == 4 && isset($_SESSION['user_id'])): ?>
                        <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>" 
                           class="block text-center mt-3 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                            <i class="fas fa-calendar-plus mr-1"></i> Book Now →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
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

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>