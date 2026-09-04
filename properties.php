<?php
// properties.php - Halaman Properties dengan Gambar dari Uploads
$page_title = "Properties - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// ==============================================
// FUNGSI GET GAMBAR PROPERTI (SCAN UPLOADS & IMAGES)
// ==============================================
function getPropertyImage($property_id) {
    $image_path = '/staynest/assets/images/';
    $upload_path = '/staynest/assets/uploads/';
    
    // Mapping prefix berdasarkan ID properti
    $prefixes = [
        1 => ['babelan', 'Babelan'],
        2 => ['alamanda', 'Alamanda'],
        3 => ['Vip', 'vip', 'VIP']
    ];
    
    $extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    
    if (isset($prefixes[$property_id])) {
        $prefix_list = $prefixes[$property_id];
        
        // Cek di folder uploads dulu (prioritas)
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $upload_path)) {
            $files = scandir($_SERVER['DOCUMENT_ROOT'] . $upload_path);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $filename = strtolower($file);
                foreach ($prefix_list as $prefix) {
                    if (strpos($filename, strtolower($prefix)) !== false) {
                        return $upload_path . $file;
                    }
                }
            }
        }
        
        // Cek di folder images
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $image_path)) {
            $files = scandir($_SERVER['DOCUMENT_ROOT'] . $image_path);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $filename = strtolower($file);
                foreach ($prefix_list as $prefix) {
                    if (strpos($filename, strtolower($prefix)) !== false) {
                        return $image_path . $file;
                    }
                }
            }
        }
    }
    
    // Default
    $default = '/staynest/assets/images/default-property.jpg';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $default)) {
        return $default;
    }
    return '';
}

// ==============================================
// FUNGSI GET SEMUA GAMBAR UNTUK SLIDE
// ==============================================
function getAllPropertyImages($property_id) {
    $images = [];
    $image_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
    $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
    
    $prefixes = [
        1 => ['babelan', 'Babelan'],
        2 => ['alamanda', 'Alamanda'],
        3 => ['Vip', 'vip', 'VIP']
    ];
    
    $extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    $prefix_list = $prefixes[$property_id] ?? ['default'];
    
    // Scan uploads
    if (is_dir($upload_path)) {
        $files = scandir($upload_path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            $filename = strtolower($file);
            foreach ($prefix_list as $prefix) {
                if (strpos($filename, strtolower($prefix)) !== false) {
                    $images[] = '/staynest/assets/uploads/' . $file;
                    break;
                }
            }
        }
    }
    
    // Scan images
    if (is_dir($image_path)) {
        $files = scandir($image_path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            $filename = strtolower($file);
            foreach ($prefix_list as $prefix) {
                if (strpos($filename, strtolower($prefix)) !== false) {
                    $img_path = '/staynest/assets/images/' . $file;
                    if (!in_array($img_path, $images)) {
                        $images[] = $img_path;
                    }
                    break;
                }
            }
        }
    }
    
    // Jika tidak ada gambar
    if (empty($images)) {
        $images[] = '/staynest/assets/images/default-property.jpg';
    }
    
    return $images;
}

// ==============================================
// AMBIL DATA PROPERTI
// ==============================================
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
            $img = getPropertyImage($property['id']);
            $all_imgs = getAllPropertyImages($property['id']);
            $total_images = count($all_imgs);
            $price = "Rp " . number_format($property['price_per_month'] ?? 700000, 0, ',', '.');
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
                
                <?php if ($property['is_vip']): ?>
                    <div class="absolute top-4 left-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">⭐ VIP</div>
                <?php endif; ?>
                
                <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm text-xs font-bold px-4 py-1.5 rounded-full shadow-lg text-purple-600">
                    🛏 <?php echo $property['available_rooms']; ?> Available
                </div>
                
                <?php if ($total_images > 1): ?>
                    <div class="absolute bottom-4 right-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fas fa-images"></i> <?php echo $total_images; ?>
                    </div>
                <?php endif; ?>
                
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