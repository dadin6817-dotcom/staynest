<?php
// includes/header.php - Header dengan Menu Admin Selalu Tampil

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'user';
$page_title = $page_title ?? 'StayNest - Find Your Cozy Home';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .admin-btn {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .admin-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(240,147,251,0.4); }
        
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 50;
        }
        
        .navbar-scrolled {
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.98);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
            text-decoration: none;
            color: #4a5568;
            padding-bottom: 4px;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #667eea; }
        
        .user-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .user-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(102,126,234,0.4); }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            min-width: 220px;
            padding: 8px;
            display: none;
            z-index: 100;
        }
        
        .user-dropdown.show { display: block; animation: slideDown 0.2s ease-out; }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .user-dropdown-item {
            padding: 10px 16px;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #374151;
            text-decoration: none;
        }
        .user-dropdown-item:hover { background: #f3f4f6; }
        .user-dropdown-item i { width: 20px; color: #667eea; }
        .user-dropdown-divider { height: 1px; background: #e5e7eb; margin: 8px 0; }
        
        @media (max-width: 768px) {
            .navbar-modern { padding: 12px 16px; }
        }
    </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="navbar-modern py-4 px-6 md:px-12" id="mainNavbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Logo -->
        <a href="/staynest/welcome.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <!-- Nav Links Desktop -->
        <div class="hidden md:flex items-center gap-8">
            <a href="/staynest/welcome.php" class="nav-link <?php echo $current_page == 'welcome.php' ? 'active' : ''; ?>">
                <i class="fas fa-home mr-1"></i> Home
            </a>
            <a href="/staynest/properties.php" class="nav-link <?php echo $current_page == 'properties.php' ? 'active' : ''; ?>">
                <i class="fas fa-building mr-1"></i> Properties
            </a>
            <a href="/staynest/bookings/my_bookings.php" class="nav-link <?php echo $current_page == 'my_bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt mr-1"></i> My Bookings
            </a>
        </div>
        
        <!-- Right Side -->
        <div class="flex items-center gap-3">
            <!-- ADMIN BUTTON - SELALU TAMPIL -->
            <a href="/staynest/admin/login.php" class="admin-btn hidden md:flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium hover:shadow-lg transition">
                <i class="fas fa-user-shield"></i> Admin
            </a>
            
            <?php if ($is_logged_in): ?>
                <div class="relative" id="userMenuContainer">
                    <button id="userMenuBtn" class="user-btn px-4 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="/staynest/profile.php" class="user-dropdown-item"><i class="fas fa-user"></i> My Profile</a>
                        <a href="/staynest/bookings/my_bookings.php" class="user-dropdown-item"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <div class="user-dropdown-divider"></div>
                        <a href="/staynest/admin/login.php" class="user-dropdown-item"><i class="fas fa-user-shield text-pink-500"></i> Admin Panel</a>
                        <div class="user-dropdown-divider"></div>
                        <a href="/staynest/logout.php" class="user-dropdown-item text-red-600"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/staynest/login.php" class="user-btn px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/staynest/register.php" class="border-2 border-purple-600 text-purple-600 px-5 py-2 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
            
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="/staynest/welcome.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">
                <i class="fas fa-home mr-2 text-purple-600"></i> Home
            </a>
            <a href="/staynest/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">
                <i class="fas fa-building mr-2 text-purple-600"></i> Properties
            </a>
            <a href="/staynest/bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">
                <i class="fas fa-calendar-alt mr-2 text-purple-600"></i> My Bookings
            </a>
            
            <!-- ADMIN MOBILE - SELALU TAMPIL -->
            <a href="/staynest/admin/login.php" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-lg text-center">
                <i class="fas fa-user-shield mr-2"></i> Admin Panel
            </a>
            
            <?php if ($is_logged_in): ?>
                <a href="/staynest/profile.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">
                    <i class="fas fa-user mr-2 text-purple-600"></i> Profile
                </a>
                <a href="/staynest/logout.php" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            <?php else: ?>
                <a href="/staynest/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="/staynest/register.php" class="px-4 py-2 border-2 border-purple-600 text-purple-600 rounded-lg text-center">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Spacer untuk fixed navbar -->
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

// User dropdown toggle
var userMenuBtn = document.getElementById('userMenuBtn');
var userDropdown = document.getElementById('userDropdown');
if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
    document.addEventListener('click', function(e) {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
}
</script>