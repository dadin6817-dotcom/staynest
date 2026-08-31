<?php
// includes/header.php - Navbar Global dengan User Login + Music Player

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'user';

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
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        
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
            text-decoration: none;
            color: #4a5568;
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
            text-decoration: none;
        }
        
        .admin-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(240,147,251,0.4); }
        
        .user-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            text-decoration: none;
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
        
        .user-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }
        
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
        .user-dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 0;
        }
        
        #musicToggleBtn { transition: all 0.3s ease; }
        #musicToggleBtn:hover { background: rgba(102, 126, 234, 0.1); }
        
        /* Music Player Styles */
        #musicControls {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        #musicToggle {
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }
        
        #musicToggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 40px rgba(102, 126, 234, 0.5);
        }
        
        @media (max-width: 768px) {
            .navbar-modern { padding: 12px 16px; }
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- NAVBAR -->
<!-- ========================================== -->
<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12" id="mainNavbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        
        <!-- Logo -->
        <a href="/staynest/index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center gap-8">
            <a href="/staynest/index.php" class="nav-link hover:text-purple-600 transition font-medium" id="navHome">Home</a>
            <a href="/staynest/properties.php" class="nav-link hover:text-purple-600 transition font-medium" id="navProperties">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="nav-link hover:text-purple-600 transition font-medium" id="navBookings">My Bookings</a>
        </div>
        
        <!-- Right Menu -->
        <div class="flex items-center gap-3">
            
            <!-- Admin Button -->
            <a href="/staynest/admin/login.php" class="hidden md:block admin-btn text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
            
            <!-- Music Button -->
            <button id="musicToggleBtn" class="hidden md:flex text-gray-700 hover:text-purple-600 transition text-xl p-2 rounded-full hover:bg-purple-50 items-center gap-2">
                <i class="fas fa-music"></i>
                <span class="text-sm font-medium" id="musicStatus">Off</span>
            </button>
            
            <!-- User Login / Profile -->
            <?php if($is_logged_in): ?>
                <div class="relative" id="userMenuContainer">
                    <button id="userMenuBtn" class="user-btn text-white px-4 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="/staynest/profile.php" class="user-dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="/staynest/bookings/my_bookings.php" class="user-dropdown-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>My Bookings</span>
                        </a>
                        <?php if($user_role == 'admin'): ?>
                            <a href="/staynest/admin/index.php" class="user-dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        <?php endif; ?>
                        <div class="user-dropdown-divider"></div>
                        <a href="/staynest/logout.php" class="user-dropdown-item text-red-600">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/staynest/login.php" class="user-btn text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                <a href="/staynest/register.php" class="bg-transparent border-2 border-purple-600 text-purple-600 px-5 py-2 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    <span>Register</span>
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
            <a href="/staynest/index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">Home</a>
            <a href="/staynest/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">My Bookings</a>
            <button id="musicToggleMobile" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition text-left flex items-center gap-2">
                <i class="fas fa-music text-purple-600"></i>
                <span id="musicStatusMobile">Music: Off</span>
            </button>
            <?php if($is_logged_in): ?>
                <a href="/staynest/profile.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg transition">My Profile</a>
                <a href="/staynest/logout.php" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">Logout</a>
            <?php else: ?>
                <a href="/staynest/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center transition">Login</a>
                <a href="/staynest/register.php" class="px-4 py-2 border-2 border-purple-600 text-purple-600 rounded-lg text-center transition">Register</a>
            <?php endif; ?>
            <a href="/staynest/admin/login.php" class="px-4 py-2 admin-btn text-white rounded-lg text-center transition">Admin Panel</a>
        </div>
    </div>
</nav>

<!-- Spacer -->
<div style="height: 80px;"></div>

<!-- ========================================== -->
<!-- MUSIC PLAYER FLOATING -->
<!-- ========================================== -->
<div id="musicPlayer" class="fixed bottom-6 right-6 z-50">
    <button id="musicToggle" class="w-14 h-14 rounded-full shadow-lg hover:shadow-xl transition transform hover:scale-110 flex items-center justify-center" style="background: linear-gradient(135deg, #667eea, #764ba2);">
        <i class="fas fa-music text-white text-xl"></i>
    </button>
    
    <div id="musicControls" class="hidden absolute bottom-20 right-0 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-5 w-72 border border-white/20">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-headphones text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">StayNest Radio</p>
                <p class="text-xs text-gray-400">Relaxing vibes 🎵</p>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                <div id="progressBar" class="h-full rounded-full" style="width: 0%; background: linear-gradient(135deg, #667eea, #764ba2);"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span id="currentTime">0:00</span>
                <span id="totalTime">3:45</span>
            </div>
        </div>
        
        <div class="flex items-center justify-between">
            <button id="prevBtn" class="text-gray-500 hover:text-purple-600 transition text-lg">
                <i class="fas fa-step-backward"></i>
            </button>
            <button id="playBtn" class="w-12 h-12 rounded-full flex items-center justify-center text-white transition transform hover:scale-105" style="background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 4px 16px rgba(102,126,234,0.3);">
                <i class="fas fa-play text-lg"></i>
            </button>
            <button id="nextBtn" class="text-gray-500 hover:text-purple-600 transition text-lg">
                <i class="fas fa-step-forward"></i>
            </button>
            <button id="volumeBtn" class="text-gray-500 hover:text-purple-600 transition text-lg">
                <i class="fas fa-volume-up"></i>
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS -->
<!-- ========================================== -->
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
    
    // ==========================================
    // MUSIC PLAYER - FULL SCRIPT
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const musicToggle = document.getElementById('musicToggle');
        const musicToggleBtn = document.getElementById('musicToggleBtn');
        const musicToggleMobile = document.getElementById('musicToggleMobile');
        const musicControls = document.getElementById('musicControls');
        const playBtn = document.getElementById('playBtn');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const volumeBtn = document.getElementById('volumeBtn');
        const progressBar = document.getElementById('progressBar');
        const currentTime = document.getElementById('currentTime');
        const totalTime = document.getElementById('totalTime');
        const musicStatus = document.getElementById('musicStatus');
        const musicStatusMobile = document.getElementById('musicStatusMobile');
        
        let isPlaying = false;
        let progress = 0;
        let progressInterval = null;
        
        // Toggle music controls
        function toggleControls(e) {
            if (e) e.stopPropagation();
            musicControls.classList.toggle('hidden');
        }
        
        if (musicToggle) musicToggle.addEventListener('click', toggleControls);
        if (musicToggleBtn) musicToggleBtn.addEventListener('click', toggleControls);
        if (musicToggleMobile) musicToggleMobile.addEventListener('click', toggleControls);
        
        // Close controls when clicking outside
        document.addEventListener('click', function(e) {
            if (musicControls && !musicControls.classList.contains('hidden')) {
                if (!musicControls.contains(e.target) && 
                    !musicToggle.contains(e.target) && 
                    !musicToggleBtn?.contains(e.target) &&
                    !musicToggleMobile?.contains(e.target)) {
                    musicControls.classList.add('hidden');
                }
            }
        });
        
        // Play/Pause
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                isPlaying = !isPlaying;
                const icon = this.querySelector('i');
                
                if (isPlaying) {
                    icon.className = 'fas fa-pause text-lg';
                    this.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                    if (musicStatus) {
                        musicStatus.textContent = 'On';
                        musicStatus.style.color = '#667eea';
                    }
                    if (musicStatusMobile) {
                        musicStatusMobile.textContent = 'Music: On';
                    }
                    simulateProgress();
                } else {
                    icon.className = 'fas fa-play text-lg';
                    this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    if (musicStatus) {
                        musicStatus.textContent = 'Off';
                        musicStatus.style.color = 'gray';
                    }
                    if (musicStatusMobile) {
                        musicStatusMobile.textContent = 'Music: Off';
                    }
                    if (progressInterval) {
                        clearTimeout(progressInterval);
                        progressInterval = null;
                    }
                }
            });
        }
        
        // Simulate progress
        function simulateProgress() {
            if (!isPlaying) return;
            
            if (progress >= 100) {
                progress = 0;
                if (playBtn) {
                    const icon = playBtn.querySelector('i');
                    icon.className = 'fas fa-play text-lg';
                    playBtn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    isPlaying = false;
                    if (musicStatus) {
                        musicStatus.textContent = 'Off';
                        musicStatus.style.color = 'gray';
                    }
                    if (musicStatusMobile) {
                        musicStatusMobile.textContent = 'Music: Off';
                    }
                }
                return;
            }
            
            progress += 0.5;
            if (progressBar) progressBar.style.width = progress + '%';
            
            // Update time
            if (currentTime) {
                const totalSeconds = 225;
                const currentSeconds = Math.floor((progress / 100) * totalSeconds);
                const mins = Math.floor(currentSeconds / 60);
                const secs = currentSeconds % 60;
                currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
            
            progressInterval = setTimeout(simulateProgress, 100);
        }
        
        // Previous button
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                progress = Math.max(0, progress - 10);
                if (progressBar) progressBar.style.width = progress + '%';
                updateTime();
            });
        }
        
        // Next button
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                progress = Math.min(100, progress + 10);
                if (progressBar) progressBar.style.width = progress + '%';
                updateTime();
            });
        }
        
        // Update time function
        function updateTime() {
            if (currentTime) {
                const totalSeconds = 225;
                const currentSeconds = Math.floor((progress / 100) * totalSeconds);
                const mins = Math.floor(currentSeconds / 60);
                const secs = currentSeconds % 60;
                currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
        }
        
        // Volume button
        if (volumeBtn) {
            volumeBtn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon.className.includes('volume-up')) {
                    icon.className = 'fas fa-volume-mute';
                } else {
                    icon.className = 'fas fa-volume-up';
                }
            });
        }
        
        // Set total time
        if (totalTime) {
            totalTime.textContent = '3:45';
        }
    });
</script>