<?php
// landing.php - LANDING PAGE (Halaman Utama StayNest)
$page_title = "StayNest - Find Your Cozy Home | Landing Page";

require_once dirname(__FILE__) . '/config/database.php';

// Fetch featured properties
try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY is_vip DESC, id DESC LIMIT 9");
    $featured_properties = $stmt->fetchAll();
} catch(Exception $e) {
    $featured_properties = [];
}

// Fetch statistics
try {
    $total_properties = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $avg_rating = 4.9;
} catch(Exception $e) {
    $total_properties = 0;
    $total_bookings = 0;
    $avg_rating = 4.9;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>StayNest - Find Your Cozy Home | Landing Page ✨</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; overflow-x: hidden; }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-bg-pink { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        
        /* Hero Section Landing */
        .landing-hero {
            position: relative;
            min-height: 90vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2ff 40%, #fdf2f8 100%);
        }
        
        /* Floating Elements */
        .floating-element {
            position: absolute;
            pointer-events: none;
            opacity: 0.1;
            font-size: 150px;
            animation: floatElement 20s ease-in-out infinite;
        }
        
        .float-1 { top: 10%; left: 5%; animation-delay: 0s; }
        .float-2 { bottom: 15%; right: 8%; animation-delay: -5s; }
        .float-3 { top: 40%; right: 20%; animation-delay: -10s; font-size: 100px; }
        
        @keyframes floatElement {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(10deg); }
            66% { transform: translate(-20px, 20px) rotate(-10deg); }
        }
        
        /* Navbar Landing */
        .navbar-landing {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.98);
        }
        
        .nav-link-landing {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link-landing::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .nav-link-landing:hover::after,
        .nav-link-landing.active::after {
            width: 100%;
        }
        
        .nav-link-landing:hover { color: #667eea; transform: translateY(-2px); }
        
        /* Search Box */
        .search-box {
            background: white;
            border-radius: 80px;
            padding: 8px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .search-box:focus-within {
            box-shadow: 0 20px 40px rgba(102,126,234,0.2);
            transform: scale(1.02);
        }
        
        /* Category Card */
        .category-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s ease;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .category-card:hover .category-icon,
        .category-card:hover .category-title,
        .category-card:hover .category-count {
            color: white !important;
        }
        
        .category-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }
        
        /* Property Card */
        .property-card-landing {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .property-card-landing:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 50px rgba(0,0,0,0.15);
        }
        
        .property-img {
            position: relative;
            overflow: hidden;
            height: 240px;
        }
        
        .property-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .property-card-landing:hover .property-img img {
            transform: scale(1.1);
        }
        
        .badge-vip {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ffd89b, #c7e9fb);
            color: #ff6b6b;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-available {
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
        }
        
        /* Testimonial Card */
        .testimonial-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            transition: all 0.4s ease;
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
            font-size: 300px;
            opacity: 0.1;
            bottom: -80px;
            right: -80px;
            transform: rotate(-15deg);
        }
        
        .cta-section::after {
            content: '✨';
            position: absolute;
            font-size: 200px;
            opacity: 0.1;
            top: -60px;
            left: -60px;
            transform: rotate(10deg);
        }
        
        /* Scroll Reveal */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
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
        
        @media (max-width: 768px) {
            .search-box { padding: 5px; }
            .category-card { padding: 1rem; }
            .testimonial-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<!-- Navbar Landing -->
<nav class="navbar-landing fixed top-0 w-full z-50 py-4 px-6 md:px-12 transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="landing.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <div class="hidden md:flex items-center gap-8">
            <a href="landing.php" class="nav-link-landing text-gray-700 hover:text-purple-600 transition font-medium active">Home</a>
            <a href="properties.php" class="nav-link-landing text-gray-700 hover:text-purple-600 transition font-medium">Properties</a>
            <a href="bookings/my_bookings.php" class="nav-link-landing text-gray-700 hover:text-purple-600 transition font-medium">My Bookings</a>
            <a href="#contact" class="nav-link-landing text-gray-700 hover:text-purple-600 transition font-medium">Contact</a>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="admin/login.php" class="hidden md:block gradient-bg text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition">
                <i class="fas fa-user-circle mr-1"></i> Sign In
            </a>
            <a href="welcome.php" class="hidden md:block btn-outline-welcome px-4 py-2 rounded-full text-sm font-medium">
                <i class="fas fa-gift mr-1"></i> Welcome
            </a>
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="landing.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a>
            <a href="properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a>
            <a href="bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Bookings</a>
            <a href="admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Sign In</a>
            <a href="welcome.php" class="px-4 py-2 border border-purple-500 text-purple-600 rounded-lg text-center">Welcome Page</a>
        </div>
    </div>
</nav>

<!-- Hero Section Landing -->
<section class="landing-hero pt-32 pb-20 px-6 relative">
    <div class="floating-element float-1">🏠</div>
    <div class="floating-element float-2">✨</div>
    <div class="floating-element float-3">🏘️</div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 bg-white/60 backdrop-blur-sm rounded-full px-5 py-2 mb-6 animate-fade-up">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-gray-700 font-medium text-sm">✨ 5000+ Gen Z sudah menemukan rumah impian</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold leading-tight mb-6 animate-fade-up delay-100">
                Find a place you'll
                <span class="gradient-text">love to call home</span>
            </h1>
            
            <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-8 animate-fade-up delay-200 leading-relaxed">
                Discover cozy spaces that match your lifestyle. Modern, affordable, and totally instagrammable.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10 animate-fade-up delay-300">
                <a href="properties.php" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-xl transition transform hover:scale-105 inline-flex items-center gap-2">
                    <i class="fas fa-search"></i> Explore Now
                </a>
                <a href="welcome.php" class="bg-transparent border-2 border-purple-600 text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-600 hover:text-white transition inline-flex items-center gap-2">
                    <i class="fas fa-gift"></i> Welcome Offer
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="max-w-2xl mx-auto animate-fade-up delay-400">
                <div class="search-box">
                    <div class="flex flex-col md:flex-row items-center gap-2">
                        <div class="flex-1 relative w-full">
                            <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="searchInputLanding" placeholder="Cari lokasi... Babelan, Alamanda, Jakarta" class="w-full pl-14 pr-4 py-4 rounded-full outline-none bg-transparent">
                        </div>
                        <button id="searchBtnLanding" class="gradient-bg text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition w-full md:w-auto">
                            <i class="fas fa-arrow-right mr-2"></i> Search
                        </button>
                    </div>
                </div>
                <div id="searchResultsLanding" class="mt-6 space-y-3"></div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="scroll-reveal">
                <div class="text-3xl md:text-4xl font-bold gradient-text">5000+</div>
                <p class="text-gray-500 mt-2">Happy Tenants</p>
            </div>
            <div class="scroll-reveal">
                <div class="text-3xl md:text-4xl font-bold gradient-text"><?php echo $total_properties; ?>+</div>
                <p class="text-gray-500 mt-2">Properties</p>
            </div>
            <div class="scroll-reveal">
                <div class="text-3xl md:text-4xl font-bold gradient-text">4.9</div>
                <p class="text-gray-500 mt-2">⭐ Rating</p>
            </div>
            <div class="scroll-reveal">
                <div class="text-3xl md:text-4xl font-bold gradient-text"><?php echo $total_bookings; ?>+</div>
                <p class="text-gray-500 mt-2">Bookings</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 scroll-reveal">
            <div class="gradient-bg inline-block rounded-full px-4 py-1 mb-4">
                <span class="text-white text-sm font-semibold">🏷️ Browse by Category</span>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Explore by <span class="gradient-text">Location</span></h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Find the perfect boarding house in your favorite area</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="category-card scroll-reveal" onclick="location.href='properties.php?location=Babelan'">
                <div class="category-icon mx-auto"><i class="fas fa-map-marker-alt text-2xl text-purple-600"></i></div>
                <h3 class="category-title font-bold mt-3">Babelan</h3>
                <p class="category-count text-xs text-gray-500">4 Properties</p>
            </div>
            <div class="category-card scroll-reveal" onclick="location.href='properties.php?location=Alamanda'">
                <div class="category-icon mx-auto"><i class="fas fa-map-marker-alt text-2xl text-purple-600"></i></div>
                <h3 class="category-title font-bold mt-3">Alamanda</h3>
                <p class="category-count text-xs text-gray-500">12 Properties</p>
            </div>
            <div class="category-card scroll-reveal" onclick="location.href='properties.php?location=Jakarta'">
                <div class="category-icon mx-auto"><i class="fas fa-map-marker-alt text-2xl text-purple-600"></i></div>
                <h3 class="category-title font-bold mt-3">Jakarta</h3>
                <p class="category-count text-xs text-gray-500">8 Properties</p>
            </div>
            <div class="category-card scroll-reveal" onclick="location.href='properties.php?location=Depok'">
                <div class="category-icon mx-auto"><i class="fas fa-map-marker-alt text-2xl text-purple-600"></i></div>
                <h3 class="category-title font-bold mt-3">Depok</h3>
                <p class="category-count text-xs text-gray-500">6 Properties</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 scroll-reveal">
            <div class="gradient-bg-pink inline-block rounded-full px-4 py-1 mb-4">
                <span class="text-white text-sm font-semibold">🔥 Hot Picks</span>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Featured <span class="gradient-text">Properties</span></h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Handpicked just for you. The most popular choices among Gen Z</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($featured_properties as $property): ?>
            <div class="property-card-landing scroll-reveal">
                <div class="property-img">
                    <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>">
                    <?php if($property['is_vip']): ?>
                        <div class="badge-vip"><i class="fas fa-crown mr-1"></i> VIP</div>
                    <?php endif; ?>
                    <div class="badge-available"><i class="fas fa-bed mr-1"></i> <?php echo $property['available_rooms']; ?> left</div>
                </div>
                <div class="p-5">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($property['name']); ?></h3>
                            <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                        </div>
                        <i class="far fa-heart text-gray-300 hover:text-red-500 cursor-pointer transition text-xl"></i>
                    </div>
                    <div class="flex gap-2 mb-3">
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">Total <?php echo $property['total_doors']; ?> Pintu</span>
                        <span class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded"><?php echo $property['available_rooms']; ?> Kosong</span>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <div>
                            <p class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?></p>
                            <p class="text-gray-500 text-xs">/bulan</p>
                        </div>
                        <a href="detail.php?id=<?php echo $property['id']; ?>" class="gradient-bg text-white px-4 py-2 rounded-full text-sm hover:shadow-lg transition flex items-center gap-1">
                            Detail <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12 scroll-reveal">
            <a href="properties.php" class="inline-flex items-center gap-2 bg-transparent border-2 border-purple-600 text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-600 hover:text-white transition">
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
            <h2 class="text-4xl md:text-5xl font-bold mb-4">What <span class="gradient-text">Gen Z Says</span></h2>
            <p class="text-gray-600 text-lg">Real stories from our happy tenants</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">A</div>
                    <div><h4 class="font-bold">Ahmad R.</h4><div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div></div>
                </div>
                <p class="text-gray-600">"Gampang banget cari kontrakan lewat StayNest! Prosesnya cepat dan tempatnya sesuai sama foto. Recommended banget! 🔥"</p>
            </div>
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">S</div>
                    <div><h4 class="font-bold">Sarah M.</h4><div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div></div>
                </div>
                <p class="text-gray-600">"Desainnya aesthetic banget! Cocok buat anak muda yang pengen tempat tinggal instagramable. Makasih StayNest! ✨"</p>
            </div>
            <div class="testimonial-card scroll-reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-xl">B</div>
                    <div><h4 class="font-bold">Budi P.</h4><div class="text-yellow-400 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div></div>
                </div>
                <p class="text-gray-600">"Customer service responsif banget! Langsung dibantu kalau ada masalah. Puas banget sama pelayanannya! 👏"</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-20 relative overflow-hidden">
    <div class="max-w-4xl mx-auto text-center px-6 relative z-10">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 scroll-reveal">Ready to Find Your New Home? 🏠</h2>
        <p class="text-xl text-white/90 mb-8 scroll-reveal">Join thousands of Gen Z who already found their cozy space with StayNest</p>
        <div class="scroll-reveal">
            <a href="welcome.php" class="bg-white text-purple-600 px-8 py-4 rounded-full font-bold text-lg hover:shadow-xl transition transform hover:scale-105 inline-flex items-center gap-2">
                Get Started Now <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center"><i class="fas fa-home text-white"></i></div>
                    <span class="text-xl font-bold">StayNest</span>
                </div>
                <p class="text-gray-400">Find your perfect place to call home. Cozy spaces that match your lifestyle.</p>
            </div>
            <div><h3 class="font-bold text-lg mb-4">Quick Links</h3><ul class="space-y-2 text-gray-400"><li><a href="properties.php" class="hover:text-white transition">Browse Properties</a></li><li><a href="bookings/my_bookings.php" class="hover:text-white transition">My Bookings</a></li><li><a href="welcome.php" class="hover:text-white transition">Welcome Page</a></li></ul></div>
            <div><h3 class="font-bold text-lg mb-4">Contact</h3><ul class="space-y-2 text-gray-400"><li><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</li><li><i class="fas fa-phone mr-2"></i> +62 812 3456 7890</li><li><i class="fas fa-envelope mr-2"></i> hello@staynest.com</li></ul></div>
            <div><h3 class="font-bold text-lg mb-4">Follow Us</h3><div class="flex gap-4"><a href="#" class="text-2xl text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a><a href="#" class="text-2xl text-gray-400 hover:text-white transition"><i class="fab fa-tiktok"></i></a><a href="#" class="text-2xl text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a></div></div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-gray-400"><p>&copy; 2026 StayNest. All rights reserved. #GenZLiving</p></div>
    </div>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, offset: 100 });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Mobile menu toggle
    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInputLanding');
    const searchBtn = document.getElementById('searchBtnLanding');
    const resultsDiv = document.getElementById('searchResultsLanding');
    
    async function searchProperties() {
        const query = searchInput?.value;
        if(query?.length > 2) {
            try {
                const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                if(resultsDiv) {
                    resultsDiv.innerHTML = data.length > 0 ? data.map(prop => `
                        <div class="bg-white rounded-xl shadow-lg p-4 flex gap-4 hover:shadow-xl transition cursor-pointer" onclick="location.href='detail.php?id=${prop.id}'">
                            <img src="${prop.image_url}" class="w-20 h-20 object-cover rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-bold text-lg">${prop.name}</h4>
                                <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> ${prop.location}</p>
                                <p class="text-purple-600 font-bold mt-1">Rp ${Number(prop.price_per_month).toLocaleString('id-ID')}/bulan</p>
                            </div>
                        </div>
                    `).join('') : '<p class="text-center text-gray-500 py-4">😢 Tidak ada properti ditemukan</p>';
                }
            } catch(err) { console.error(err); }
        } else if(resultsDiv) resultsDiv.innerHTML = '';
    }
    
    searchBtn?.addEventListener('click', () => { if(searchInput?.value) window.location.href = `search.php?q=${encodeURIComponent(searchInput.value)}`; });
    searchInput?.addEventListener('input', searchProperties);
    searchInput?.addEventListener('keypress', (e) => { if(e.key === 'Enter') searchBtn?.click(); });
    
    // Scroll reveal observer
    const scrollElements = document.querySelectorAll('.scroll-reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('revealed'); });
    }, { threshold: 0.1 });
    scrollElements.forEach(el => observer.observe(el));
</script>

</body>
</html>