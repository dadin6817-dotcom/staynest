<?php
// bookings/payment.php - Halaman Pembayaran dengan Termin
$page_title = "Payment - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=payment.php');
    exit;
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = null;
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
} catch (Exception $e) {}

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

// Cek expired
$now = new DateTime();
$expiry = new DateTime($booking['payment_expiry']);
$is_expired = $now > $expiry;

if ($is_expired) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'expired', payment_status = 'expired' WHERE id = ?");
    $stmt->execute([$booking_id]);
    $error = "⏰ Payment has expired (24 hours). Please book again.";
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now']) && !$is_expired) {
    $payment_method = $_POST['payment_method'] ?? 'BCA';
    
    try {
        $transaction_id = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        $stmt = $pdo->prepare("
            UPDATE bookings SET 
                payment_status = 'paid',
                status = 'active',
                updated_at = NOW(),
                payment_method = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$payment_method, $booking_id, $_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("
            INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, payment_date, payment_type)
            VALUES (?, ?, ?, ?, 'success', NOW(), ?)
        ");
        $stmt->execute([
            $booking_id,
            $booking['total_price'],
            $payment_method,
            $transaction_id,
            $booking['payment_method'] ?? 'full'
        ]);
        
        $_SESSION['success'] = "✅ Payment successful!";
        header('Location: booking_detail.php?id=' . $booking_id);
        exit;
        
    } catch (Exception $e) {
        $error = "Payment failed: " . $e->getMessage();
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">💳 Payment</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($booking && !$is_expired): ?>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($booking['property_name']); ?></h2>
                    <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                    ⏳ Pending
                </span>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Booking Code</p>
                    <p class="font-bold"><?php echo htmlspecialchars($booking['booking_code']); ?></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Total Price</p>
                    <p class="font-bold text-xl text-purple-600">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Payment Type</p>
                    <p class="font-bold"><?php echo ucfirst($booking['payment_method'] ?? 'Full'); ?></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Payment Deadline</p>
                    <p class="font-bold <?php echo $is_expired ? 'text-red-600' : 'text-green-600'; ?>">
                        <?php echo date('d M Y H:i', strtotime($booking['payment_expiry'])); ?>
                    </p>
                </div>
            </div>
            
            <!-- Virtual Account -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-purple-700"><i class="fas fa-info-circle mr-1"></i> Virtual Account</p>
                <p class="text-2xl font-bold text-purple-800 tracking-widest">
                    <?php echo '8880' . str_pad($booking['id'], 10, '0', STR_PAD_LEFT); ?>
                </p>
            </div>
            
            <!-- Payment Method -->
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Payment Method</label>
                <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                    <?php $banks = ['BCA', 'BRI', 'BNI', 'MANDIRI', 'BSI']; ?>
                    <?php foreach ($banks as $bank): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="<?php echo $bank; ?>" 
                                   <?php echo $bank == 'BCA' ? 'checked' : ''; ?>
                                   class="hidden peer">
                            <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                <span class="text-sm font-medium peer-checked:text-purple-600"><?php echo $bank; ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="flex gap-4">
                <form method="POST" class="flex-1">
                    <button type="submit" name="pay_now" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i> Pay Now
                    </button>
                </form>
                <a href="my_bookings.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                    Cancel
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>