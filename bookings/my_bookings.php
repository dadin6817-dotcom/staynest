<?php
// bookings/my_bookings.php - Halaman My Bookings
$page_title = "My Bookings - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/functions.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: /staynest/login.php?redirect=bookings/my_bookings.php');
    exit;
}

require_once dirname(__FILE__) . '/../includes/header.php';

$user_id = $_SESSION['user_id'];
$bookings = [];
$error = '';

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
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800">📋 My Bookings</h1>
            <p class="text-gray-500 mt-2">Manage all your bookings here</p>
        </div>
        <a href="/staynest/properties.php" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition inline-flex items-center gap-2">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
    
    <!-- Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600"><?php echo count($bookings); ?></div>
            <div class="text-gray-500 text-sm">Total Bookings</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">
                <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'pending'; })); ?>
            </div>
            <div class="text-gray-500 text-sm">Pending</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600">
                <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'active'; })); ?>
            </div>
            <div class="text-gray-500 text-sm">Active</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">
                <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'completed'; })); ?>
            </div>
            <div class="text-gray-500 text-sm">Completed</div>
        </div>
    </div>
    
    <!-- Bookings List -->
    <?php if (empty($bookings)): ?>
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <i class="fas fa-calendar-plus text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Booking</h3>
            <p class="text-gray-500 mb-6">Kamu belum melakukan booking apapun. Yuk cari properti!</p>
            <a href="/staynest/properties.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i> Cari Properti
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($bookings as $booking): 
                $status_colors = [
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'active' => 'bg-green-100 text-green-700',
                    'completed' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    'expired' => 'bg-gray-100 text-gray-700'
                ];
                $status_class = $status_colors[$booking['status']] ?? 'bg-gray-100 text-gray-700';
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="flex flex-col md:flex-row">
                    <!-- Icon -->
                    <div class="md:w-48 h-48 md:h-auto bg-gradient-to-br from-purple-500 to-purple-700 flex-shrink-0 flex items-center justify-center">
                        <i class="fas fa-home text-white text-5xl"></i>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-3 mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($booking['property_name'] ?? 'Property'); ?>
                                </h3>
                                <p class="text-gray-500 text-sm mt-1">
                                    <i class="fas fa-map-marker-alt text-purple-500 mr-1"></i> 
                                    <?php echo htmlspecialchars($booking['property_location'] ?? '-'); ?>
                                </p>
                            </div>
                            <span class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap <?php echo $status_class; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        
                        <!-- Details -->
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
                                <p class="font-semibold text-sm text-purple-600">
                                    <?php echo formatRupiah($booking['total_price']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
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
                            
                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                   class="bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition inline-flex items-center gap-2 text-sm font-semibold"
                                   onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>