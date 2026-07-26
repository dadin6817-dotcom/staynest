<?php
// index.php - BERANDA (Homepage - Full English Version - Tanpa Watch Video)
$page_title = "StayNest - Find Your Cozy Home ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// ==============================================
// FUNGSI UNTUK MENDAPATKAN GAMBAR PROPERTI (SAMA DENGAN properties.php)
// ==============================================
function getPropertyImage($property_id, $property_name) {
    // Mapping gambar properti dari folder assets/images
    $property_images = [
        1 => '/staynest/assets/images/babelan-2.jpeg',    // StayNest Vela
        2 => '/staynest/assets/images/alamanda-2.jpeg',   // StayNest Aera
        3 => '/staynest/assets/images/Vip-1.jpeg',        // StayNest Elora
    ];
    
    if (isset($property_images[$property_id])) {
        return $property_images[$property_id];
    }
    
    // Fallback ke gambar default
    return '/staynest/assets/images/default-property.jpg';
}

// Fetch featured properties
try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY is_vip DESC, id DESC LIMIT 6");
    $featured_properties = $stmt->fetchAll();
} catch(Exception $e) {
    $featured_properties = [];
}

// ==============================================
// DATA FALLBACK YANG SUDAH DIREVISI
// StayNest Elora: 12 doors, 7 available (kosong), 5 occupied (terisi)
// StayNest Aera: 4 doors, 2 available, 2 occupied
// StayNest Vela: 2 doors, 1 available, 1 occupied
// ==============================================
if (empty($featured_properties)) {
    $featured_properties = [
        [
            'id' => 1, 
            'name' => 'StayNest Vela', 
            'location' => 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi', 
            'total_doors' => 2, 
            'available_rooms' => 1,      // 1 kosong
            'occupied_rooms' => 1,       // 1 terisi
            'price_per_month' => 700000, 
            'is_vip' => false
        ],
        [
            'id' => 2, 
            'name' => 'StayNest Aera', 
            'location' => 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi', 
            'total_doors' => 4, 
            'available_rooms' => 2,      // 2 kosong
            'occupied_rooms' => 2,       // 2 terisi
            'price_per_month' => 700000, 
            'is_vip' => true
        ],
        [
            'id' => 3, 
            'name' => 'StayNest Elora', 
            'location' => 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi', 
            'total_doors' => 12, 
            'available_rooms' => 7,      // 4 (L1) + 3 (L2) = 7 kosong
            'occupied_rooms' => 5,       // 2 (L1) + 3 (L2) = 5 terisi
            'price_per_month' => 800000, 
            'is_vip' => true
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayNest - Find Your Cozy Home ✨</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            overflow-x: hidden;
        }
        
        /* Gradient Classes */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg-pink {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 85vh;
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
        
        .bg-elem-1 {
            background: #667eea;
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }
        
        .bg-elem-2 {
            background: #f093fb;
            width: 350px;
            height: 350px;
            bottom: -80px;
            left: -80px;
            animation-delay: -5s;
        }
        
        @keyframes floatElement {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.2); }
            66% { transform: translate(-30px, 30px) scale(0.8); }
        }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102,126,234,0.4);
        }
        
        /* Search Container */
        .search-container {
            background: white;
            border-radius: 80px;
            padding: 8px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .search-container:focus-within {
            box-shadow: 0 20px 40px rgba(102,126,234,0.15);
            transform: scale(1.02);
        }
        
        .search-input {
            border: none;
            padding: 18px 25px;
            border-radius: 80px;
            width: 100%;
            font-size: 1rem;
            outline: none;
            background: transparent;
        }
        
        .search-input::placeholder {
            color: #a0aec0;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 16px 40px;
            border-radius: 60px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }
        
        /* Feature Card */
        .feature-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 50px rgba(102,126,234,0.12);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scale(1.1);
        }
        
        .feature-card:hover .feature-icon i {
            color: white !important;
        }
        
        /* Property Card */
        .property-card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            transition: all 0.4s ease;
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
            transition: transform 0.5s ease;
        }
        
        .property-card:hover .property-img img {
            transform: scale(1.1);
        }
        
        .vip-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ffd89b, #c7e9fb);
            color: #ff6b6b;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
            z-index: 2;
        }
        
        .available-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(5px);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            color: #667eea;
            z-index: 2;
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
        
        /* Stats Section */
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card:hover .stat-number,
        .stat-card:hover .stat-label {
            color: white !important;
            -webkit-text-fill-color: white !important;
        }
        
        .stat-label {
            color: #64748b;
            margin-top: 0.5rem;
            font-weight: 500;
        }
        
        /* Testimonial Card */
        .testimonial-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102,126,234,0.15);
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '🏠';
            position: absolute;
            font-size: 200px;
            opacity: 0.1;
            bottom: -60px;
            right: -60px;
            transform: rotate(-15deg);
        }
        
        .cta-section::after {
            content: '✨';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            top: -40px;
            left: -40px;
            transform: rotate(10deg);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-bounce {
            animation: bounce 1s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .btn-primary {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .feature-card {
                padding: 1.5rem;
            }
            
            .search-container {
                padding: 5px;
            }
            
            .search-btn {
                padding: 12px 25px;
                font-size: 14px;
            }
            
            .property-img {
                height: 180px;
            }
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero-section pt-32 pb-20 px-6 relative">
    <div class="hero-bg-element bg-elem-1"></div>
    <div class="hero-bg-element bg-elem-2"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 bg-white/60 backdrop-blur-sm rounded-full px-5 py-2 mb-6 animate-fade-up">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-gray-700 font-medium text-sm">✨ Find Your Perfect Space ✨</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold leading-tight mb-6 animate-fade-up delay-100">
                <span class="gradient-text">StayNest</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-gray-600 max-w-2xl mx-auto mb-4 animate-fade-up delay-200 leading-relaxed">
                Find a place you'll love to call home
            </p>
            
            <p class="text-base md:text-lg text-gray-500 max-w-2xl mx-auto mb-10 animate-fade-up delay-300 leading-relaxed">
                Discover cozy spaces that match your lifestyle. Modern, affordable, and totally instagrammable.
                Start your journey to find your dream home now! 🏠✨
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-fade-up delay-400">
                <a href="#properties" class="btn-primary">
                    <i class="fas fa-search"></i> Explore Now
                </a>
            </div>
            
            <div class="max-w-2xl mx-auto animate-fade-up delay-400">
                <div class="search-container">
                    <div class="flex flex-col md:flex-row items-center gap-2">
                        <div class="flex-1 relative w-full">
                            <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="searchInput" placeholder="Search location... Example: Babelan, Alamanda, VIP" class="search-input pl-14 pr-4 w-full">
                        </div>
                        <button id="searchBtn" class="search-btn w-full md:w-auto">
                            <i class="fas fa-arrow-right mr-2"></i> Search
                        </button>
                    </div>
                </div>
                <div id="searchResults" class="mt-6 space-y-3"></div>
                
                <!-- POPULAR TAGS -->
                <div class="flex flex-wrap gap-2 justify-center mt-6">
                    <span class="text-sm text-gray-500">Popular:</span>
                    <a href="properties.php?location=Babelan" class="text-sm px-3 py-1 bg-gray-100 rounded-full hover:bg-purple-100 hover:text-purple-600 transition">📍 Babelan</a>
                    <a href="properties.php?location=Alamanda" class="text-sm px-3 py-1 bg-gray-100 rounded-full hover:bg-purple-100 hover:text-purple-600 transition">📍 Alamanda</a>
                    <a href="properties.php?location=VIP" class="text-sm px-3 py-1 bg-gray-100 rounded-full hover:bg-purple-100 hover:text-purple-600 transition">📍 VIP Village</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="stat-card scroll-reveal">
                <div class="stat-number">5000+</div>
                <div class="stat-label">Happy Tenants</div>
            </div>
            <div class="stat-card scroll-reveal">
                <div class="stat-number">50+</div>
                <div class="stat-label">Properties</div>
            </div>
            <div class="stat-card scroll-reveal">
                <div class="stat-number">4.9</div>
                <div class="stat-label">⭐ Rating</div>
            </div>
            <div class="stat-card scroll-reveal">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 scroll-reveal">
            <div class="gradient-bg inline-block rounded-full px-4 py-1 mb-4">
                <span class="text-white text-sm font-semibold">✨ Why Choose Us</span>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                More than just a <span class="gradient-text">place to stay</span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                We provide everything you need for a comfortable and stylish living experience
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="feature-card scroll-reveal">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">100% Verified</h3>
                <p class="text-gray-500">All properties have been checked directly for your comfort and safety</p>
            </div>
            
            <div class="feature-card scroll-reveal">
                <div class="feature-icon">
                    <i class="fas fa-bolt text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Instant Booking</h3>
                <p class="text-gray-500">Fast & easy booking process, get confirmation within minutes</p>
            </div>
            
            <div class="feature-card scroll-reveal">
                <div class="feature-icon">
                    <i class="fas fa-headset text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">24/7 Support</h3>
                <p class="text-gray-500">Our customer service team is ready to help you anytime you need</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section id="properties" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 scroll-reveal">
            <div class="gradient-bg-pink inline-block rounded-full px-4 py-1 mb-4">
                <span class="text-white text-sm font-semibold">🔥 Hot Picks</span>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                Featured <span class="gradient-text">Properties</span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Handpicked just for you. The most popular choices among our tenants
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($featured_properties as $property): 
                // Gunakan fungsi getPropertyImage untuk mendapatkan gambar
                $property_image = getPropertyImage($property['id'], $property['name']);
                
                // Format harga untuk Elora (tampilan range)
                if ($property['id'] == 3) {
                    $price_display = "Rp 1.000.000 - Rp 1.200.000";
                } else {
                    $price_display = "Rp " . number_format($property['price_per_month'], 0, ',', '.');
                }
            ?>
            <div class="property-card scroll-reveal" onclick="window.location.href='detail.php?id=<?php echo $property['id']; ?>'">
                <div class="property-img">
                    <img src="<?php echo htmlspecialchars($property_image); ?>" 
                         alt="<?php echo htmlspecialchars($property['name']); ?>" 
                         onerror="this.src='/staynest/assets/images/default-property.jpg'">
                    <?php if($property['is_vip']): ?>
                        <div class="vip-badge"><i class="fas fa-crown mr-1"></i> VIP</div>
                    <?php endif; ?>
                    <div class="available-badge"><i class="fas fa-bed mr-1"></i> <?php echo $property['available_rooms']; ?> left</div>
                </div>
                <div class="p-5">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($property['name']); ?></h3>
                            <p class="text-gray-500 text-sm flex items-center gap-1">
                                <i class="fas fa-map-marker-alt text-purple-500"></i> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                        </div>
                        <button class="wishlist-btn text-gray-300 hover:text-red-500 transition" onclick="event.stopPropagation()">
                            <i class="far fa-heart text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mb-4 mt-3">
                        <span class="property-detail-icon"><i class="fas fa-door-open text-purple-500"></i> Total <?php echo $property['total_doors']; ?> Doors</span>
                        <span class="property-detail-icon"><i class="fas fa-bed text-purple-500"></i> <?php echo $property['available_rooms']; ?> Available</span>
                    </div>
                    
                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-2xl font-bold text-purple-600">
                                    <?php echo $price_display; ?>
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
        </div>
        
        <div class="text-center mt-12 scroll-reveal">
            <a href="properties.php" class="bg-transparent border-2 border-purple-600 text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-600 hover:text-white transition inline-flex items-center gap-2">
                View All Properties <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-gradient-to-r from-purple-50 to-pink-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 scroll-reveal">
            <div class="gradient-bg inline-block rounded-full px-4 py-1 mb-4">
                <span class="text-white text-sm font-semibold">💬 Testimonials</span>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                What <span class="gradient-text">Our Tenants Say</span>
            </h2>
            <p class="text-gray-600 text-lg">Real stories from our happy residents</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">A</div>
                    <div>
                        <h4 class="font-bold">Ahmad R.</h4>
                        <div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
                <p class="text-gray-600">"Super easy to find a boarding house with StayNest! Fast process and the place matches the photos. Highly recommended! 🔥"</p>
            </div>
            
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">S</div>
                    <div>
                        <h4 class="font-bold">Sarah M.</h4>
                        <div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
                <p class="text-gray-600">"The design is super aesthetic! Perfect for anyone looking for an instagrammable place. Thank you StayNest! ✨"</p>
            </div>
            
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">B</div>
                    <div>
                        <h4 class="font-bold">Budi P.</h4>
                        <div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
                <p class="text-gray-600">"Customer service is super responsive! They help immediately when there's an issue. Very satisfied with the service! 👏"</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-20 relative overflow-hidden">
    <div class="max-w-4xl mx-auto text-center px-6 relative z-10">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 scroll-reveal">
            Ready to Find Your New Home? 🏠
        </h2>
        <p class="text-xl text-white/90 mb-8 scroll-reveal">
            Join thousands of happy tenants who already found their cozy space with StayNest
        </p>
        <div class="scroll-reveal">
            <a href="properties.php" class="bg-white text-purple-600 px-8 py-4 rounded-full font-bold text-lg hover:shadow-xl transition transform hover:scale-105 inline-flex items-center gap-2">
                Get Started Now <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, offset: 100 });
    
    // Search functionality
    var searchInput = document.getElementById('searchInput');
    var searchBtn = document.getElementById('searchBtn');
    var resultsDiv = document.getElementById('searchResults');
    
    async function searchProperties() {
        var query = searchInput ? searchInput.value : '';
        if (query && query.length > 2) {
            try {
                var response = await fetch('api/search.php?q=' + encodeURIComponent(query));
                var data = await response.json();
                if (resultsDiv) {
                    if (data.length > 0) {
                        var html = '';
                        for (var i = 0; i < data.length; i++) {
                            var prop = data[i];
                            html += '<div class="bg-white rounded-xl shadow-lg p-4 flex gap-4 hover:shadow-xl transition cursor-pointer" onclick="location.href=\'detail.php?id=' + prop.id + '\'">';
                            html += '<img src="' + (prop.image_url || 'https://placehold.co/100x100/667eea/white?text=StayNest') + '" class="w-20 h-20 object-cover rounded-lg">';
                            html += '<div class="flex-1">';
                            html += '<h4 class="font-bold text-lg">' + prop.name + '</h4>';
                            html += '<p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> ' + prop.location + '</p>';
                            html += '<p class="text-purple-600 font-bold mt-1">Rp ' + Number(prop.price_per_month).toLocaleString('id-ID') + '/month</p>';
                            html += '</div></div>';
                        }
                        resultsDiv.innerHTML = html;
                    } else {
                        resultsDiv.innerHTML = '<p class="text-center text-gray-500 py-4">😢 No properties found</p>';
                    }
                }
            } catch(err) {
                console.error('Search error:', err);
            }
        } else if (resultsDiv && query && query.length === 0) {
            resultsDiv.innerHTML = '';
        }
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            if (searchInput && searchInput.value && searchInput.value.length > 0) {
                window.location.href = 'search.php?q=' + encodeURIComponent(searchInput.value);
            }
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', searchProperties);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && searchBtn) {
                searchBtn.click();
            }
        });
    }
    
    // Scroll reveal observer
    var scrollElements = document.querySelectorAll('.scroll-reveal');
    var observer = new IntersectionObserver(function(entries) {
        for (var i = 0; i < entries.length; i++) {
            if (entries[i].isIntersecting) {
                entries[i].target.classList.add('revealed');
            }
        }
    }, { threshold: 0.1 });
    
    for (var s = 0; s < scrollElements.length; s++) {
        observer.observe(scrollElements[s]);
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