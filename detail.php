<?php
// detail.php - PROPERTY DETAIL PAGE with Unit Images Gallery
$page_title = "Property Details - StayNest ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;
$facilities = array();
$advantages = array();
$gallery = array();
$unit_prices = array();

if ($pdo) {
    try {
        // Get property data
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        $property = $stmt->fetch();
        
        // Get all facilities
        $stmt2 = $pdo->prepare("SELECT facility_name, facility_icon FROM property_facilities WHERE property_id = ?");
        $stmt2->execute([$id]);
        $all_items = $stmt2->fetchAll();
        
        // Separate facilities and advantages
        foreach($all_items as $item) {
            $is_advantage = strpos($item['facility_name'], '✅') !== false || 
                           strpos($item['facility_name'], '📍') !== false || 
                           strpos($item['facility_name'], '🏠') !== false || 
                           strpos($item['facility_name'], '🏥') !== false || 
                           strpos($item['facility_name'], '🛍️') !== false || 
                           strpos($item['facility_name'], '🏢') !== false ||
                           strpos($item['facility_name'], '💰') !== false;
            
            if($is_advantage) {
                $advantages[] = $item;
            } else {
                $facilities[] = $item;
            }
        }
        
        // Get gallery images
        $stmt3 = $pdo->prepare("SELECT image_url, is_primary FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, id ASC");
        $stmt3->execute([$id]);
        $gallery = $stmt3->fetchAll();
        
        // Get unit prices for property
        $stmt4 = $pdo->prepare("SELECT unit_number, price_per_month FROM unit_prices WHERE property_id = ? ORDER BY unit_number");
        $stmt4->execute([$id]);
        $unit_prices = $stmt4->fetchAll();
        
    } catch(Exception $e) {
        $property = null;
    }
}

// FALLBACK DATA (UPDATED)
if (!$property) {
    $fallback = array(
        1 => array(
            'id' => 1, 
            'name' => 'StayNest Vela', 
            'location' => 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi', 
            'total_doors' => 2, 
            'available_rooms' => 1,      // 1 kosong
            'occupied_rooms' => 1,       // 1 terisi
            'price_per_month' => 700000, 
            'is_vip' => false, 
            'description' => 'Rumah kontrakan dengan 1 ruang tengah, 1 kamar tidur, 1 kamar mandi, dapur, listrik token, air tanah jetpump, akses mobil sampai depan kontrakan, bebas banjir, dan lokasi dekat Pondok Pesantren At-Taqwa.',
            'facilities' => array(
                '1 Ruang Tengah', '1 Kamar Tidur', '1 Kamar Mandi', 
                'Dapur (Westafel)', 'Listrik Token (900 kWh)', 'Air Tanah Jetpump'
            ),
            'advantages' => array(
                '✅ Akses Mobil Depan Kontrakan', '✅ Bebas Banjir', 
                '✅ 2 km dari Pondok Pesantren At-Taqwa'
            )
        ),
        2 => array(
            'id' => 2, 
            'name' => 'StayNest Aera', 
            'location' => 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi', 
            'total_doors' => 4, 
            'available_rooms' => 2,      // 2 kosong
            'occupied_rooms' => 2,       // 2 terisi
            'price_per_month' => 700000, 
            'is_vip' => true, 
            'description' => 'Rumah kontrakan 3 sekat yang baru direnovasi sehingga lebih bersih dan nyaman, dilengkapi dapur, listrik token, air jetpump, akses mobil mudah, bebas banjir, dan lokasi strategis dekat berbagai tempat penting.',
            'facilities' => array(
                '3 Sekat', 'Dapur (Westafel)', 'Listrik Token (900 kWh)', 'Air Tanah Jetpump'
            ),
            'advantages' => array(
                '🏠 Baru Direnovasi', '✅ Akses Mobil Depan Kontrakan', 
                '📍 50 m dari Jalan Raya', '✅ Bebas Banjir', 
                '📍 2 km dari KCM Wisma Asri', '📍 2 km dari McD Gading Terrace', 
                '📍 2 km dari Jembatan Besi Teluk Pucung', '📍 2 km dari Pom Bensin'
            )
        ),
        3 => array(
            'id' => 3, 
            'name' => 'StayNest Elora', 
            'location' => 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi', 
            'total_doors' => 12, 
            'available_rooms' => 7,      // 4 (L1) + 3 (L2) = 7 kosong
            'occupied_rooms' => 5,       // 2 (L1) + 3 (L2) = 5 terisi
            'price_per_month' => 800000, 
            'is_vip' => true, 
            'description' => 'Rumah minimalis 2 lantai dengan total 12 unit. Lantai 1 (unit 1-6) harga Rp 1.200.000, Lantai 2 (unit 7-12) harga Rp 1.000.000.',
            'facilities' => array(
                'Ruang Tengah', '1 Kamar Mandi', 'Dapur Westafel', 
                'Ruang Jemur', 'Listrik Token (1.300 kWh)', 'Air Tanah Jetpump'
            ),
            'advantages' => array(
                '🏢 Lantai 1 (Unit 1-6): 2 Kamar Tidur', '🏢 Lantai 2 (Unit 7-12): 1 Kamar Tidur',
                '💰 Harga Lantai 1 (Unit 1-6): Rp 1.200.000/bulan', 
                '💰 Harga Lantai 2 (Unit 7-12): Rp 1.000.000/bulan',
                '✅ Akses Mobil Depan Kontrakan', '✅ Tidak Banjir', 
                '🏥 1 km dari RS Primaya Bekasi Utara', '🛍️ 1 km dari Golden City', 
                '🛍️ 5 km dari Summarecon Mall Bekasi'
            )
        )
    );
    
    if (isset($fallback[$id])) {
        $property = $fallback[$id];
        $facilities = array();
        $advantages = array();
        foreach ($property['facilities'] as $fac) { 
            $facilities[] = array('facility_name' => $fac, 'facility_icon' => 'fas fa-check'); 
        }
        foreach ($property['advantages'] as $adv) { 
            $advantages[] = array('facility_name' => $adv, 'facility_icon' => 'fas fa-star'); 
        }
    } else { 
        echo '<meta http-equiv="refresh" content="0;url=properties.php">';
        exit; 
    }
}

$prop_id = isset($property['id']) ? $property['id'] : $id;
$prop_name = isset($property['name']) ? $property['name'] : 'Property';
$prop_location = isset($property['location']) ? $property['location'] : 'Location not available';
$prop_total = isset($property['total_doors']) ? (int)$property['total_doors'] : 0;
$prop_avail = isset($property['available_rooms']) ? (int)$property['available_rooms'] : 0;
$prop_occ = isset($property['occupied_rooms']) ? (int)$property['occupied_rooms'] : 0;
$prop_price = isset($property['price_per_month']) ? $property['price_per_month'] : 0;
$prop_vip = isset($property['is_vip']) ? $property['is_vip'] : false;
$prop_desc = isset($property['description']) ? $property['description'] : '';

// =============================================
// GAMBAR UTAMA KONTRAKAN (HERO SECTION) dari folder images
// =============================================
$hero_images = [
    1 => '/assets/images/babelan-2.jpeg',    // StayNest Vela
    2 => '/assets/images/alamanda-2.jpeg',   // StayNest Aera
    3 => '/assets/images/Vip-1.jpeg',        // StayNest Elora
];

// Gunakan gambar hero dari folder images
if (array_key_exists($prop_id, $hero_images)) {
    $prop_image = $hero_images[$prop_id];
} else {
    // Fallback ke gambar default
    $prop_image = '/assets/images/default-property.jpg';
}

// =============================================
// GAMBAR UNIT dari folder uploads (TIDAK BERUBAH)
// =============================================
function getUnitImages($property_id, $unit_number) {
    $base_path = '/uploads/';  // ← TETAP MENGGUNAKAN UPLOADS
    $images = array();
    
    // For StayNest Elora (id=3)
    if ($property_id == 3) {
        // Lantai 1 (Unit 1-6) - 7 images: Vip-1 to Vip-7
        if ($unit_number >= 1 && $unit_number <= 6) {
            for ($i = 1; $i <= 7; $i++) {
                $images[] = $base_path . 'Vip-' . $i . '.jpeg';
            }
        }
        // Lantai 2 (Unit 7-12) - 6 images: vip1 to vip6
        else if ($unit_number >= 7 && $unit_number <= 12) {
            for ($i = 1; $i <= 6; $i++) {
                $images[] = $base_path . 'vip' . $i . '.jpeg';
            }
        }
    }
    // For StayNest Vela (id=1) - 8 images: babelan-1.jpeg to babelan-8.jpeg
    elseif ($property_id == 1) {
        for ($i = 1; $i <= 8; $i++) {
            $images[] = $base_path . 'babelan-' . $i . '.jpeg';
        }
    }
    // For StayNest Aera (id=2) - 6 images: alamanda-1.jpeg to alamanda-6.jpeg
    elseif ($property_id == 2) {
        for ($i = 1; $i <= 6; $i++) {
            $images[] = $base_path . 'alamanda-' . $i . '.jpeg';
        }
    }
    
    return $images;
}

// Generate units with multiple images (UPDATED - sesuai data terbaru)
$units = array();

// Price mapping for Elora
$price_lantai_1 = 1200000;
$price_lantai_2 = 1000000;

// Generate units based on property
if ($prop_id == 3) {
    // =============================================
    // STAYNEST ELORA (id=3) - UNIT DISTRIBUTION
    // Lantai 1: Unit 1-6 (2 terisi: Unit 1-2, 4 kosong: Unit 3-6)
    // Lantai 2: Unit 7-12 (3 terisi: Unit 7-9, 3 kosong: Unit 10-12)
    // =============================================
    
    // Lantai 1 (Unit 1-6)
    for ($i = 1; $i <= 6; $i++) {
        // Unit 1-2 = TERISI (Occupied), Unit 3-6 = KOSONG (Available)
        $is_available = ($i >= 3); // Unit 3,4,5,6 available
        $images = getUnitImages($prop_id, $i);
        
        $units[] = array(
            'unit_number' => $i,
            'is_available' => $is_available,
            'price' => $price_lantai_1,
            'images' => $images,
            'floor' => '1',
            'total_images' => count($images)
        );
    }
    
    // Lantai 2 (Unit 7-12)
    for ($i = 7; $i <= 12; $i++) {
        // Unit 7-9 = TERISI (Occupied), Unit 10-12 = KOSONG (Available)
        $is_available = ($i >= 10); // Unit 10,11,12 available
        $images = getUnitImages($prop_id, $i);
        
        $units[] = array(
            'unit_number' => $i,
            'is_available' => $is_available,
            'price' => $price_lantai_2,
            'images' => $images,
            'floor' => '2',
            'total_images' => count($images)
        );
    }
} else {
    // For StayNest Vela (id=1) and StayNest Aera (id=2)
    for ($i = 1; $i <= $prop_total; $i++) {
        // Determine availability based on property
        if ($prop_id == 1) {
            // StayNest Vela: 2 doors, 1 available (Unit 2), 1 occupied (Unit 1)
            $is_available = ($i == 2);
        } elseif ($prop_id == 2) {
            // StayNest Aera: 4 doors, 2 available (Unit 3-4), 2 occupied (Unit 1-2)
            $is_available = ($i >= 3);
        } else {
            $is_available = ($i <= $prop_avail);
        }
        
        $images = getUnitImages($prop_id, $i);
        
        $units[] = array(
            'unit_number' => $i,
            'is_available' => $is_available,
            'price' => $prop_price,
            'images' => $images,
            'floor' => '1',
            'total_images' => count($images)
        );
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prop_name); ?> - StayNest ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Swiper CSS for image slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .unit-number { font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        
        .navbar-modern { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); box-shadow: 0 2px 20px rgba(0,0,0,0.05); }
        .nav-link { transition: all 0.3s ease; position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: linear-gradient(135deg, #667eea, #764ba2); transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .nav-link:hover { color: #667eea; }
        
        .unit-card {
            background: white;
            border-radius: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .unit-card:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -10px rgba(0,0,0,0.1); border-color: #667eea; }
        .unit-card.available { border-left: 4px solid #22c55e; }
        .unit-card.occupied { border-left: 4px solid #ef4444; opacity: 0.7; }
        
        .unit-slider {
            width: 100%;
            height: 220px;
            position: relative;
        }
        
        .unit-slider .swiper-slide img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .swiper-button-next, .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.5);
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 14px;
        }
        
        .swiper-pagination-bullet {
            background: white;
            opacity: 0.7;
        }
        
        .swiper-pagination-bullet-active {
            background: #667eea;
        }
        
        .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .status-available { background: rgba(34,197,94,0.2); color: #15803d; }
        .status-occupied { background: rgba(239,68,68,0.2); color: #b91c1c; }
        
        .facility-tag { background: #f1f5f9; padding: 8px 16px; border-radius: 30px; font-size: 13px; color: #475569; transition: all 0.3s ease; display: inline-block; margin: 4px; }
        .facility-tag:hover { background: linear-gradient(135deg, #667eea, #764ba2); color: white; transform: translateY(-2px); }
        
        .advantage-tag { background: #e8f5e9; padding: 8px 16px; border-radius: 30px; font-size: 13px; color: #2e7d32; transition: all 0.3s ease; display: inline-block; margin: 4px; border-left: 3px solid #4caf50; }
        .advantage-tag:hover { background: #c8e6c9; transform: translateY(-2px); }
        
        .stat-box { background: rgba(255,255,255,0.2); border-radius: 1rem; padding: 1rem; text-align: center; backdrop-filter: blur(10px); }
        .section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #667eea; font-size: 1.8rem; }
        
        .floor-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .floor-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }
        
        .image-counter {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            z-index: 10;
        }
        
        @media (max-width: 768px) { 
            .unit-card { margin-bottom: 1rem; } 
            .unit-number { font-size: 1.2rem; }
            .section-title { font-size: 1.2rem; }
            .facility-tag, .advantage-tag { font-size: 11px; padding: 5px 12px; }
            .unit-slider, .unit-slider .swiper-slide img { height: 180px; }
            .floor-title { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3"><div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center"><i class="fas fa-home text-white text-xl"></i></div><span class="text-2xl font-extrabold gradient-text">StayNest</span></a>
        <div class="hidden md:flex items-center gap-8"><a href="index.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium">Home</a><a href="properties.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium">Properties</a><a href="bookings/my_bookings.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium">My Bookings</a></div>
        <div class="flex items-center gap-3"><a href="admin/login.php" class="hidden md:block gradient-bg text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition"><i class="fas fa-user-shield mr-1"></i> Admin</a><button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700"><i class="fas fa-bars"></i></button></div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100"><div class="flex flex-col gap-3"><a href="index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a><a href="properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a><a href="bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Bookings</a><a href="admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Admin Panel</a></div></div>
</nav>

<div style="height: 80px;"></div>

<!-- Property Hero Section -->
<section class="relative gradient-bg text-white py-16 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8 items-center">
            <div class="md:w-1/2">
                <?php if($prop_vip): ?>
                    <div class="inline-flex items-center gap-2 bg-yellow-400 text-purple-900 px-3 py-1 rounded-full text-sm font-bold mb-4"><i class="fas fa-crown"></i> VIP PROPERTY</div>
                <?php endif; ?>
                <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($prop_name); ?></h1>
                <p class="text-lg text-white/90 mb-4 flex items-center gap-2"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop_location); ?></p>
                <p class="text-white/80 mb-6 leading-relaxed"><?php echo htmlspecialchars($prop_desc); ?></p>
                <div class="flex items-center gap-4">
                    <div class="stat-box"><p class="text-2xl font-bold"><?php echo $prop_total; ?></p><p class="text-xs opacity-80">Total Units</p></div>
                    <div class="stat-box"><p class="text-2xl font-bold text-green-300"><?php echo $prop_avail; ?></p><p class="text-xs opacity-80">Available</p></div>
                    <div class="stat-box"><p class="text-2xl font-bold"><?php echo $prop_occ; ?></p><p class="text-xs opacity-80">Occupied</p></div>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="<?php echo htmlspecialchars($prop_image); ?>" 
                     alt="<?php echo htmlspecialchars($prop_name); ?>" 
                     class="rounded-2xl shadow-2xl w-full h-80 object-cover">
            </div>
        </div>
    </div>
</section>

<!-- Facilities Section -->
<section class="py-12 px-6 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="section-title"><i class="fas fa-clipboard-list"></i><span class="gradient-text">Fasilitas</span></div>
        <div class="flex flex-wrap gap-2">
            <?php if(count($facilities) > 0): ?>
                <?php foreach($facilities as $fac): ?>
                    <span class="facility-tag"><i class="fas fa-check-circle text-purple-500 mr-2"></i> <?php echo htmlspecialchars($fac['facility_name']); ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="facility-tag"><i class="fas fa-wifi text-purple-500 mr-2"></i> WiFi Cepat</span>
                <span class="facility-tag"><i class="fas fa-snowflake text-purple-500 mr-2"></i> AC</span>
                <span class="facility-tag"><i class="fas fa-car text-purple-500 mr-2"></i> Parkir Luas</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Advantages Section -->
<section class="py-12 px-6 bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="section-title"><i class="fas fa-star"></i><span class="gradient-text">Keunggulan</span></div>
        <div class="flex flex-wrap gap-2">
            <?php if(count($advantages) > 0): ?>
                <?php foreach($advantages as $adv): ?>
                    <span class="advantage-tag"><i class="fas fa-check-circle text-green-600 mr-2"></i> <?php echo htmlspecialchars($adv['facility_name']); ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="advantage-tag"><i class="fas fa-check-circle text-green-600 mr-2"></i> Akses Mobil Depan Kontrakan</span>
                <span class="advantage-tag"><i class="fas fa-check-circle text-green-600 mr-2"></i> Bebas Banjir</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Units Section with Image Gallery per Unit -->
<section class="py-12 px-6 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-bold mb-2">Available <span class="gradient-text">Units</span></h2>
            <p class="text-gray-500">Choose your preferred room from <?php echo $prop_total; ?> available units</p>
            <?php if($prop_id == 3): ?>
                <div class="mt-3 inline-flex gap-4 flex-wrap justify-center">
                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-building"></i> Lantai 1 (Unit 1-6): Rp 1.200.000/bulan
                    </span>
                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-building"></i> Lantai 2 (Unit 7-12): Rp 1.000.000/bulan
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    📊 Status: Lantai 1 (Unit 1-2 Terisi, Unit 3-6 Tersedia) | Lantai 2 (Unit 7-9 Terisi, Unit 10-12 Tersedia)
                </p>
            <?php endif; ?>
        </div>
        
        <?php if($prop_id == 3): ?>
            <!-- Lantai 1 Section -->
            <div class="floor-section">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="floor-title">Lantai 1</h3>
                        <p class="text-gray-500">Unit 1 - 6 | Harga: Rp 1.200.000/bulan</p>
                        <p class="text-xs text-red-500 mt-1">⚠️ Unit 1-2 Terisi | Unit 3-6 Tersedia</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $lantai1_units = array_slice($units, 0, 6);
                    foreach($lantai1_units as $unit): 
                    ?>
                        <div class="unit-card <?php echo $unit['is_available'] ? 'available' : 'occupied'; ?>">
                            <!-- Swiper Slider for Unit Images -->
                            <div class="unit-slider swiper-container" data-swiper="swiper-<?php echo $prop_id . '-' . $unit['unit_number']; ?>">
                                <div class="swiper-wrapper">
                                    <?php foreach($unit['images'] as $image): ?>
                                        <div class="swiper-slide">
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?> Image"
                                                 onerror="this.src='/uploads/default-unit.jpg'">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="image-counter">
                                    <i class="fas fa-images"></i> <?php echo $unit['total_images']; ?> Gambar
                                </div>
                            </div>
                            
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="unit-number">Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?></div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-building"></i> Lantai <?php echo $unit['floor']; ?>
                                        </div>
                                    </div>
                                    <span class="status-badge <?php echo $unit['is_available'] ? 'status-available' : 'status-occupied'; ?>">
                                        <i class="fas <?php echo $unit['is_available'] ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-1"></i>
                                        <?php echo $unit['is_available'] ? 'Available' : 'Occupied'; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($unit['price'], 0, ',', '.'); ?></p>
                                    <p class="text-gray-400 text-sm">/month</p>
                                </div>
                                
                                <?php if($unit['is_available']): ?>
                                    <a href="/bookings/my_bookings.php?book=<?php echo $prop_id; ?>&unit=<?php echo $unit['unit_number']; ?>" 
                                       class="w-full gradient-bg text-white py-2 rounded-full font-semibold hover:shadow-lg transition flex items-center justify-center gap-2 mt-2 text-center">
                                        Book Now <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="w-full bg-gray-200 text-gray-500 py-2 rounded-full font-semibold cursor-not-allowed mt-2" disabled>
                                        <i class="fas fa-lock mr-2"></i> Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Lantai 2 Section -->
            <div class="floor-section">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="floor-title">Lantai 2</h3>
                        <p class="text-gray-500">Unit 7 - 12 | Harga: Rp 1.000.000/bulan</p>
                        <p class="text-xs text-red-500 mt-1">⚠️ Unit 7-9 Terisi | Unit 10-12 Tersedia</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $lantai2_units = array_slice($units, 6, 6);
                    foreach($lantai2_units as $unit): 
                    ?>
                        <div class="unit-card <?php echo $unit['is_available'] ? 'available' : 'occupied'; ?>">
                            <!-- Swiper Slider for Unit Images -->
                            <div class="unit-slider swiper-container" data-swiper="swiper-<?php echo $prop_id . '-' . $unit['unit_number']; ?>">
                                <div class="swiper-wrapper">
                                    <?php foreach($unit['images'] as $image): ?>
                                        <div class="swiper-slide">
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?> Image"
                                                 onerror="this.src='/uploads/default-unit.jpg'">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="image-counter">
                                    <i class="fas fa-images"></i> <?php echo $unit['total_images']; ?> Gambar
                                </div>
                            </div>
                            
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="unit-number">Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?></div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-building"></i> Lantai <?php echo $unit['floor']; ?>
                                        </div>
                                    </div>
                                    <span class="status-badge <?php echo $unit['is_available'] ? 'status-available' : 'status-occupied'; ?>">
                                        <i class="fas <?php echo $unit['is_available'] ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-1"></i>
                                        <?php echo $unit['is_available'] ? 'Available' : 'Occupied'; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($unit['price'], 0, ',', '.'); ?></p>
                                    <p class="text-gray-400 text-sm">/month</p>
                                </div>
                                
                                <?php if($unit['is_available']): ?>
                                    <a href="/bookings/my_bookings.php?book=<?php echo $prop_id; ?>&unit=<?php echo $unit['unit_number']; ?>" 
                                       class="w-full gradient-bg text-white py-2 rounded-full font-semibold hover:shadow-lg transition flex items-center justify-center gap-2 mt-2 text-center">
                                        Book Now <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="w-full bg-gray-200 text-gray-500 py-2 rounded-full font-semibold cursor-not-allowed mt-2" disabled>
                                        <i class="fas fa-lock mr-2"></i> Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- For other properties (Vela and Aera) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($units as $unit): ?>
                    <div class="unit-card <?php echo $unit['is_available'] ? 'available' : 'occupied'; ?>">
                        <!-- Swiper Slider for Unit Images -->
                        <div class="unit-slider swiper-container" data-swiper="swiper-<?php echo $prop_id . '-' . $unit['unit_number']; ?>">
                            <div class="swiper-wrapper">
                                <?php foreach($unit['images'] as $image): ?>
                                    <div class="swiper-slide">
                                        <img src="<?php echo htmlspecialchars($image); ?>" 
                                             alt="Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?> Image"
                                             onerror="this.src='/uploads/default-unit.jpg'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="image-counter">
                                <i class="fas fa-images"></i> <?php echo $unit['total_images']; ?> Gambar
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="unit-number">Unit <?php echo str_pad($unit['unit_number'], 2, '0', STR_PAD_LEFT); ?></div>
                                    <?php if($prop_id == 1): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-info-circle"></i> Unit 1 Terisi | Unit 2 Tersedia
                                        </div>
                                    <?php elseif($prop_id == 2): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-info-circle"></i> Unit 1-2 Terisi | Unit 3-4 Tersedia
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="status-badge <?php echo $unit['is_available'] ? 'status-available' : 'status-occupied'; ?>">
                                    <i class="fas <?php echo $unit['is_available'] ? 'fa-check-circle' : 'fa-times-circle'; ?> mr-1"></i>
                                    <?php echo $unit['is_available'] ? 'Available' : 'Occupied'; ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($unit['price'], 0, ',', '.'); ?></p>
                                <p class="text-gray-400 text-sm">/month</p>
                            </div>
                            
                            <?php if($unit['is_available']): ?>
                                <a href="/bookings/my_bookings.php?book=<?php echo $prop_id; ?>&unit=<?php echo $unit['unit_number']; ?>" 
                                   class="w-full gradient-bg text-white py-2 rounded-full font-semibold hover:shadow-lg transition flex items-center justify-center gap-2 mt-2 text-center">
                                    Book Now <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="w-full bg-gray-200 text-gray-500 py-2 rounded-full font-semibold cursor-not-allowed mt-2" disabled>
                                    <i class="fas fa-lock mr-2"></i> Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    // Initialize all Swiper sliders
    document.addEventListener('DOMContentLoaded', function() {
        const swiperContainers = document.querySelectorAll('.swiper-container');
        swiperContainers.forEach((container, index) => {
            new Swiper(container, {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'slide',
                speed: 500,
            });
        });
    });
    
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) { 
        mobileMenuBtn.addEventListener('click', function() { 
            mobileMenu.classList.toggle('hidden'); 
        }); 
    }
</script>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>
