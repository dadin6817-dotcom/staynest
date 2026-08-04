<?php
// includes/header.php - Navbar Global dengan Music Player (NO JEDA)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'StayNest - Find Your Cozy Home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Gen Z Music Player (Stay Alive antar halaman - NO JEDA) -->
    <script src="/assets/js/music-player.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow-x: hidden; }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled {
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.98);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
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
        
        .admin-btn {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            transition: all 0.3s ease;
        }
        
        .admin-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(240,147,251,0.4); }
        
        @media (max-width: 768px) { .navbar-modern { padding: 12px 16px; } }
    </style>
</head>
<body>

<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12" id="mainNavbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="/index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <div class="hidden md:flex items-center gap-8">
            <a href="/index.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium" id="navHome">Home</a>
            <a href="/properties.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium" id="navProperties">Properties</a>
            <a href="/bookings/my_bookings.php" class="nav-link text-gray-700 hover:text-purple-600 transition font-medium" id="navBookings">My Bookings</a>
            <a href="/admin/login.php" class="admin-btn text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-user-shield"></i> <span>Admin</span>
            </a>
        </div>
        
        <div class="flex items-center gap-3 md:hidden">
            <a href="/admin/login.php" class="admin-btn text-white px-3 py-1.5 rounded-full text-xs font-medium">
                <i class="fas fa-user-shield"></i>
            </a>
            <button id="mobileMenuBtn" class="text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="/index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a>
            <a href="/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a>
            <a href="/bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Bookings</a>
            <a href="/admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Admin Panel</a>
        </div>
    </div>
</nav>

<div style="height: 80px;"></div>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        var navbar = document.getElementById('mainNavbar');
        if (navbar) {
            if (window.scrollY > 50) navbar.classList.add('navbar-scrolled');
            else navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Mobile menu toggle
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Set active nav link
    var currentPath = window.location.pathname;
    var navHome = document.getElementById('navHome');
    var navProperties = document.getElementById('navProperties');
    var navBookings = document.getElementById('navBookings');
    
    if (navHome && (currentPath.includes('index.php') || currentPath === '/' || currentPath === '/index.php')) navHome.classList.add('active');
    if (navProperties && currentPath.includes('properties.php')) navProperties.classList.add('active');
    if (navBookings && currentPath.includes('my_bookings.php')) navBookings.classList.add('active');
</script>
