<?php
// bookings/my_bookings.php - Halaman My Bookings
$page_title = "My Bookings - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_bookings.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$bookings = [];
$error = '';

try {
    // Ambil semua booking user
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location,
               p.image_url, 
               (SELECT COUNT(*) FROM payments WHERE booking_id = b.id) as payment_count
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">📋 My Bookings</h1>
        <a href="../index.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
            <i class="fas fa-home mr-2"></i> Browse Properties
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($bookings)): ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-calendar-plus text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Bookings Yet</h3>
            <p class="text-gray-500 mb-4">You haven't made any bookings yet.</p>
            <a href="../index.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i> Find Properties
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($bookings as $booking): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="md:w-48 h-48 md:h-auto bg-gray-200">
                            <img src="<?php echo htmlspecialchars($booking['image_url'] ?? '/assets/images/default-property.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($booking['property_name']); ?>"
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($booking['property_name']); ?></h3>
                                    <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                    <?php 
                                    switch($booking['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-700';
                                            break;
                                        case 'active':
                                            echo 'bg-green-100 text-green-700';
                                            break;
                                        case 'completed':
                                            echo 'bg-blue-100 text-blue-700';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-700';
                                            break;
                                        case 'expired':
                                            echo 'bg-gray-100 text-gray-700';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-700';
                                    }
                                    ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div>
                                    <p class="text-sm text-gray-500">Check In</p>
                                    <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Check Out</p>
                                    <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Guests</p>
                                    <p class="font-semibold"><?php echo $booking['guests']; ?> person(s)</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Price</p>
                                    <p class="font-bold text-purple-600">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3 mt-4">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="payment.php?id=<?php echo $booking['id']; ?>" 
                                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                        <i class="fas fa-credit-card mr-2"></i> Pay Now
                                    </a>
                                <?php endif; ?>
                                
                                <a href="booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                   class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-info-circle mr-2"></i> Details
                                </a>
                                
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                       class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
                                       onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="fas fa-times mr-2"></i> Cancel
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