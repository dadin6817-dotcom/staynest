<?php
// detail.php - Halaman Detail Properti dengan Gambar dari UPLOADS & IMAGES
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

// ==============================================
// FUNGSI SCAN SEMUA GAMBAR DARI FOLDER
// ==============================================
function getAllPropertyImages($property_id, $property_name) {
    $images = [];
    $base_path_images = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
    $base_path_uploads = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
    
    // Mapping prefix berdasarkan properti
    $prefix_mapping = [
        1 => ['babelan', 'Babelan'],
        2 => ['alamanda', 'Alamanda'],
        3 => ['Vip', 'vip', 'VIP']
    ];
    
    $prefixes = $prefix_mapping[$property_id] ?? ['default'];
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Fungsi untuk scan folder
    function scanFolder($path, $prefixes, $extensions) {
        $found = [];
        if (!is_dir($path)) return $found;
        
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            
            $filename = strtolower($file);
            foreach ($prefixes as $prefix) {
                $prefix_lower = strtolower($prefix);
                if (strpos($filename, $prefix_lower) !== false) {
                    $found[] = '/staynest/assets/uploads/' . $file;
                    break;
                }
            }
        }
        return $found;
    }
    
    // Scan folder uploads
    $upload_images = scanFolder($base_path_uploads, $prefixes, $extensions);
    foreach ($upload_images as $img) {
        $images[] = $img;
    }
    
    // Scan folder images (fallback)
    $image_images = scanFolder($base_path_images, $prefixes, $extensions);
    foreach ($image_images as $img) {
        if (!in_array($img, $images)) {
            $images[] = $img;
        }
    }
    
    // Jika tidak ada gambar, pakai default
    if (empty($images)) {
        $images[] = '/staynest/assets/images/default-property.jpg';
    }
    
    return $images;
}

// ==============================================
// FUNGSI GET GAMBAR UNIT
// ==============================================
function getUnitImages($property_id, $total_units) {
    $base_path_images = '/staynest/assets/images/';
    $base_path_uploads = '/staynest/assets/uploads/';
    $unit_images = [];
    
    $unit_mapping = [
        1 => ['babelan', 1, 8],
        2 => ['alamanda', 2, 6],
        3 => ['Vip', 1, 8]
    ];
    
    if (isset($unit_mapping[$property_id])) {
        $data = $unit_mapping[$property_id];
        $prefix = $data[0];
        $start = $data[1];
        $count = $data[2];
        
        for ($i = 0; $i < $total_units && $i < $count; $i++) {
            $num = $start + $i;
            $filename = $base_path_images . $prefix . '-' . $num . '.jpeg';
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $filename)) {
                $unit_images[$i + 1] = $filename;
            }
        }
    }
    
    return $unit_images;
}

// Ambil semua gambar properti
$all_images = getAllPropertyImages($property_id, $property['name']);
$main_img = !empty($all_images) ? $all_images[0] : '/staynest/assets/images/default-property.jpg';

$price_display = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
$total_units = $property['total_doors'];
$unit_images = getUnitImages($property_id, $total_units);

// Data fasilitas & keunggulan
$facilities = [
    '3 Sekat',
    'Dapur (Wastafel)',
    'Listrik Token (800 kWh)',
    'Air Tanah Jetpump'
];

$advantages = [
    'Baru Direnovasi',
    'Akses Mobil Depan Kontrakan',
    '50 m dari Jalan Raya',
    'Bebas Banjir',
    '2 km dari KCM Wisata Asri',
    '2 km dari McD Gading Terrace',
    '2 km dari Jembatan Besi Teluk Pucung',
    '2 km dari Pom Bensin'
];

// Data status unit
$available = $property['available_rooms'];
$occupied = $property['occupied_rooms'];
$unit_status = [];

for ($i = 1; $i <= $total_units; $i++) {
    if ($i <= $available) {
        $unit_status[$i] = 'available';
    } elseif ($i <= $available + $occupied) {
        $unit_status[$i] = 'occupied';
    } else {
        $unit_status[$i] = 'not_available';
    }
}
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
        <!-- Left: Image Slide -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden relative">
            <div class="relative">
                <img id="mainPropertyImage" 
                     src="<?php echo htmlspecialchars($main_img); ?>" 
                     alt="<?php echo htmlspecialchars($property['name']); ?>"
                     class="w-full h-96 object-cover"
                     onerror="this.src='/staynest/assets/images/default-property.jpg'">
                
                <!-- Image Counter -->
                <div class="absolute bottom-4 left-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full">
                    <span id="imageCounter">1 / <?php echo count($all_images); ?></span>
                </div>
                
                <!-- Navigation Buttons -->
                <button id="prevImageBtn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full shadow-lg transition flex items-center justify-center">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="nextImageBtn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full shadow-lg transition flex items-center justify-center">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Image Indicator Dots -->
                <div id="imageDots" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                    <!-- Dots akan diisi JavaScript -->
                </div>
            </div>
            
            <!-- Thumbnails -->
            <div id="thumbnailContainer" class="flex gap-2 p-3 overflow-x-auto">
                <!-- Thumbnails akan diisi oleh JavaScript -->
            </div>
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

    <!-- Description -->
    <div class="mt-10 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📋 Property Description</h2>
        <p class="text-gray-600 leading-relaxed">
            <?php echo htmlspecialchars($property['description'] ?? 'Rumah kontrakan yang baru direnovasi sehingga lebih bersih dan nyaman, dilengkapi dapur, listrik token, air jetpump, akses mobil mudah, bebas banjir, dan lokasi strategis dekat berbagai tempat penting.'); ?>
        </p>
    </div>

    <!-- Facilities & Advantages -->
    <div class="grid md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🛋️ Fasilitas</h2>
            <ul class="space-y-3">
                <?php foreach ($facilities as $item): ?>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-check-circle text-green-500"></i> <?php echo $item; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">⭐ Keunggulan</h2>
            <ul class="space-y-3">
                <?php foreach ($advantages as $item): ?>
                <li class="flex items-center gap-3 text-gray-600">
                    <i class="fas fa-star text-yellow-500"></i> <?php echo $item; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Available Units -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">🏠 Available Units</h2>
        <p class="text-gray-500 mb-6">Choose your preferred room from <?php echo $available; ?> available units</p>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php for ($i = 1; $i <= $total_units; $i++): 
                $status = $unit_status[$i] ?? 'not_available';
                $status_text = $status == 'available' ? '🟢 Available' : ($status == 'occupied' ? '🔴 Occupied' : '🔴 Not Available');
                $status_class = $status == 'available' ? 'text-green-500' : 'text-red-500';
                $unit_img = isset($unit_images[$i]) ? $unit_images[$i] : $main_img;
            ?>
            <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-xl transition group">
                <div class="h-40 overflow-hidden bg-gray-100 relative">
                    <img src="<?php echo htmlspecialchars($unit_img); ?>" 
                         alt="Unit <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                         onerror="this.src='<?php echo htmlspecialchars($main_img); ?>'">
                    <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold <?php echo $status == 'available' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                        <?php echo $status == 'available' ? 'Available' : ($status == 'occupied' ? 'Occupied' : 'Not Available'); ?>
                    </div>
                </div>
                
                <div class="p-4">
                    <h4 class="font-bold text-gray-800">Unit <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></h4>
                    <p class="text-sm text-gray-500">Unit <?php echo $i; ?> Teras</p>
                    <p class="text-purple-600 font-bold mt-2"><?php echo $price_display; ?></p>
                    <span class="text-sm <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    
                    <?php if ($status == 'available' && isset($_SESSION['user_id'])): ?>
                        <a href="/staynest/bookings/book_now.php?id=<?php echo $property['id']; ?>&unit=<?php echo $i; ?>" 
                           class="block text-center mt-3 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                            <i class="fas fa-calendar-plus mr-1"></i> Book Now →
                        </a>
                    <?php elseif ($status == 'available' && !isset($_SESSION['user_id'])): ?>
                        <a href="/staynest/login.php?redirect=bookings/book_now.php?id=<?php echo $property['id']; ?>&unit=<?php echo $i; ?>" 
                           class="block text-center mt-3 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm">
                            <i class="fas fa-lock mr-1"></i> Login to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SLIDE SCRIPT - LENGKAP -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua gambar dari PHP
    var images = [];
    <?php foreach ($all_images as $img): ?>
    images.push('<?php echo htmlspecialchars($img); ?>');
    <?php endforeach; ?>
    
    // Jika hanya 1 gambar, tambahkan duplikat untuk efek slide
    if (images.length === 1) {
        images.push(images[0]);
        images.push(images[0]);
    }
    
    var currentIndex = 0;
    var mainImage = document.getElementById('mainPropertyImage');
    var imageCounter = document.getElementById('imageCounter');
    var thumbnailContainer = document.getElementById('thumbnailContainer');
    var imageDots = document.getElementById('imageDots');
    var autoSlideInterval;
    
    function updateImage(index) {
        if (index < 0) index = images.length - 1;
        if (index >= images.length) index = 0;
        currentIndex = index;
        
        mainImage.src = images[currentIndex];
        imageCounter.textContent = (currentIndex + 1) + ' / ' + images.length;
        
        // Update active thumbnail
        document.querySelectorAll('.thumb-img').forEach(function(el, i) {
            if (i === currentIndex) {
                el.classList.add('border-purple-600', 'ring-2', 'ring-purple-300');
                el.classList.remove('opacity-50');
            } else {
                el.classList.remove('border-purple-600', 'ring-2', 'ring-purple-300');
                el.classList.add('opacity-50');
            }
        });
        
        // Update dots
        document.querySelectorAll('.dot').forEach(function(el, i) {
            if (i === currentIndex) {
                el.classList.add('bg-purple-600', 'scale-125');
                el.classList.remove('bg-gray-300');
            } else {
                el.classList.remove('bg-purple-600', 'scale-125');
                el.classList.add('bg-gray-300');
            }
        });
    }
    
    // Create thumbnails
    thumbnailContainer.innerHTML = '';
    images.forEach(function(src, index) {
        var thumb = document.createElement('img');
        thumb.src = src;
        thumb.className = 'thumb-img w-16 h-12 object-cover rounded-lg cursor-pointer transition border-2 border-transparent hover:opacity-100 ' + (index === 0 ? 'border-purple-600 ring-2 ring-purple-300' : 'opacity-50');
        thumb.onclick = function() { updateImage(index); };
        thumbnailContainer.appendChild(thumb);
    });
    
    // Create dots
    imageDots.innerHTML = '';
    images.forEach(function(src, index) {
        var dot = document.createElement('span');
        dot.className = 'dot w-2 h-2 rounded-full transition-all ' + (index === 0 ? 'bg-purple-600 scale-125' : 'bg-gray-300');
        imageDots.appendChild(dot);
    });
    
    // Navigation buttons
    document.getElementById('prevImageBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        updateImage(currentIndex - 1);
    });
    
    document.getElementById('nextImageBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        updateImage(currentIndex + 1);
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            updateImage(currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
            updateImage(currentIndex + 1);
        }
    });
    
    // Auto slide
    function startAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(function() {
            updateImage(currentIndex + 1);
        }, 4000);
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    startAutoSlide();
    
    // Pause on hover
    var imageContainer = document.getElementById('mainPropertyImage').parentElement;
    imageContainer.addEventListener('mouseenter', stopAutoSlide);
    imageContainer.addEventListener('mouseleave', startAutoSlide);
    
    // Touch support for mobile
    var touchStartX = 0;
    var touchEndX = 0;
    imageContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    imageContainer.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        var diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                updateImage(currentIndex + 1);
            } else {
                updateImage(currentIndex - 1);
            }
        }
    });
    
    // ==========================================
    // INISIALISASI
    // ==========================================
    updateImage(0);
    console.log('📸 Total images loaded: ' + images.length);
});
</script>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.thumb-img {
    transition: all 0.3s ease;
    min-width: 64px;
}
.thumb-img:hover {
    opacity: 0.8 !important;
}
.dot {
    transition: all 0.3s ease;
}
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>