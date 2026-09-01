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
    
    <!-- ========================================== -->
    <!-- MUSIC PLAYER JS - LOAD DI SEMUA HALAMAN -->
    <!-- ========================================== -->
    <script src="/staynest/assets/js/music-player.js"></script>
    
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
        
        /* Music Button di Navbar */
        #musicToggleBtn {
            transition: all 0.3s ease;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        #musicToggleBtn:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.05);
        }
        
        /* Music Player Styles */
        #musicPlayerContainer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 9998;
        }
        
        #musicToggle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        #musicToggle:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 35px rgba(102,126,234,0.6);
        }
        
        #musicToggle .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(102,126,234,0.3);
            animation: pulseRing 1.5s ease-in-out infinite;
            display: none;
        }
        
        #musicToggle .pulse-ring.active {
            display: block;
        }
        
        @keyframes pulseRing {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.3); opacity: 0.1; }
        }
        
        #musicToggle i {
            color: white;
            font-size: 20px;
            position: relative;
            z-index: 1;
        }
        
        #musicControls {
            position: absolute;
            bottom: 70px;
            left: 0;
            width: 300px;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 20px 22px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            display: none;
            border: 1px solid rgba(102,126,234,0.08);
            transition: all 0.3s ease;
        }
        
        #musicControls.show {
            display: block;
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .music-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        
        .music-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .music-header-left .live-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulseDot 1.5s infinite;
        }
        
        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }
        
        .music-header-left h3 {
            font-size: 14px;
            font-weight: 600;
            color: #667eea;
        }
        .music-header-left h3 i { margin-right: 6px; }
        
        .music-close-btn {
            background: none;
            border: none;
            color: #999;
            font-size: 20px;
            cursor: pointer;
            padding: 0 5px;
            transition: color 0.3s ease;
        }
        .music-close-btn:hover { color: #333; }
        
        .music-info {
            background: linear-gradient(135deg, #f5f0ff, #fdf2f8);
            border-radius: 14px;
            padding: 12px;
            text-align: center;
            margin-bottom: 14px;
        }
        
        .music-info .note-icon {
            font-size: 24px;
            display: block;
            margin-bottom: 4px;
        }
        
        .music-info .song-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .music-progress-container {
            margin-bottom: 14px;
        }
        
        .music-progress-container .time-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #999;
            margin-bottom: 4px;
        }
        
        .music-progress-track {
            width: 100%;
            height: 4px;
            background: #e8e8e8;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .music-progress-track .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 4px;
            width: 0%;
            transition: width 0.1s ease;
        }
        
        .music-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 14px;
        }
        
        .music-controls button {
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #555;
        }
        
        .music-controls button:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .music-controls .play-btn {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        
        .music-controls .play-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(102,126,234,0.5);
        }
        
        .music-volume {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .music-volume i { color: #667eea; font-size: 12px; }
        
        .music-volume input[type="range"] {
            flex: 1;
            height: 3px;
            -webkit-appearance: none;
            appearance: none;
            background: #e8e8e8;
            border-radius: 3px;
            outline: none;
        }
        
        .music-volume input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(102,126,234,0.3);
        }
        
        .music-volume input[type="range"]::-moz-range-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            cursor: pointer;
            border: none;
        }
        
        .music-volume .vol-percent {
            font-size: 10px;
            color: #999;
            min-width: 32px;
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .navbar-modern { padding: 12px 16px; }
            #musicControls { width: 280px !important; left: 0 !important; }
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- NAVBAR -->
<!-- ========================================== -->
<nav class="navbar-modern fixed top-0 w-full z-50 py-4 px-6 md:px-12" id="mainNavbar">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        
        <a href="/staynest/index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">StayNest</span>
        </a>
        
        <div class="hidden md:flex items-center gap-8">
            <a href="/staynest/index.php" class="nav-link hover:text-purple-600 transition font-medium" id="navHome">Home</a>
            <a href="/staynest/properties.php" class="nav-link hover:text-purple-600 transition font-medium" id="navProperties">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="nav-link hover:text-purple-600 transition font-medium" id="navBookings">My Bookings</a>
        </div>
        
        <div class="flex items-center gap-3">
            
            <a href="/staynest/admin/login.php" class="hidden md:block admin-btn text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-user-shield"></i> <span>Admin</span>
            </a>
            
            <button id="musicToggleBtn" class="hidden md:flex items-center gap-2 text-gray-700 hover:text-purple-600 transition text-sm font-medium px-3 py-2 rounded-full hover:bg-purple-50">
                <i class="fas fa-music"></i>
                <span id="musicStatus">Off</span>
            </button>
            
            <?php if($is_logged_in): ?>
                <div class="relative" id="userMenuContainer">
                    <button id="userMenuBtn" class="user-btn text-white px-4 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="/staynest/profile.php" class="user-dropdown-item"><i class="fas fa-user"></i> My Profile</a>
                        <a href="/staynest/bookings/my_bookings.php" class="user-dropdown-item"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <?php if($user_role == 'admin'): ?>
                            <a href="/staynest/admin/index.php" class="user-dropdown-item"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
                        <?php endif; ?>
                        <div class="user-dropdown-divider"></div>
                        <a href="/staynest/logout.php" class="user-dropdown-item text-red-600"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/staynest/login.php" class="user-btn text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                </a>
                <a href="/staynest/register.php" class="bg-transparent border-2 border-purple-600 text-purple-600 px-5 py-2 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> <span>Register</span>
                </a>
            <?php endif; ?>
            
            <button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
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

<div style="height: 80px;"></div>

<!-- ========================================== -->
<!-- MUSIC PLAYER -->
<!-- ========================================== -->
<div id="musicPlayerContainer">
    <button id="musicToggle">
        <span class="pulse-ring" id="pulseRing"></span>
        <i class="fas fa-music" id="musicToggleIcon"></i>
    </button>
    
    <div id="musicControls">
        <div class="music-header">
            <div class="music-header-left">
                <span class="live-dot"></span>
                <h3><i class="fas fa-music"></i> StayNest Radio</h3>
            </div>
            <button class="music-close-btn" id="closeMusicBtn">&times;</button>
        </div>
        
        <div class="music-info">
            <span class="note-icon" id="musicNoteAnim">🎧</span>
            <p class="song-name" id="songName">Nastelbom Elegant</p>
        </div>
        
        <div class="music-progress-container">
            <div class="time-row">
                <span id="currentTime">0:00</span>
                <span id="totalTime">3:45</span>
            </div>
            <div class="music-progress-track" id="progressTrack">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
        
        <div class="music-controls">
            <button id="prevBtn"><i class="fas fa-step-backward"></i></button>
            <button class="play-btn" id="playBtn"><i class="fas fa-play"></i></button>
            <button id="nextBtn"><i class="fas fa-step-forward"></i></button>
        </div>
        
        <div class="music-volume">
            <i class="fas fa-volume-down"></i>
            <input type="range" id="volumeSlider" min="0" max="100" value="40">
            <span class="vol-percent" id="volumePercent">40%</span>
        </div>
    </div>
</div>

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
    
    // Music Player
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎵 StayNest Music Player Loaded!');
        
        const musicToggle = document.getElementById('musicToggle');
        const musicToggleBtn = document.getElementById('musicToggleBtn');
        const musicToggleMobile = document.getElementById('musicToggleMobile');
        const musicControls = document.getElementById('musicControls');
        const closeMusicBtn = document.getElementById('closeMusicBtn');
        const playBtn = document.getElementById('playBtn');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const progressTrack = document.getElementById('progressTrack');
        const progressFill = document.getElementById('progressFill');
        const volumeSlider = document.getElementById('volumeSlider');
        const volumePercent = document.getElementById('volumePercent');
        const currentTime = document.getElementById('currentTime');
        const totalTime = document.getElementById('totalTime');
        const pulseRing = document.getElementById('pulseRing');
        const musicToggleIcon = document.getElementById('musicToggleIcon');
        const musicStatus = document.getElementById('musicStatus');
        const musicStatusMobile = document.getElementById('musicStatusMobile');
        const musicNoteAnim = document.getElementById('musicNoteAnim');
        
        let isPlaying = false;
        let progress = 0;
        let progressInterval = null;
        let volume = 40;
        const totalDuration = 225;
        
        function toggleControls(e) {
            if (e) e.stopPropagation();
            musicControls.classList.toggle('show');
        }
        
        if (musicToggle) musicToggle.addEventListener('click', toggleControls);
        if (musicToggleBtn) musicToggleBtn.addEventListener('click', toggleControls);
        if (musicToggleMobile) musicToggleMobile.addEventListener('click', toggleControls);
        
        if (closeMusicBtn) {
            closeMusicBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                musicControls.classList.remove('show');
            });
        }
        
        document.addEventListener('click', function(e) {
            if (musicControls && musicControls.classList.contains('show')) {
                if (!musicControls.contains(e.target) && 
                    !musicToggle.contains(e.target) && 
                    !musicToggleBtn?.contains(e.target) &&
                    !musicToggleMobile?.contains(e.target)) {
                    musicControls.classList.remove('show');
                }
            }
        });
        
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                isPlaying = !isPlaying;
                const icon = this.querySelector('i');
                
                if (isPlaying) {
                    icon.className = 'fas fa-pause';
                    this.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                    if (pulseRing) pulseRing.classList.add('active');
                    if (musicToggleIcon) musicToggleIcon.className = 'fas fa-stop';
                    if (musicStatus) { musicStatus.textContent = 'On'; musicStatus.style.color = '#667eea'; }
                    if (musicStatusMobile) musicStatusMobile.textContent = 'Music: On';
                    simulateProgress();
                    animateNotes();
                } else {
                    icon.className = 'fas fa-play';
                    this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    if (pulseRing) pulseRing.classList.remove('active');
                    if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
                    if (musicStatus) { musicStatus.textContent = 'Off'; musicStatus.style.color = 'gray'; }
                    if (musicStatusMobile) musicStatusMobile.textContent = 'Music: Off';
                    if (progressInterval) { clearTimeout(progressInterval); progressInterval = null; }
                }
            });
        }
        
        let noteInterval = null;
        function animateNotes() {
            if (noteInterval) clearInterval(noteInterval);
            if (!isPlaying) return;
            const notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
            let i = 0;
            noteInterval = setInterval(function() {
                if (!isPlaying) { clearInterval(noteInterval); return; }
                if (musicNoteAnim) {
                    musicNoteAnim.textContent = notes[i % notes.length];
                    i++;
                }
            }, 800);
        }
        
        function simulateProgress() {
            if (!isPlaying) return;
            if (progress >= 100) {
                progress = 0;
                if (playBtn) {
                    const icon = playBtn.querySelector('i');
                    icon.className = 'fas fa-play';
                    playBtn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    isPlaying = false;
                    if (pulseRing) pulseRing.classList.remove('active');
                    if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
                    if (musicStatus) { musicStatus.textContent = 'Off'; musicStatus.style.color = 'gray'; }
                    if (musicStatusMobile) musicStatusMobile.textContent = 'Music: Off';
                    if (noteInterval) clearInterval(noteInterval);
                }
                return;
            }
            progress += 0.5;
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
            progressInterval = setTimeout(simulateProgress, 100);
        }
        
        function updateTimeDisplay() {
            if (currentTime) {
                const currentSeconds = Math.floor((progress / 100) * totalDuration);
                const mins = Math.floor(currentSeconds / 60);
                const secs = currentSeconds % 60;
                currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
        }
        
        if (progressTrack) {
            progressTrack.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const percent = (x / rect.width) * 100;
                progress = Math.min(100, Math.max(0, percent));
                if (progressFill) progressFill.style.width = progress + '%';
                updateTimeDisplay();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                progress = Math.max(0, progress - 10);
                if (progressFill) progressFill.style.width = progress + '%';
                updateTimeDisplay();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                progress = Math.min(100, progress + 10);
                if (progressFill) progressFill.style.width = progress + '%';
                updateTimeDisplay();
            });
        }
        
        if (volumeSlider) {
            volumeSlider.addEventListener('input', function() {
                volume = parseFloat(this.value);
                if (volumePercent) volumePercent.textContent = volume + '%';
                if (volume === 0) {
                    document.querySelector('.music-volume i').className = 'fas fa-volume-mute';
                } else {
                    document.querySelector('.music-volume i').className = 'fas fa-volume-down';
                }
                localStorage.setItem('staynest_musicVolume', volume);
            });
        }
        
        const savedVolume = localStorage.getItem('staynest_musicVolume');
        if (savedVolume !== null && volumeSlider) {
            volume = parseFloat(savedVolume);
            volumeSlider.value = volume;
            if (volumePercent) volumePercent.textContent = volume + '%';
        }
        
        if (totalTime) {
            const mins = Math.floor(totalDuration / 60);
            const secs = totalDuration % 60;
            totalTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName !== 'INPUT' && e.key === ' ') {
                e.preventDefault();
                if (playBtn) playBtn.click();
            }
        });
        
        console.log('🎵 StayNest Music Player ready!');
    });
</script>