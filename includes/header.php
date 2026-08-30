<?php
// includes/header.php - Navbar Global dengan User Login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'user';

$page_title = $page_title ?? 'StayNest - Find Your Cozy Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Style navbar seperti di file yang diberikan */
    </style>
</head>
<body>

<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12" id="mainNavbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Logo -->
        <a href="/staynest/index.php" class="flex items-center gap-3">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <!-- Menu -->
        <div class="hidden md:flex items-center gap-8">
            <a href="/staynest/index.php" class="nav-link text-gray-700">Home</a>
            <a href="/staynest/properties.php" class="nav-link text-gray-700">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="nav-link text-gray-700">My Bookings</a>
        </div>
        
        <!-- Right Menu -->
        <div class="flex items-center gap-3">
            <!-- Admin Button -->
            <a href="/staynest/admin/login.php" class="admin-btn text-white px-5 py-2 rounded-full text-sm font-medium">
                <i class="fas fa-user-shield"></i> Admin
            </a>
            
            <!-- User Login / Profile -->
            <?php if($is_logged_in): ?>
                <!-- User Profile Dropdown -->
                <div class="relative" id="userMenuContainer">
                    <button id="userMenuBtn" class="user-btn text-white px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="/staynest/profile.php" class="user-dropdown-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="/staynest/bookings/my_bookings.php" class="user-dropdown-item">
                            <i class="fas fa-calendar-check"></i> My Bookings
                        </a>
                        <?php if($user_role == 'admin'): ?>
                            <a href="/staynest/admin/index.php" class="user-dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                            </a>
                        <?php endif; ?>
                        <div class="user-dropdown-divider"></div>
                        <a href="/staynest/logout.php" class="user-dropdown-item text-red-600">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login & Register Buttons -->
                <a href="/staynest/login.php" class="user-btn text-white px-5 py-2 rounded-full text-sm font-medium">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/staynest/register.php" class="bg-transparent border-2 border-purple-600 text-purple-600 px-5 py-2 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition">
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
            <a href="/staynest/index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a>
            <a href="/staynest/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Bookings</a>
            <?php if($is_logged_in): ?>
                <a href="/staynest/profile.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Profile</a>
                <a href="/staynest/logout.php" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Logout</a>
            <?php else: ?>
                <a href="/staynest/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Login</a>
                <a href="/staynest/register.php" class="px-4 py-2 border-2 border-purple-600 text-purple-600 rounded-lg text-center">Register</a>
            <?php endif; ?>
            <a href="/staynest/admin/login.php" class="px-4 py-2 admin-btn text-white rounded-lg text-center">Admin Panel</a>
        </div>
    </div>
</nav>

<!-- Spacer -->
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