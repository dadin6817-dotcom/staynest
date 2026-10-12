<?php
// admin/index.php - Dashboard Admin StayNest (REVISI FINAL)
$page_title = "Admin Dashboard - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once dirname(__FILE__) . '/../config/database.php';

// ==============================================
// AMBIL STATISTIK
// ==============================================
$stats = ['properties' => 0, 'bookings' => 0, 'users' => 0, 'pending' => 0, 'revenue' => 0];

try {
    $stats['properties'] = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $stats['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $stats['revenue'] = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status IN ('active', 'completed')")->fetchColumn();
} catch (Exception $e) {}

// Recent bookings
$recent_bookings = [];
try {
    $stmt = $pdo->query("
        SELECT b.*, p.name as property_name, u.full_name as user_name
        FROM bookings b
        LEFT JOIN properties p ON b.property_id = p.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f8fafc; }
        .gradient-text { background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
    </style>
</head>
<body>

<!-- Admin Navbar -->
<nav class="bg-white shadow-md py-4 px-6 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                <i class="fas fa-user-shield text-white text-xl"></i>
            </div>
            <span class="text-2xl font-extrabold gradient-text">Admin Panel</span>
        </a>
        
        <div class="flex items-center gap-4">
            <span class="hidden md:flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-user-circle text-purple-600 text-lg"></i>
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
            </span>
            <a href="../welcome.php" class="text-gray-600 hover:text-purple-600 transition text-sm hidden md:flex items-center gap-1">
                <i class="fas fa-home"></i> Website
            </a>
            <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-2">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Welcome -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📊 Dashboard Admin</h1>
        <p class="text-gray-500 mt-1">Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-5 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-building text-xl text-purple-600"></i>
            </div>
            <div class="text-2xl font-bold text-purple-600"><?php echo $stats['properties']; ?></div>
            <div class="text-gray-500 text-xs mt-1">Properties</div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-5 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-calendar-check text-xl text-blue-600"></i>
            </div>
            <div class="text-2xl font-bold text-blue-600"><?php echo $stats['bookings']; ?></div>
            <div class="text-gray-500 text-xs mt-1">Bookings</div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-5 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-users text-xl text-green-600"></i>
            </div>
            <div class="text-2xl font-bold text-green-600"><?php echo $stats['users']; ?></div>
            <div class="text-gray-500 text-xs mt-1">Users</div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-5 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-clock text-xl text-yellow-600"></i>
            </div>
            <div class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></div>
            <div class="text-gray-500 text-xs mt-1">Pending</div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-5 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-money-bill text-xl text-pink-600"></i>
            </div>
            <div class="text-lg font-bold text-pink-600">Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></div>
            <div class="text-gray-500 text-xs mt-1">Revenue</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <a href="../properties.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center group">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                <i class="fas fa-building text-3xl text-purple-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Manage Properties</h3>
            <p class="text-gray-500 text-sm">Lihat & kelola properti</p>
        </a>
        
        <a href="../bookings/my_bookings.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center group">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                <i class="fas fa-list text-3xl text-blue-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">View Bookings</h3>
            <p class="text-gray-500 text-sm">Lihat semua booking</p>
        </a>
        
        <a href="../welcome.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center group">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                <i class="fas fa-home text-3xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">View Website</h3>
            <p class="text-gray-500 text-sm">Kembali ke homepage</p>
        </a>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">📋 Recent Bookings</h2>
            <a href="../bookings/my_bookings.php" class="text-purple-600 hover:text-purple-800 text-sm font-semibold">
                View All →
            </a>
        </div>
        
        <?php if (empty($recent_bookings)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Belum ada booking</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-500 border-b">
                            <th class="pb-3">Booking Code</th>
                            <th class="pb-3">Property</th>
                            <th class="pb-3">User</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $b): 
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'active' => 'bg-green-100 text-green-700',
                                'completed' => 'bg-blue-100 text-blue-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'expired' => 'bg-gray-100 text-gray-700'
                            ];
                            $status_class = $status_colors[$b['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 text-sm font-medium"><?php echo htmlspecialchars($b['booking_code'] ?? '-'); ?></td>
                            <td class="py-3 text-sm"><?php echo htmlspecialchars($b['property_name'] ?? '-'); ?></td>
                            <td class="py-3 text-sm"><?php echo htmlspecialchars($b['user_name'] ?? '-'); ?></td>
                            <td class="py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_class; ?>">
                                    <?php echo ucfirst($b['status'] ?? '-'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>