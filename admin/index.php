<?php
// admin/index.php - Admin Dashboard (Dengan Musik Auto Play)
require_once dirname(__FILE__) . '/../config/database.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
    $total_properties = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $total_bookings = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
    $active_bookings = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE payment_status = 'pending'");
    $pending_payments = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(price_per_month * duration_months) as total FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.status = 'confirmed' AND b.payment_status = 'paid'");
    $revenue = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE MONTH(booked_at) = MONTH(CURRENT_DATE()) AND YEAR(booked_at) = YEAR(CURRENT_DATE())");
    $monthly_bookings = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("
        SELECT b.*, p.name as property_name, p.location, p.price_per_month 
        FROM bookings b 
        JOIN properties p ON b.property_id = p.id 
        ORDER BY b.booked_at DESC 
        LIMIT 10
    ");
    $recent_bookings = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT location, COUNT(*) as count FROM properties GROUP BY location ORDER BY count DESC LIMIT 5");
    $popular_locations = $stmt->fetchAll();
    
} catch(Exception $e) {
    $total_properties = 0; $total_bookings = 0; $active_bookings = 0;
    $pending_payments = 0; $revenue = 0; $monthly_bookings = 0;
    $recent_bookings = array(); $popular_locations = array();
}

$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StayNest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Music Player untuk Admin Dashboard -->
    <script src="/staynest/assets/js/music-player.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .sidebar { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%); }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .nav-item { transition: all 0.3s ease; }
        .nav-item:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); }
        .nav-item.active { background: rgba(255,255,255,0.2); }
        .location-text { display: block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 200px; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #667eea; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    
    <div class="sidebar w-64 text-white flex flex-col flex-shrink-0">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <span class="text-xl font-bold">StayNest Admin</span>
            </div>
            
            <nav class="space-y-2">
                <a href="index.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-tachometer-alt w-5"></i> <span>Dashboard</span>
                </a>
                <a href="manage_bookings.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-calendar-check w-5"></i> <span>Manage Bookings</span>
                </a>
                <a href="add_property.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-plus-circle w-5"></i> <span>Add Property</span>
                </a>
                <a href="../index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition mt-8">
                    <i class="fas fa-globe w-5"></i> <span>View Website</span>
                </a>
            </nav>
        </div>
        
        <div class="mt-auto p-6 border-t border-white/20">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div><p class="font-semibold"><?php echo htmlspecialchars($admin_name); ?></p><p class="text-xs text-white/70">Administrator</p></div>
            </div>
            <a href="logout.php" class="flex items-center gap-2 text-white/70 hover:text-white transition text-sm">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>
    
    <div class="flex-1 overflow-y-auto">
        <div class="p-8">
            <div class="mb-8"><h1 class="text-3xl font-bold text-gray-800">Dashboard</h1><p class="text-gray-500 mt-1">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p></div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="stat-card bg-white rounded-xl p-6 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Total Properties</p><p class="text-3xl font-bold text-gray-800"><?php echo $total_properties; ?></p></div><div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-building text-blue-600 text-xl"></i></div></div></div>
                <div class="stat-card bg-white rounded-xl p-6 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Total Bookings</p><p class="text-3xl font-bold text-gray-800"><?php echo $total_bookings; ?></p></div><div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"><i class="fas fa-calendar text-purple-600 text-xl"></i></div></div></div>
                <div class="stat-card bg-white rounded-xl p-6 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Active Bookings</p><p class="text-3xl font-bold text-green-600"><?php echo $active_bookings; ?></p></div><div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-green-600 text-xl"></i></div></div></div>
                <div class="stat-card bg-white rounded-xl p-6 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Pending Payments</p><p class="text-3xl font-bold text-orange-600"><?php echo $pending_payments; ?></p></div><div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center"><i class="fas fa-clock text-orange-600 text-xl"></i></div></div></div>
                <div class="stat-card bg-white rounded-xl p-6 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Total Revenue</p><p class="text-xl font-bold text-purple-600">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p></div><div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"><i class="fas fa-money-bill text-purple-600 text-xl"></i></div></div></div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-line mr-2 text-purple-600"></i> Monthly Bookings</h2><div class="text-center py-8"><div class="inline-block"><div class="text-6xl font-bold text-purple-600 mb-2"><?php echo $monthly_bookings; ?></div><p class="text-gray-500">Bookings this month</p></div></div><div class="mt-4 pt-4 border-t border-gray-100"><div class="flex justify-between text-sm text-gray-500"><span>Total: <?php echo $total_bookings; ?></span><span>Active: <?php echo $active_bookings; ?></span><span>Pending Pay: <?php echo $pending_payments; ?></span></div></div></div>
                
                <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-pie mr-2 text-purple-600"></i> Popular Locations</h2><?php if(count($popular_locations) > 0): ?><div class="space-y-4"><?php $max_count = !empty($popular_locations) ? max(array_column($popular_locations, 'count')) : 1; foreach($popular_locations as $location): ?><div><div class="flex justify-between text-sm mb-1"><span class="text-gray-700 location-text" title="<?php echo htmlspecialchars($location['location']); ?>">📍 <?php echo htmlspecialchars($location['location']); ?></span><span class="text-gray-500 ml-2"><?php echo $location['count']; ?> prop</span></div><div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo min(100, ($location['count'] / $max_count) * 100); ?>%"></div></div></div><?php endforeach; ?></div><?php else: ?><div class="text-center py-8 text-gray-500"><i class="fas fa-chart-line text-4xl mb-2 block"></i><p>No location data available</p></div><?php endif; ?></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="add_property.php" class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl p-6 text-white hover:shadow-lg transition transform hover:scale-105"><i class="fas fa-plus-circle text-3xl mb-3 block"></i><h3 class="text-lg font-bold mb-1">Add New Property</h3><p class="text-sm opacity-90">Add a new boarding house</p></a>
                <a href="manage_bookings.php" class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white hover:shadow-lg transition transform hover:scale-105"><i class="fas fa-calendar-check text-3xl mb-3 block"></i><h3 class="text-lg font-bold mb-1">Manage Bookings</h3><p class="text-sm opacity-90">View all customer bookings</p></a>
                <a href="../properties.php" class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white hover:shadow-lg transition transform hover:scale-105"><i class="fas fa-building text-3xl mb-3 block"></i><h3 class="text-lg font-bold mb-1">View Properties</h3><p class="text-sm opacity-90">See all properties on website</p></a>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6"><div class="flex justify-between items-center mb-4"><h2 class="text-lg font-bold text-gray-800"><i class="fas fa-clock mr-2 text-purple-600"></i> Recent Bookings</h2><a href="manage_bookings.php" class="text-purple-600 hover:underline text-sm">View All →</a></div><div class="overflow-x-auto"><table class="w-full min-w-[800px]"><thead><tr class="border-b border-gray-200"><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">ID</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Property</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Customer</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Check-in</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Status</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Payment</th><th class="text-left py-3 px-3 text-sm font-semibold text-gray-600">Amount</th></tr></thead><tbody><?php if(count($recent_bookings) > 0): foreach($recent_bookings as $booking): ?><tr class="border-b border-gray-100 hover:bg-gray-50 transition"><td class="py-3 px-3 text-sm font-mono">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td><td class="py-3 px-3"><p class="font-medium text-gray-800 text-sm" title="<?php echo htmlspecialchars($booking['property_name']); ?>"><?php echo htmlspecialchars(strlen($booking['property_name']) > 20 ? substr($booking['property_name'], 0, 18) . '...' : $booking['property_name']); ?></p><p class="text-xs text-gray-500"><?php echo htmlspecialchars(strlen($booking['location']) > 25 ? substr($booking['location'], 0, 23) . '...' : $booking['location']); ?></p></td><td class="py-3 px-3"><p class="font-medium text-sm"><?php echo htmlspecialchars($booking['customer_name']); ?></p><p class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($booking['customer_email'], 0, 20)); ?></p></td><td class="py-3 px-3 text-sm"><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></td><td class="py-3 px-3"><span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : ($booking['status'] == 'pending_payment' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')); ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?></span></td><td class="py-3 px-3"><span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : ($booking['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : ($booking['payment_status'] == 'unpaid' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')); ?>"><?php echo ucfirst($booking['payment_status'] ?? 'N/A'); ?></span></td><td class="py-3 px-3 text-sm font-semibold text-purple-600">Rp <?php echo number_format($booking['price_per_month'] * $booking['duration_months'], 0, ',', '.'); ?></td></tr><?php endforeach; else: ?><tr><td colspan="7" class="text-center py-8 text-gray-500"><i class="fas fa-inbox text-4xl mb-2 block"></i>No bookings found</td></tr><?php endif; ?></tbody></table></div></div>
        </div>
    </div>
</body>
</html>