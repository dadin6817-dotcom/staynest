<?php
// bookings/booking_detail.php - Detail Booking
$page_title = "Booking Detail - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// ==========================================
// 🔒 CEK LOGIN - AMAN!
// ==========================================
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=booking_detail.php');
    exit;
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = null;

// ==========================================
// 🔒 AMBIL BOOKING HANYA MILIK USER INI
// ==========================================
try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location 
        FROM bookings b 
        JOIN properties p ON b.property_id = p.id 
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);  // 🔒 HARUS milik user ini
    $booking = $stmt->fetch();
} catch(Exception $e) {}

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">📄 Booking Detail</h1>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($booking['property_name']); ?></h2>
                <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold <?php echo $booking['status'] == 'active' ? 'bg-green-100 text-green-700' : ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'); ?>">
                <?php echo ucfirst($booking['status']); ?>
            </span>
        </div>
        
        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Booking Code</p>
                <p class="font-bold text-lg"><?php echo htmlspecialchars($booking['booking_code']); ?></p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Total Price</p>
                <p class="font-bold text-xl text-purple-600">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Check In</p>
                <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Check Out</p>
                <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Duration</p>
                <p class="font-semibold"><?php echo $booking['duration_months']; ?> months</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Guests</p>
                <p class="font-semibold"><?php echo $booking['guests']; ?> person(s)</p>
            </div>
        </div>
        
        <div class="border-t border-gray-100 pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">👤 Tenant Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
            <?php if($booking['notes']): ?>
            <p><strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 flex gap-4">
            <a href="my_bookings.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <?php if($booking['status'] == 'active' || $booking['status'] == 'pending'): ?>
            <a href="#" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-print mr-1"></i> Print Invoice
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>