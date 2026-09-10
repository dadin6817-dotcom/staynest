<?php
// bookings/my_bookings.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "My Bookings - StayNest";

// Cek config file
$config_file = dirname(__FILE__) . '/../config/database.php';
if (!file_exists($config_file)) {
    die("Error: config/database.php tidak ditemukan!");
}
require_once $config_file;

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_bookings.php');
    exit;
}

// Cek header file
$header_file = dirname(__FILE__) . '/../includes/header.php';
if (!file_exists($header_file)) {
    die("Error: includes/header.php tidak ditemukan!");
}
require_once $header_file;

$user_id = $_SESSION['user_id'];
$bookings = [];
$error = '';

// Ambil data bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location
        FROM bookings b
        LEFT JOIN properties p ON b.property_id = p.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800">📋 My Bookings</h1>
            <p class="text-gray-500 mt-2">Manage all your bookings here</p>
        </div>
        <a href="../properties.php" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition inline-flex items-center gap-2">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($bookings)): ?>
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <i class="fas fa-calendar-plus text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Booking</h3>
            <p class="text-gray-500 mb-6">Kamu belum melakukan booking apapun. Yuk cari properti!</p>
            <a href="../properties.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i> Cari Properti
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($bookings as $booking): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-48 h-48 md:h-auto bg-gradient-to-br from-purple-400 to-purple-600 flex-shrink-0 flex items-center justify-center">
                            <i class="fas fa-home text-white text-5xl"></i>
                        </div>
                        
                        <div class="flex-1 p-6">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-3 mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($booking['property_name'] ?? 'Property'); ?></h3>
                                    <p class="text-gray-500 text-sm mt-1">
                                        <i class="fas fa-map-marker-alt text-purple-500 mr-1"></i> 
                                        <?php echo htmlspecialchars($booking['property_location'] ?? '-'); ?>
                                    </p>
                                </div>
                                <span class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap
                                    <?php 
                                    switch($booking['status']) {
                                        case 'pending': echo 'bg-yellow-100 text-yellow-700'; break;
                                        case 'active': echo 'bg-green-100 text-green-700'; break;
                                        case 'completed': echo 'bg-blue-100 text-blue-700'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-700'; break;
                                        case 'expired': echo 'bg-gray-100 text-gray-700'; break;
                                        default: echo 'bg-gray-100 text-gray-700';
                                    }
                                    ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Booking Code</p>
                                    <p class="font-semibold text-sm"><?php echo htmlspecialchars($booking['booking_code']); ?></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Check In</p>
                                    <p class="font-semibold text-sm"><?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Check Out</p>
                                    <p class="font-semibold text-sm"><?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">Total</p>
                                    <p class="font-semibold text-sm text-purple-600">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="payment.php?id=<?php echo $booking['id']; ?>" 
                                       class="bg-green-600 text-white px-5 py-2.5 rounded-xl hover:bg-green-700 transition inline-flex items-center gap-2 text-sm font-semibold">
                                        <i class="fas fa-credit-card"></i> Bayar
                                    </a>
                                <?php endif; ?>
                                
                                <a href="booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                   class="bg-purple-600 text-white px-5 py-2.5 rounded-xl hover:bg-purple-700 transition inline-flex items-center gap-2 text-sm font-semibold">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$footer_file = dirname(__FILE__) . '/../includes/footer.php';
if (file_exists($footer_file)) {
    require_once $footer_file;
}
?>