<?php
// properties.php - ALL PROPERTIES PAGE (Dengan Gambar Modern)
$page_title = "Properties - StayNest | Find Your Cozy Home ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Get filter parameters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 2000000;

// Initialize variables
$properties = [];
$locations = [];

// Try to fetch from database
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT DISTINCT location FROM properties ORDER BY location");
        $locations = $stmt->fetchAll();
    } catch(Exception $e) {
        $locations = [];
    }
    
    try {
        $sql = "SELECT * FROM properties WHERE 1=1";
        $params = [];
        
        if(!empty($location)) {
            $sql .= " AND location LIKE :location";
            $params[':location'] = "%$location%";
        }
        
        if($min_price > 0) {
            $sql .= " AND price_per_month >= :min_price";
            $params[':min_price'] = $min_price;
        }
        
        if($max_price > 0) {
            $sql .= " AND price_per_month <= :max_price";
            $params[':max_price'] = $max_price;
        }
        
        $sql .= " ORDER BY is_vip DESC, id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
    } catch(Exception $e) {
        $properties = [];
    }
}

// ==============================================
// FUNGSI UNTUK MENDAPATKAN GAMBAR PROPERTI
// ==============================================
function getPropertyImage($property_id, $property_name) {
    $property_images = [
        1 => '/staynest/assets/images/babelan-2.jpeg',    // StayNest Vela
        2 => '/staynest/assets/images/alamanda-2.jpeg',   // StayNest Aera
        3 => '/staynest/assets/images/Vip-1.jpeg',        // StayNest Elora
    ];
    
    if (isset($property_images[$property_id])) {
        return $property_images[$property_id];
    }
    
    return '/staynest/assets/images/default-property.jpg';
}

// DATA PROPERTI DENGAN DATA BARU (UPDATED)
if (empty($properties)) {
    $properties = [
        [
            'id' => 1,
            'name' => 'StayNest Vela',
            'location' => 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi',
            'total_doors' => 2,
            'available_rooms' => 1,      // 1 terisi, 1 penuh (1 available)
            'occupied_rooms' => 1,
            'price_per_month' => 700000,
            'is_vip' => false
        ],
        [
            'id' => 2,
            'name' => 'StayNest Aera',
            'location' => 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi',
            'total_doors' => 4,
            'available_rooms' => 1,      // 3 terisi, 1 penuh (1 available)
            'occupied_rooms' => 3,
            'price_per_month' => 700000,
            'is_vip' => true
        ],
        [
            'id' => 3,
            'name' => 'StayNest Elora',
            'location' => 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi',
            'total_doors' => 12,
            'available_rooms' => 6,      // 6 terisi, 6 penuh (6 available)
            'occupied_rooms' => 6,
            'price_per_month' => 800000,
            'is_vip' => true
        ]
    ];
}

// Fallback locations
if (empty($locations)) {
    $locations = [
        ['location' => 'Babelan'],
        ['location' => 'Alamanda'],
        ['location' => 'Vip'],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>StayNest - Properties | Find Your Cozy Home ✨</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .properties-hero {
            position: relative;
            min-height: 35vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2ff 40%, #fdf2f8 100%);
        }
        
        .hero-bg-element {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: floatElement 15s ease-in-out infinite;
        }
        
        .bg-elem-1 { background: #667eea; width: 300px; height: 300px; top: -80px; right: -80px; }
        .bg-elem-2 { background: #f093fb; width: 250px; height: 250px; bottom: -60px; left: -60px; animation-delay: -5s; }
        
        @keyframes floatElement {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.2); }
            66% { transform: translate(-30px, 30px) scale(0.8); }
        }
        
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #667eea; transform: translateY(-2px); }
        
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: -30px;
            position: relative;
            z-index: 10;
        }
        
        .filter-select, .filter-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            outline: none;
            transition: all 0.3s ease;
            background: white;
            font-size: 14px;
        }
        
        .filter-select:focus, .filter-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .filter-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .filter-btn:hover { transform: scale(1.02); box-shadow: 0 5px 20px rgba(102,126,234,0.3); }
        
        .reset-btn {
            background: #f1f5f9;
            color: #64748b;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }
        
        .reset-btn:hover { background: #e2e8f0; color: #334155; }
        
        .property-card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            cursor: pointer;
        }
        
        .property-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 50px rgba(0,0,0,0.15);
        }
        
        .property-img {
            position: relative;
            overflow: hidden;
            height: 230px;
        }
        
        .property-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .property-card:hover .property-img img {
            transform: scale(1.1);
        }
        
        .property-img::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
            pointer-events: none;
        }
        
        .vip-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ffd89b, #c7e9fb);
            color: #ff6b6b;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .available-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(5px);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            color: #667eea;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .property-detail-icon {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 20px;
        }
        
        .price-tag {
            font-size: 1.6rem;
            font-weight: 800;
            color: #667eea;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-up { animation: fadeInUp 0.6s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        
        @media (max-width: 768px) {
            .filter-section { padding: 16px; margin-top: -20px; }
            .property-img { height: 180px; }
            .price-tag { font-size: 1.3rem; }
            .property-detail-icon { font-size: 11px; padding: 3px 8px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <div class="hidden md:flex items-center gap-8">
            <a href="index.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium">Home</a>
            <a href="properties.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium active">Properties</a>
            <a href="bookings/my_bookings.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium">My Bookings</a>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="admin/login.php" class="hidden md:block gradient-bg text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition">
                <i class="fas fa-user-shield mr-1"></i> Admin
            </a>
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">Home</a>
            <a href="properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">Properties</a>
            <a href="bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">My Bookings</a>
            <a href="admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center transition">Admin Panel</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="properties-hero pt-32 pb-12 px-6 relative">
    <div class="hero-bg-element bg-elem-1"></div>
    <div class="hero-bg-element bg-elem-2"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 animate-fade-up">
                <span class="gradient-text">Find Your Perfect Space</span> 🏠
            </h1>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto animate-fade-up delay-100">
                Discover the best boarding houses curated just for you
            </p>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="px-6 pb-8">
    <div class="max-w-5xl mx-auto">
        <div class="filter-section">
            <form method="GET" action="properties.php">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📍 Location</label>
                        <select name="location" class="filter-select">
                            <option value="">All Locations</option>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                    <?php echo ($location == $loc['location']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">💰 Min Price</label>
                        <input type="number" name="min_price" class="filter-input" placeholder="0" value="<?php echo $min_price; ?>" min="0" step="50000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">💰 Max Price</label>
                        <input type="number" name="max_price" class="filter-input" placeholder="2,000,000" value="<?php echo $max_price; ?>" min="0" step="50000">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="properties.php" class="reset-btn">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Properties Section -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-8">
            <p class="text-gray-500">
                Showing <span class="font-bold text-purple-600"><?php echo count($properties); ?></span> properties
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(count($properties) > 0): ?>
                <?php foreach($properties as $index => $property): 
                    $property_image = getPropertyImage($property['id'], $property['name']);
                    
                    // Format harga untuk tampilan (1 juta - 1,2 juta untuk Elora)
                    $display_price = $property['price_per_month'];
                    if ($property['id'] == 3) {
                        $display_price_text = "Rp 1.000.000 - Rp 1.200.000";
                    } else {
                        $display_price_text = "Rp " . number_format($display_price, 0, ',', '.');
                    }
                ?>
                <div class="property-card animate-fade-up" style="animation-delay: <?php echo $index * 0.1; ?>s"
                     onclick="window.location.href='detail.php?id=<?php echo $property['id']; ?>'">
                    
                    <div class="property-img">
                        <img src="<?php echo htmlspecialchars($property_image); ?>" 
                             alt="<?php echo htmlspecialchars($property['name']); ?>"
                             onerror="this.src='/staynest/assets/images/default-property.jpg'">
                        
                        <?php if(isset($property['is_vip']) && ($property['is_vip'] == 1 || $property['is_vip'] === true)): ?>
                            <div class="vip-badge">
                                <i class="fas fa-crown mr-1"></i> VIP
                            </div>
                        <?php endif; ?>
                        
                        <div class="available-badge">
                            <i class="fas fa-bed mr-1"></i> 
                            <?php echo $property['available_rooms']; ?> Available
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($property['name']); ?></h3>
                                <p class="text-gray-500 text-sm flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt text-purple-500"></i> 
                                    <?php echo htmlspecialchars($property['location']); ?>
                                </p>
                            </div>
                            <button class="wishlist-btn text-gray-300 hover:text-red-500 transition" onclick="event.stopPropagation()">
                                <i class="far fa-heart text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 mb-4 mt-3">
                            <span class="property-detail-icon">
                                <i class="fas fa-door-open text-purple-500"></i>
                                Total <?php echo $property['total_doors']; ?> Doors
                            </span>
                            <span class="property-detail-icon">
                                <i class="fas fa-bed text-purple-500"></i>
                                <?php echo $property['occupied_rooms']; ?> Occupied
                            </span>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-4 mt-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="price-tag">
                                        <?php echo $display_price_text; ?>
                                    </p>
                                    <p class="text-gray-400 text-xs">/month</p>
                                </div>
                                <a href="detail.php?id=<?php echo $property['id']; ?>" class="bg-purple-600 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-purple-700 transition flex items-center gap-2 shadow-md" onclick="event.stopPropagation()">
                                    View Details <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-16">
                    <div class="inline-flex flex-col items-center gap-4 text-gray-400">
                        <i class="fas fa-building fa-5x"></i>
                        <h3 class="text-xl font-semibold text-gray-700">No properties found</h3>
                        <p class="text-gray-500">Try adjusting your filters</p>
                        <a href="properties.php" class="gradient-bg text-white px-6 py-2 rounded-full hover:shadow-lg transition">
                            <i class="fas fa-sync-alt mr-2"></i> Reset Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // Mobile menu toggle
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Wishlist functionality
    var wishlistBtns = document.querySelectorAll('.wishlist-btn');
    for (var w = 0; w < wishlistBtns.length; w++) {
        wishlistBtns[w].addEventListener('click', function(e) {
            e.stopPropagation();
            var icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#ef4444';
                this.style.transform = 'scale(1.2)';
                var self = this;
                setTimeout(function() { self.style.transform = ''; }, 200);
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        });
    }
</script>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>