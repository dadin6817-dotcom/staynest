<?php
// home.php - HOME PAGE (Versi berbeda dengan menu khusus)
$page_title = "Home - StayNest | Explore & Discover ✨";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Fetch all properties
try {
    $stmt = $pdo->query("SELECT * FROM properties ORDER BY is_vip DESC, id DESC");
    $all_properties = $stmt->fetchAll();
} catch(Exception $e) {
    $all_properties = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>StayNest - Home | Explore & Discover ✨</title>
    
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
        
        .hero-section {
            position: relative;
            min-height: 100vh;
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
        
        .bg-elem-1 { background: #667eea; width: 400px; height: 400px; top: -100px; right: -100px; }
        .bg-elem-2 { background: #f093fb; width: 350px; height: 350px; bottom: -80px; left: -80px; animation-delay: -5s; }
        
        @keyframes floatElement {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.2); }
            66% { transform: translate(-30px, 30px) scale(0.8); }
        }
        
        /* Navbar HOME - Berbeda dengan Beranda */
        .navbar-home {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border-bottom: 2px solid rgba(240,147,251,0.3);
        }
        
        .nav-link-home {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link-home::before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            transition: width 0.3s ease;
            border-radius: 3px;
        }
        
        .nav-link-home:hover::before,
        .nav-link-home.active::before {
            width: 100%;
        }
        
        .nav-link-home:hover { color: #f093fb; transform: translateY(-2px); }
        
        /* HOME Badge */
        .home-badge {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 8px 24px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .btn-home {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(240,147,251,0.3);
        }
        
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(240,147,251,0.4); }
        
        .btn-outline-home {
            background: transparent;
            color: #f093fb;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            border: 2px solid #f093fb;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-outline-home:hover { background: #f093fb; color: white; transform: translateY(-3px); }
        
        .property-card-home {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .property-card-home:hover { transform: translateY(-12px); box-shadow: 0 30px 50px rgba(0,0,0,0.15); }
        
        .property-img-container {
            position: relative;
            overflow: hidden;
            height: 260px;
        }
        
        .property-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .property-card-home:hover .property-img-container img { transform: scale(1.1); }
        
        .vip-badge-home {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ffd89b 0%, #c7e9fb 100%);
            color: #ff6b6b;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .available-badge-home {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(5px);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            color: #f5576c;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-up { animation: fadeInUp 0.8s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        
        @media (max-width: 768px) {
            .btn-home, .btn-outline-home { padding: 10px 20px; font-size: 14px; }
        }
    </style>
</head>
<body>

<!-- ==================== NAVBAR HOME (BERBEDA) ==================== -->
<nav class="navbar-home fixed top-0 w-full z-50 py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="home.php" class="flex items-center gap-3">
            <div class="w-10 h-10 gradient-bg-pink rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <!-- MENU HOME (Menu 2 - Berbeda) -->
        <div class="hidden md:flex items-center gap-8">
            <a href="home.php" class="nav-link-home text-gray-700 hover:text-pink-500 transition font-medium active">🏠 Home</a>
            <a href="explore.php" class="nav-link-home text-gray-700 hover:text-pink-500 transition font-medium">🔍 Explore</a>
            <a href="favorites.php" class="nav-link-home text-gray-700 hover:text-pink-500 transition font-medium">❤️ Favorites</a>
            <a href="profile.php" class="nav-link-home text-gray-700 hover:text-pink-500 transition font-medium">👤 Profile</a>
            <a href="dashboard.php" class="nav-link-home text-gray-700 hover:text-pink-500 transition font-medium">📊 Dashboard</a>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="admin/login.php" class="hidden md:block gradient-bg-pink text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition">
                <i class="fas fa-user-circle mr-1"></i> Sign In
            </a>
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700"><i class="fas fa-bars"></i></button>
        </div>
    </div>
    
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="home.php" class="px-4 py-2 hover:bg-pink-50 rounded-lg transition">🏠 Home</a>
            <a href="explore.php" class="px-4 py-2 hover:bg-pink-50 rounded-lg transition">🔍 Explore</a>
            <a href="favorites.php" class="px-4 py-2 hover:bg-pink-50 rounded-lg transition">❤️ Favorites</a>
            <a href="profile.php" class="px-4 py-2 hover:bg-pink-50 rounded-lg transition">👤 Profile</a>
            <a href="dashboard.php" class="px-4 py-2 hover:bg-pink-50 rounded-lg transition">📊 Dashboard</a>
            <a href="admin/login.php" class="px-4 py-2 gradient-bg-pink text-white rounded-lg text-center">🔐 Sign In</a>
        </div>
    </div>
</nav>

<!-- Hero Section - HOME (Tanpa Welcome, Lebih ke Discover) -->
<section class="hero-section pt-32 pb-20 px-6 relative">
    <div class="hero-bg-element bg-elem-1"></div>
    <div class="hero-bg-element bg-elem-2"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="text-center">
            <div class="home-badge mb-6 animate-fade-up">
                <i class="fas fa-compass text-pink-500"></i>
                <span class="text-gray-700 font-medium">🌟 Discover Your Next Home</span>
            </div>
            
            <!-- JUDUL BERBEDA (TANPA WELCOME) -->
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold leading-tight mb-4 animate-fade-up delay-100">
                Find Your <span class="gradient-text">Perfect Space</span>
                <br>
                <span class="text-gray-800">Start Your Journey</span>
            </h1>
            
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-4 animate-fade-up delay-200 leading-relaxed">
                <span class="gradient-text font-semibold">Discover, Explore, and Book</span> the best properties around you
            </p>
            
            <p class="text-3xl md:text-4xl font-bold text-gray-800 mb-6 animate-fade-up delay-200">
                Your next <span class="gradient-text">home is waiting</span>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-fade-up delay-300">
                <a href="explore.php" class="btn-home">
                    <i class="fas fa-compass"></i> Start Exploring
                </a>
                <a href="#" class="btn-outline-home" id="learnMoreBtn">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>
            
            <!-- Search Bar Sama -->
            <div class="max-w-2xl mx-auto animate-fade-up delay-300">
                <div class="bg-white rounded-full p-2 shadow-lg flex flex-col md:flex-row items-center gap-2">
                    <div class="flex-1 relative w-full">
                        <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchInputHome" placeholder="Search location... Babelan, Alamanda, Jakarta" class="w-full pl-14 pr-4 py-4 rounded-full outline-none">
                    </div>
                    <button id="searchBtnHome" class="bg-gradient-to-r from-pink-500 to-red-500 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition w-full md:w-auto">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                <div id="searchResultsHome" class="mt-6 space-y-3"></div>
            </div>
        </div>
    </div>
</section>

<!-- All Properties Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="gradient-bg-pink inline-block rounded-full px-4 py-1 mb-4"><span class="text-white text-sm font-semibold">🏘️ Available Now</span></div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4">All <span class="gradient-text">Properties</span></h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Check out our complete collection of boarding houses</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($all_properties as $property): ?>
            <div class="property-card-home" data-aos="fade-up">
                <div class="property-img-container">
                    <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>">
                    <?php if($property['is_vip']): ?>
                        <div class="vip-badge-home"><i class="fas fa-crown mr-1"></i> VIP</div>
                    <?php endif; ?>
                    <div class="available-badge-home"><i class="fas fa-bed mr-1"></i> <?php echo $property['available_rooms']; ?> left</div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p class="text-gray-500 text-sm mb-2"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                    <div class="flex justify-between items-center mt-4">
                        <div><p class="text-2xl font-bold text-pink-600">Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?></p><p class="text-gray-500 text-sm">/month</p></div>
                        <a href="detail.php?id=<?php echo $property['id']; ?>" class="bg-pink-500 text-white px-5 py-2 rounded-full text-sm hover:bg-pink-600 transition flex items-center gap-2">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    AOS.init({ duration: 800, once: true });
    
    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() { document.getElementById('mobileMenu').classList.toggle('hidden'); });
    
    const searchInput = document.getElementById('searchInputHome'), searchBtn = document.getElementById('searchBtnHome'), resultsDiv = document.getElementById('searchResultsHome');
    async function searchProperties() {
        const query = searchInput?.value;
        if(query?.length > 2) {
            try {
                const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                if(resultsDiv) resultsDiv.innerHTML = data.length > 0 ? data.map(prop => `<div class="bg-white rounded-xl shadow-lg p-4 flex gap-4"><img src="${prop.image_url}" class="w-20 h-20 object-cover rounded-lg"><div><h4 class="font-bold">${prop.name}</h4><p class="text-gray-500 text-sm">${prop.location}</p><p class="text-pink-600 font-bold">Rp ${Number(prop.price_per_month).toLocaleString('id-ID')}/bulan</p><a href="detail.php?id=${prop.id}" class="text-pink-500 text-sm">View →</a></div></div>`).join('') : '<p class="text-center text-gray-500 py-4">No properties found</p>';
            } catch(err) { console.error(err); }
        } else if(resultsDiv) resultsDiv.innerHTML = '';
    }
    searchBtn?.addEventListener('click', () => { if(searchInput?.value) window.location.href = `search.php?q=${encodeURIComponent(searchInput.value)}`; });
    searchInput?.addEventListener('input', searchProperties);
    
    document.getElementById('learnMoreBtn')?.addEventListener('click', (e) => { e.preventDefault(); alert('🏠 StayNest\n\nFind your perfect boarding house with ease!\n✓ Verified properties\n✓ Instant booking\n✓ 24/7 support\n\nStart your journey today! ✨'); });
</script>

</body>
</html>