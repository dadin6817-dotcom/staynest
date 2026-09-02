<?php
// bookings/payment.php - Halaman Payment
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

// Ambil data booking
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

// Cek apakah booking sudah expired (> 24 jam)
$now = new DateTime();
$expiry = new DateTime($booking['payment_expiry']);
$is_expired = $now > $expiry;

// Cek cooldown
$cooldown_until = null;
$cooldown_active = false;
if (!empty($booking['cooldown_until'])) {
    $cooldown_until = new DateTime($booking['cooldown_until']);
    $cooldown_active = $now < $cooldown_until;
}

if ($is_expired) {
    // Set cooldown 2 menit
    $cooldown_time = new DateTime();
    $cooldown_time->modify('+2 minutes');
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', cooldown_until = ? WHERE id = ?");
    $stmt->execute([$cooldown_time->format('Y-m-d H:i:s'), $booking_id]);
    $error = "⏰ Payment time has expired (24 hours). Please wait 2 minutes before booking again.";
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {
    if ($is_expired) {
        $error = "Payment already expired!";
    } elseif ($cooldown_active) {
        $remaining = $cooldown_until->diff($now);
        $error = "⏳ Please wait " . $remaining->i . " minutes " . $remaining->s . " seconds before booking again.";
    } else {
        try {
            // Generate transaction ID
            $transaction_id = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Update payment status
            $stmt = $pdo->prepare("
                UPDATE bookings SET 
                    payment_status = 'paid',
                    status = 'active',
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            // Insert ke tabel payments
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, payment_date)
                VALUES (?, ?, ?, ?, 'success', NOW())
            ");
            $stmt->execute([
                $booking_id,
                $booking['total_price'],
                $booking['payment_method'],
                $transaction_id
            ]);
            
            $_SESSION['success'] = "✅ Payment successful! Your booking is now active.";
            header('Location: booking_detail.php?id=' . $booking_id);
            exit;
            
        } catch (Exception $e) {
            $error = "Payment failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">💳 Payment</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            <?php if (strpos($error, '2 minutes') !== false): ?>
                <br><br>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-3 rounded-lg text-sm">
                    <i class="fas fa-hourglass-half mr-1"></i> 
                    <span id="countdown">Counting down...</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($booking && !$is_expired && !$cooldown_active): ?>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($booking['property_name']); ?></h2>
                    <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                    ⏳ Pending Payment
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
                    <p class="text-sm text-gray-500">Payment Method</p>
                    <p class="font-bold"><?php echo htmlspecialchars($booking['payment_method']); ?></p>
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
                    <?php echo $booking['payment_method'] == 'BCA' ? '8820' : ($booking['payment_method'] == 'BRI' ? '8880' : '8870'); ?>
                    <?php echo str_pad($booking['id'], 10, '0', STR_PAD_LEFT); ?>
                </p>
                <p class="text-xs text-purple-500 mt-1">Pay before <?php echo date('d M Y H:i', strtotime($booking['payment_expiry'])); ?></p>
            </div>
            
            <div class="flex gap-4">
                <form method="POST" class="flex-1">
                    <button type="submit" name="pay_now" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i> Confirm Payment
                    </button>
                </form>
                <a href="my_bookings.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                    Cancel
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($is_expired): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
            <i class="fas fa-clock text-6xl text-red-400 mb-4 block"></i>
            <h3 class="text-xl font-bold text-red-700">Payment Expired</h3>
            <p class="text-red-600">Your booking has been cancelled due to payment timeout.</p>
            <p class="text-sm text-red-500 mt-2">You can try booking again after the cooldown period.</p>
            <a href="/staynest/properties.php" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-xl hover:bg-purple-700 transition">
                Browse Properties
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
<?php if ($cooldown_active && isset($cooldown_until)): ?>
var cooldownTime = new Date('<?php echo $cooldown_until->format('Y-m-d H:i:s'); ?>').getTime();
var countdown = document.getElementById('countdown');
if (countdown) {
    var timer = setInterval(function() {
        var now = new Date().getTime();
        var distance = cooldownTime - now;
        if (distance < 0) {
            clearInterval(timer);
            countdown.textContent = '⏰ Cooldown finished! You can book again now.';
            location.reload();
            return;
        }
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        countdown.textContent = '⏳ Please wait ' + minutes + 'm ' + seconds + 's before booking again.';
    }, 1000);
}
<?php endif; ?>
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>