<?php
// properties.php - Halaman Properties
$page_title = "Properties - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// ==============================================
// FUNGSI GET GAMBAR PROPERTI (SCAN UPLOADS & IMAGES)
// ==============================================
function getPropertyImage($property) {
    $base_path_images = '/staynest/assets/images/';
    $base_path_uploads = '/staynest/assets/uploads/';
    
    // Mapping gambar berdasarkan ID properti
    $property_images = [
        1 => ['babelan-1.jpeg', 'babelan-2.jpeg', 'babelan-3.jpeg'],
        2 => ['alamanda-2.jpeg', 'alamanda-3.jpeg', 'alamanda-4.jpeg'],
        3 => ['Vip-1.jpeg', 'Vip-2.jpeg', 'Vip-3.jpeg']
    ];
    
    $default = '/staynest/assets/images/default-property.jpg';
    
    if (isset($property_images[$property['id']])) {
        foreach ($property_images[$property['id']] as $filename) {
            // Cek di folder images
            $img_path = $base_path_images . $filename;
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path)) {
                return $img_path;
            }
            // Cek di folder uploads
            $upload_path = $base_path_uploads . $filename;
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $upload_path)) {
                return $upload_path;
            }
        }
    }
    
    // Default
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $default)) {
        return $default;
    }
    return '';
}

// ==============================================
// SCAN SEMUA GAMBAR DARI UPLOADS UNTUK PROPERTI
// ==============================================
function getAllPropertyImages($property_id) {
    $images = [];
    $base_path_uploads = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
    $base_path_images = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
    
    $prefix_mapping = [
        1 => ['babelan'],
        2 => ['alamanda'],
        3 => ['Vip', 'vip']
    ];
    
    $prefixes = $prefix_mapping[$property_id] ?? ['default'];
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    function scanFolderForImages($path, $prefixes, $extensions) {
        $found = [];
        if (!is_dir($path)) return $found;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            $filename = strtolower($file);
            foreach ($prefixes as $prefix) {
                if (strpos($filename, strtolower($prefix)) !== false) {
                    $found[] = '/staynest/assets/uploads/' . $file;
                    break;
                }
            }
        }
        return $found;
    }
    
    $upload_images = scanFolderForImages($base_path_uploads, $prefixes, $extensions);
    foreach ($upload_images as $img) {
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

try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY is_vip DESC, id ASC");
    $properties = $stmt->fetchAll();
} catch(Exception $e) {
    $properties = [];
}

if (empty($properties)) {
    $properties = [
        ['id' => 1, 'name' => 'StayNest Vela', 'location' => 'Babelan, Bekasi', 'total_doors' => 2, 'available_rooms' => 1, 'occupied_rooms' => 1, 'price_per_month' => 700000, 'is_vip' => 0],
        ['id' => 2, 'name' => 'StayNest Aera', 'location' => 'Tambun, Bekasi', 'total_doors' => 4, 'available_rooms' => 1, 'occupied_rooms' => 3, 'price_per_month' => 700000, 'is_vip' => 1],
        ['id' => 3, 'name' => 'StayNest Elora', 'location' => 'Babelan, Bekasi', 'total_doors' => 12, 'available_rooms' => 6, 'occupied_rooms' => 6, 'price_per_month' => 800000, 'is_vip' => 1]
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
            $total_images = count(getAllPropertyImages($property['id']));
        ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 group">
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
                
                <!-- Badge Jumlah Gambar -->
                <?php if ($total_images > 1): ?>
                    <div class="absolute bottom-4 right-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fas fa-images"></i> <?php echo $total_images; ?> Photos
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
                    <?php if ($total_images > 1): ?>
                        <span class="text-xs bg-blue-100 text-blue-600 px-3 py-1.5 rounded-full font-medium">
                            <i class="fas fa-images mr-1"></i> <?php echo $total_images; ?> Photos
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-2xl font-extrabold text-purple-600"><?php echo $price; ?></p>
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