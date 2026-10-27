<?php
// includes/header.php - Navbar dengan Active State yang Benar

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'user';
$page_title = $page_title ?? 'StayNest';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .gradient-text { background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
        .nav-link { position: relative; padding-bottom: 4px; transition: all 0.3s ease; text-decoration: none; color: #4a5568; font-weight: 500; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: linear-gradient(135deg, #667eea, #764ba2); transition: width 0.3s ease; border-radius: 2px; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #667eea; }
        .nav-link.active { color: #667eea; }
        .user-btn { background: linear-gradient(135deg, #667eea, #764ba2); transition: all 0.3s ease; text-decoration: none; color: white; }
        .user-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(102,126,234,0.4); }
    </style>
</head>
<body>

<nav class="bg-white/95 backdrop-blur-md shadow-md fixed top-0 w-full z-50 py-4 px-6">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Logo -->
        <a href="/staynest/welcome.php" class="flex items-center gap-3">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <!-- Nav Links Desktop -->
        <div class="hidden md:flex items-center gap-8">
            <!-- MENU WELCOME -->
            <a href="/staynest/welcome.php" class="nav-link <?php echo ($current_page == 'welcome.php') ? 'active' : ''; ?>">
                <i class="fas fa-star mr-1"></i> Welcome
            </a>
            
            <!-- MENU HOME -->
            <a href="/staynest/index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-home mr-1"></i> Home
            </a>
            
            <!-- MENU PROPERTIES -->
            <a href="/staynest/properties.php" class="nav-link <?php echo ($current_page == 'properties.php') ? 'active' : ''; ?>">
                <i class="fas fa-building mr-1"></i> Properties
            </a>
            
            <!-- MENU MY BOOKINGS -->
            <a href="/staynest/bookings/my_bookings.php" class="nav-link <?php echo ($current_page == 'my_bookings.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt mr-1"></i> My Bookings
            </a>
        </div>
        
        <!-- Right Side -->
        <div class="flex items-center gap-3">
            <a href="/staynest/admin/login.php" class="hidden md:flex items-center gap-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2 rounded-full text-sm font-medium hover:shadow-lg transition">
                <i class="fas fa-user-shield"></i> Admin
            </a>
            
            <?php if ($is_logged_in): ?>
                <div class="flex items-center gap-2">
                    <span class="hidden md:flex items-center gap-2 text-sm font-medium text-gray-700">
                        <i class="fas fa-user-circle text-purple-600"></i> 
                        <?php echo htmlspecialchars($user_name); ?>
                    </span>
                    <a href="/staynest/logout.php" class="text-red-600 hover:text-red-800 text-sm font-medium">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="/staynest/login.php" class="user-btn px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/staynest/register.php" class="border-2 border-purple-600 text-purple-600 px-5 py-2 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
            
            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="/staynest/welcome.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition <?php echo ($current_page == 'welcome.php') ? 'bg-purple-50 text-purple-600' : ''; ?>">
                <i class="fas fa-star mr-2"></i> Welcome
            </a>
            <a href="/staynest/index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition <?php echo ($current_page == 'index.php') ? 'bg-purple-50 text-purple-600' : ''; ?>">
                <i class="fas fa-home mr-2"></i> Home
            </a>
            <a href="/staynest/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition <?php echo ($current_page == 'properties.php') ? 'bg-purple-50 text-purple-600' : ''; ?>">
                <i class="fas fa-building mr-2"></i> Properties
            </a>
            <a href="/staynest/bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition <?php echo ($current_page == 'my_bookings.php') ? 'bg-purple-50 text-purple-600' : ''; ?>">
                <i class="fas fa-calendar-alt mr-2"></i> My Bookings
            </a>
            
            <a href="/staynest/admin/login.php" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-lg text-center mt-2">
                <i class="fas fa-user-shield mr-2"></i> Admin Panel
            </a>
            
            <?php if ($is_logged_in): ?>
                <a href="/staynest/profile.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">My Profile</a>
                <a href="/staynest/logout.php" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">Logout</a>
            <?php else: ?>
                <a href="/staynest/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Login</a>
                <a href="/staynest/register.php" class="px-4 py-2 border-2 border-purple-600 text-purple-600 rounded-lg text-center">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Spacer -->
<div style="height: 80px;"></div>

<script>
window.addEventListener('scroll', function() {
    var navbar = document.getElementById('mainNavbar');
    if (navbar) {
        if (window.scrollY > 50) navbar.classList.add('navbar-scrolled');
        else navbar.classList.remove('navbar-scrolled');
    }
});

var mobileMenuBtn = document.getElementById('mobileMenuBtn');
var mobileMenu = document.getElementById('mobileMenu');
if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
    });
}
</script>