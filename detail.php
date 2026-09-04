<?php
// detail.php - Halaman Detail Properti dengan SEMUA FOTO
$page_title = "Property Detail - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}

if (!$property) {
    header('Location: properties.php');
    exit;
}

// ================================================================
// CEK FOLDER UPLOADS - AMBIL SEMUA GAMBAR
// ================================================================
function getImagesFromFolder($property_id) {
    $images = [];
    
    // Path folder
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
    $image_dir = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
    
    // Prefix berdasarkan properti
    $prefixes = [
        1 => ['babelan', 'Babelan'],
        2 => ['alamanda', 'Alamanda'],
        3 => ['Vip', 'vip', 'VIP']
    ];
    
    $extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    $prefix_list = $prefixes[$property_id] ?? ['default'];
    
    // 1. Scan UPLOADS
    if (is_dir($upload_dir)) {
        $files = scandir($upload_dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            foreach ($prefix_list as $prefix) {
                if (stripos($file, $prefix) !== false) {
                    $images[] = '/staynest/assets/uploads/' . $file;
                    break;
                }
            }
        }
    }
    
    // 2. Scan IMAGES
    if (is_dir($image_dir)) {
        $files = scandir($image_dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            foreach ($prefix_list as $prefix) {
                if (stripos($file, $prefix) !== false) {
                    $img_path = '/staynest/assets/images/' . $file;
                    if (!in_array($img_path, $images)) {
                        $images[] = $img_path;
                    }
                    break;
                }
            }
        }
    }
    
    // 3. Sortir dan hapus duplikat
    $images = array_unique($images);
    sort($images);
    
    // 4. Default
    if (empty($images)) {
        $images[] = '/staynest/assets/images/default-property.jpg';
    }
    
    return $images;
}

$all_images = getImagesFromFolder($property_id);
$main_img = $all_images[0] ?? '/staynest/assets/images/default-property.jpg';
$total_images = count($all_images);

// Debug di browser (bisa dihapus setelah berhasil)
// echo "<!-- Total Images: " . $total_images . " -->";
// foreach ($all_images as $img) { echo "<!-- " . $img . " -->"; }

$price_display = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
$total_units = $property['total_doors'] ?? 4;

// Unit images - pakai gambar dari folder
$unit_images = [];
for ($i = 0; $i < $total_units; $i++) {
    $unit_images[$i + 1] = isset($all_images[$i]) ? $all_images[$i] : $main_img;
}

// Fasilitas
$facilities = ['3 Sekat', 'Dapur (Wastafel)', 'Listrik Token (800 kWh)', 'Air Tanah Jetpump'];
$advantages = ['Baru Direnovasi', 'Akses Mobil Depan Kontrakan', '50 m dari Jalan Raya', 'Bebas Banjir', '2 km dari KCM Wisata Asri', '2 km dari McD Gading Terrace', '2 km dari Jembatan Besi Teluk Pucung', '2 km dari Pom Bensin'];

// Status unit
$available = $property['available_rooms'];
$occupied = $property['occupied_rooms'];
$unit_status = [];
for ($i = 1; $i <= $total_units; $i++) {
    if ($i <= $available) $unit_status[$i] = 'available';
    elseif ($i <= $available + $occupied) $unit_status[$i] = 'occupied';
    else $unit_status[$i] = 'not_available';
}
?>

<div class="max-w-6xl mx-auto px-4 py-8" style="margin-top: 80px;">
    <a href="properties.php" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to Properties
    </a>

    <?php if ($property['is_vip']): ?>
        <div class="mb-4">
            <span class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-bold px-4 py-1.5 rounded-full text-sm shadow-lg">
                ⭐ VIP PROPERTY
            </span>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- LEFT: GAMBAR SLIDE -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="relative">
                <img id="mainImage" 
                     src="<?php echo htmlspecialchars($main_img); ?>" 
                     alt="<?php echo htmlspecialchars($property['name']); ?>"
                     class="w-full h-96 object-cover"
                     onerror="this.src='/staynest/assets/images/default-property.jpg'">
                
                <div class="absolute bottom-4 left-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full">
                    <span id="imgCounter">1 / <?php echo $total_images; ?></span>
                </div>
                
                <button onclick="changeImage(-1)" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full shadow-lg transition flex items-center justify-center">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="changeImage(1)" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full shadow-lg transition flex items-center justify-center">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Thumbnails -->
            <div id="thumbnails" class="flex gap-2 p-3 overflow-x-auto">
                <?php foreach ($all_images as $index => $img): ?>
                <img src="<?php echo htmlspecialchars($img); ?>" 
                     class="thumb-img w-16 h-12 object-cover rounded-lg cursor-pointer transition border-2 <?php echo $index === 0 ? 'border-purple-600 ring-2 ring-purple-300' : 'border-transparent opacity-50 hover:opacity-100'; ?>"
                     onclick="goToImage(<?php echo $index; ?>)"
                     onerror="this.style.display='none'">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT: INFO -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($property['name']); ?></h1>
            <p class="text-gray-500 mt-2 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-purple-500"></i>
                <?php echo htmlspecialchars($property['location']); ?>
            </p>

            <div class="mt-4 flex flex-wrap gap-4">
                <div class="bg-purple-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Total Units</p>
                    <p class="text-xl font-bold text-purple-600"><?php echo $total_units; ?></p>
                </div>
                <div class="bg-green-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Available</p>
                    <p class="text-xl font-bold text-green-600"><?php echo $available; ?></p>
                </div>
                <div class="bg-red-50 px-4 py-2 rounded-xl">
                    <p class="text-sm text-gray-500">Occupied</p>
                    <p class="text-xl font-bold text-red-600"><?php echo $occupied; ?></p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-3xl font-extrabold text-purple-600"><?php echo $price_display; ?></p>
                <p class="text-xs text-gray-400">/ month</p>
            </div>

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

    <!-- DESCRIPTION -->
    <div class="mt-10 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📋 Property Description</h2>
        <p class="text-gray-600 leading-relaxed">
            <?php echo htmlspecialchars($property['description'] ?? 'Rumah kontrakan yang baru direnovasi sehingga lebih bersih dan nyaman, dilengkapi dapur, listrik token, air jetpump, akses mobil mudah, bebas banjir, dan lokasi strategis dekat berbagai tempat penting.'); ?>
        </p>
    </div>

    <!-- FACILITIES & ADVANTAGES -->
    <div class="grid md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🛋️ Fasilitas</h2>
            <ul class="space-y-3">
                <?php foreach ($facilities as $item): ?>
                <li class="flex items-center gap-3 text-gray-600"><i class="fas fa-check-circle text-green-500"></i> <?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">⭐ Keunggulan</h2>
            <ul class="space-y-3">
                <?php foreach ($advantages as $item): ?>
                <li class="flex items-center gap-3 text-gray-600"><i class="fas fa-star text-yellow-500"></i> <?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- AVAILABLE UNITS -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">🏠 Available Units</h2>
        <p class="text-gray-500 mb-6">Choose your preferred room from <?php echo $available; ?> available units</p>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php for ($i = 1; $i <= $total_units; $i++): 
                $status = $unit_status[$i] ?? 'not_available';
                $status_text = $status == 'available' ? '🟢 Available' : ($status == 'occupied' ? '🔴 Occupied' : '🔴 Not Available');
                $status_class = $status == 'available' ? 'text-green-500' : 'text-red-500';
                $unit_img = $unit_images[$i] ?? $main_img;
            ?>
            <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-xl transition group">
                <div class="h-40 overflow-hidden bg-gray-100 relative">
                    <img src="<?php echo htmlspecialchars($unit_img); ?>" 
                         alt="Unit <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                         onerror="this.src='<?php echo htmlspecialchars($main_img); ?>'">
                    <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold <?php echo $status == 'available' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                        <?php echo $status == 'available' ? 'Available' : 'Not Available'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <h4 class="font-bold text-gray-800">Unit <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></h4>
                    <p class="text-sm text-gray-500">Unit <?php echo $i; ?> Teras</p>
                    <p class="text-purple-600 font-bold mt-2"><?php echo $price_display; ?></p>
                    <span class="text-sm <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    
                    <?php if ($status == 'available'): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>&unit=<?php echo $i; ?>" 
                               class="block text-center mt-3 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                <i class="fas fa-calendar-plus mr-1"></i> Book Now →
                            </a>
                        <?php else: ?>
                            <a href="/staynest/login.php?redirect=bookings/book_now.php?id=<?php echo $property['id']; ?>&unit=<?php echo $i; ?>" 
                               class="block text-center mt-3 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm">
                                <i class="fas fa-lock mr-1"></i> Login to Book
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <div class="mt-4 text-center text-sm text-gray-400">
            <i class="fas fa-images mr-1"></i> Total <?php echo $total_images; ?> photos available
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- SLIDE SCRIPT -->
<!-- ================================================================ -->
<script>
// Data gambar dari PHP
var images = [];
<?php foreach ($all_images as $img): ?>
images.push('<?php echo htmlspecialchars($img); ?>');
<?php endforeach; ?>

var currentIndex = 0;
var mainImage = document.getElementById('mainImage');
var imgCounter = document.getElementById('imgCounter');

function changeImage(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = images.length - 1;
    if (currentIndex >= images.length) currentIndex = 0;
    updateImage();
}

function goToImage(index) {
    currentIndex = index;
    updateImage();
}

function updateImage() {
    mainImage.src = images[currentIndex];
    imgCounter.textContent = (currentIndex + 1) + ' / ' + images.length;
    
    // Update thumbnails
    document.querySelectorAll('.thumb-img').forEach(function(el, i) {
        if (i === currentIndex) {
            el.className = 'thumb-img w-16 h-12 object-cover rounded-lg cursor-pointer transition border-2 border-purple-600 ring-2 ring-purple-300';
        } else {
            el.className = 'thumb-img w-16 h-12 object-cover rounded-lg cursor-pointer transition border-2 border-transparent opacity-50 hover:opacity-100';
        }
    });
}

// Auto slide
var autoSlide = setInterval(function() {
    changeImage(1);
}, 4000);

// Pause on hover
document.querySelector('.bg-white.rounded-2xl.shadow-lg.overflow-hidden').addEventListener('mouseenter', function() {
    clearInterval(autoSlide);
});
document.querySelector('.bg-white.rounded-2xl.shadow-lg.overflow-hidden').addEventListener('mouseleave', function() {
    autoSlide = setInterval(function() {
        changeImage(1);
    }, 4000);
});

// Keyboard
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') changeImage(-1);
    else if (e.key === 'ArrowRight') changeImage(1);
});

console.log('📸 Total images loaded: ' + images.length);
</script>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.thumb-img {
    min-width: 64px;
    max-width: 64px;
}
.thumb-img:hover {
    opacity: 0.8 !important;
}
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>