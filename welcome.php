<?php
// welcome.php - WELCOME PAGE (Dengan Musik & Chatbot)
$page_title = "Welcome to StayNest - Your Journey Begins Here ✨";

require_once dirname(__FILE__) . '/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Welcome to StayNest ✨</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Music Player -->
    <script src="/assets/js/music-player.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; overflow-x: hidden; }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .welcome-hero { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; background: linear-gradient(135deg, #f5f7fa 0%, #eef2ff 30%, #fdf2f8 60%, #fff4e6 100%); }
        .animated-bg { position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0; }
        .animated-bg span { position: absolute; width: 80px; height: 80px; background: rgba(102,126,234,0.1); border-radius: 50%; pointer-events: none; animation: floatBubble 8s infinite linear; }
        .animated-bg span:nth-child(1) { top: 10%; left: 5%; width: 100px; height: 100px; animation-duration: 12s; }
        .animated-bg span:nth-child(2) { top: 20%; right: 10%; width: 150px; height: 150px; animation-duration: 15s; background: rgba(240,147,251,0.1); }
        .animated-bg span:nth-child(3) { bottom: 15%; left: 15%; width: 120px; height: 120px; animation-duration: 10s; background: rgba(79,172,254,0.1); }
        .animated-bg span:nth-child(4) { bottom: 25%; right: 20%; width: 90px; height: 90px; animation-duration: 18s; }
        .animated-bg span:nth-child(5) { top: 50%; left: 40%; width: 200px; height: 200px; animation-duration: 20s; background: rgba(102,126,234,0.05); }
        .animated-bg span:nth-child(6) { top: 70%; left: 70%; width: 130px; height: 130px; animation-duration: 14s; }
        @keyframes floatBubble { 0% { transform: translateY(0) rotate(0deg); opacity: 0; } 10% { opacity: 0.5; } 90% { opacity: 0.5; } 100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; } }
        .welcome-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 3rem; padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.3); transition: all 0.3s ease; }
        .btn-welcome { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 40px; border-radius: 60px; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 12px; text-decoration: none; box-shadow: 0 10px 25px -5px rgba(102,126,234,0.4); }
        .btn-welcome:hover { transform: scale(1.05); }
        .feature-item { display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 20px; transition: all 0.3s ease; cursor: pointer; }
        .feature-item:hover { transform: translateX(10px); background: linear-gradient(135deg, #667eea10, #764ba210); }
        .feature-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #667eea15, #764ba215); border-radius: 18px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .feature-item:hover .feature-icon { background: linear-gradient(135deg, #667eea, #764ba2); transform: scale(1.1); }
        .feature-item:hover .feature-icon i { color: white !important; }
        .navbar-welcome { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); box-shadow: 0 2px 20px rgba(0,0,0,0.05); }
        .nav-link-welcome { transition: all 0.3s ease; position: relative; }
        .nav-link-welcome::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: linear-gradient(135deg, #667eea, #764ba2); transition: width 0.3s ease; }
        .nav-link-welcome:hover::after { width: 100%; }
        .nav-link-welcome:hover { color: #667eea; transform: translateY(-2px); }
        @keyframes pulseAnim { 0%,100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.05); opacity: 0.5; } }
        .pulse-animation { animation: pulseAnim 2s ease-in-out infinite; }
        @media (max-width: 768px) { .welcome-card { padding: 1.5rem; margin: 0 1rem; } .btn-welcome { padding: 12px 24px; font-size: 0.9rem; } }
    </style>
</head>
<body>

<nav class="navbar-welcome fixed top-0 w-full z-50 py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="welcome.php" class="flex items-center gap-3"><div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center"><i class="fas fa-home text-white text-xl"></i></div><span class="text-2xl font-extrabold gradient-text">StayNest</span></a>
        <div class="hidden md:flex items-center gap-8"><a href="index.php" class="nav-link-welcome text-gray-700 hover:text-purple-600 transition font-medium">Home</a><a href="properties.php" class="nav-link-welcome text-gray-700 hover:text-purple-600 transition font-medium">Properties</a><a href="bookings/my_bookings.php" class="nav-link-welcome text-gray-700 hover:text-purple-600 transition font-medium">My Bookings</a></div>
        <div class="flex items-center gap-3"><a href="admin/login.php" class="hidden md:block gradient-bg text-white px-5 py-2 rounded-full text-sm font-medium hover:shadow-lg transition"><i class="fas fa-user-shield mr-1"></i> Admin</a><button id="mobileMenuBtn" class="md:hidden text-2xl text-gray-700"><i class="fas fa-bars"></i></button></div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100"><div class="flex flex-col gap-3"><a href="index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a><a href="properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a><a href="bookings/my_bookings.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">My Bookings</a><a href="admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Admin Panel</a></div></div>
</nav>

<section class="welcome-hero pt-32 pb-20 px-6 relative">
    <div class="animated-bg"><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <div class="container mx-auto px-4 relative z-10"><div class="max-w-5xl mx-auto">
        <div class="welcome-card text-center" data-aos="fade-up">
            <div class="inline-block mb-6"><div class="w-20 h-20 gradient-bg rounded-2xl flex items-center justify-center mx-auto shadow-lg pulse-animation"><i class="fas fa-home text-white text-3xl"></i></div></div>
            <div class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-100 to-pink-100 rounded-full px-5 py-2 mb-6"><span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span></span><span class="text-purple-600 font-medium text-sm">✨ Welcome to Your New Journey ✨</span></div>
            <h1 class="text-4xl md:text-7xl lg:text-8xl font-extrabold leading-tight mb-6"><span class="gradient-text">Welcome to StayNest</span></h1>
            <p class="text-xl md:text-3xl text-gray-700 mb-4">Find a place you'll <span class="gradient-text font-bold">love to call home</span></p>
            <p class="text-base md:text-lg text-gray-500 max-w-2xl mx-auto mb-8 leading-relaxed">Discover cozy spaces that match your lifestyle. Modern, affordable, and totally instagrammable. Your perfect home is just a click away! 🏠✨</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8"><a href="index.php" class="btn-welcome"><i class="fas fa-arrow-right"></i> Get Started Now</a></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            <div class="feature-item"><div class="feature-icon"><i class="fas fa-check-circle text-2xl text-purple-600"></i></div><div><h4 class="font-bold text-sm md:text-base">100% Verified</h4><p class="text-xs text-gray-500">All properties checked</p></div></div>
            <div class="feature-item"><div class="feature-icon"><i class="fas fa-bolt text-2xl text-purple-600"></i></div><div><h4 class="font-bold text-sm md:text-base">Instant Booking</h4><p class="text-xs text-gray-500">Fast & easy process</p></div></div>
            <div class="feature-item"><div class="feature-icon"><i class="fas fa-headset text-2xl text-purple-600"></i></div><div><h4 class="font-bold text-sm md:text-base">24/7 Support</h4><p class="text-xs text-gray-500">Team ready to help</p></div></div>
        </div>
    </div></div>
    <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none"><svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-16"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill" fill="#ffffff" opacity="0.9"></path></svg></div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, offset: 50 });
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) { mobileMenuBtn.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); }); }
</script>

<!-- Chatbot Component -->
<?php include_once dirname(__FILE__) . '/includes/chatbot.php'; ?>

</body>
</html>
