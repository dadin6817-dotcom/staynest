<?php
// admin/index.php - Dashboard Admin
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
require_once dirname(__FILE__) . '/../includes/header.php';

// Ambil statistik
$stats = [];
try {
    // Total properties
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
    $stats['properties'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $stats['bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $stats['pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (Exception $e) {
    $stats = ['properties' => 0, 'bookings' => 0, 'users' => 0, 'pending' => 0];
}
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📊 Admin Dashboard</h1>
        <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Properties</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['properties']; ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['bookings']; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['users']; ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Bookings</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid md:grid-cols-3 gap-6">
        <a href="../properties.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-plus-circle text-3xl text-purple-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Manage Properties</h3>
            <p class="text-gray-500 text-sm">Add, edit, or delete properties</p>
        </a>
        
        <a href="../bookings/my_bookings.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-list text-3xl text-blue-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">View Bookings</h3>
            <p class="text-gray-500 text-sm">Manage all bookings</p>
        </a>
        
        <a href="../index.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-home text-3xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">View Website</h3>
            <p class="text-gray-500 text-sm">Go to homepage</p>
        </a>
    </div>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>