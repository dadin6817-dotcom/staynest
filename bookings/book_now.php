<?php
// bookings/book_now.php - Halaman Booking Properti dengan Payment
$page_title = "Book Now - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=book_now.php');
    exit;
}

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;
$error = '';
$is_extend = false;
$existing_booking = null;

// Cek extend
if (isset($_GET['extend']) && $_GET['extend'] == 1 && isset($_GET['booking_id'])) {
    $is_extend = true;
    $booking_id = (int)$_GET['booking_id'];
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, p.name as property_name, p.location as property_location, p.price_per_month, p.image_url
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            WHERE b.id = ? AND b.user_id = ? AND b.status = 'active'
        ");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $existing_booking = $stmt->fetch();
        if ($existing_booking) {
            $property_id = $existing_booking['property_id'];
            $property = [
                'id' => $existing_booking['property_id'],
                'name' => $existing_booking['property_name'],
                'location' => $existing_booking['property_location'],
                'price_per_month' => $existing_booking['price_per_month'],
                'image_url' => $existing_booking['image_url'] ?? '/staynest/assets/images/default-property.jpg'
            ];
        } else {
            $error = "Active booking not found!";
        }
    } catch (Exception $e) {
        $error = "Error loading booking: " . $e->getMessage();
    }
}

// Ambil properti baru
if (!$is_extend && $property_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch();
        if (!$property) $error = "Property not found!";
    } catch (Exception $e) {
        $error = "Error loading property: " . $e->getMessage();
    }
} elseif (!$is_extend && $property_id == 0) {
    $error = "No property selected!";
}

// Ambil data user
$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {}

// Proses booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $duration = (int)($_POST['duration'] ?? 0);
    $guests = (int)($_POST['guests'] ?? 1);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $use_account_data = isset($_POST['use_account_data']);
    $is_extend_booking = isset($_POST['is_extend']);
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $use_old_data = isset($_POST['use_old_data']) && $_POST['use_old_data'] == 1;
    $payment_method = $_POST['payment_method'] ?? '';

    if ($use_account_data && $user) {
        $full_name = $user['full_name'];
        $email = $user['email'];
        $phone = $user['phone'];
    }
    if ($is_extend_booking && $use_old_data && $existing_booking) {
        $full_name = $existing_booking['full_name'];
        $email = $existing_booking['email'];
        $phone = $existing_booking['phone'];
        $guests = $existing_booking['guests'];
    }

    if (!in_array($duration, [1, 2, 3, 6, 12])) $error = "Select valid duration!";
    if (empty($full_name)) $error = "Full name is required!";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Valid email is required!";
    if (empty($phone)) $error = "Phone number is required!";
    if (empty($payment_method)) $error = "Payment method is required!";

    if (empty($error)) {
        try {
            $booking_code = 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $price_per_month = $property['price_per_month'] ?? 700000;
            $total_price = $price_per_month * $duration;
            $check_in = date('Y-m-d');
            $check_out = date('Y-m-d', strtotime("+$duration months"));
            $payment_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    property_id, user_id, booking_code, check_in, check_out,
                    duration_months, total_price, guests, full_name, email, phone, notes,
                    status, payment_status, payment_method, payment_expiry
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, ?)
            ");
            $stmt->execute([
                $property_id,
                $_SESSION['user_id'],
                $booking_code,
                $check_in,
                $check_out,
                $duration,
                $total_price,
                $guests,
                $full_name,
                $email,
                $phone,
                $notes,
                $payment_method,
                $payment_expiry
            ]);

            $booking_id = $pdo->lastInsertId();
            if ($booking_id > 0) {
                header('Location: payment.php?id=' . $booking_id);
                exit;
            } else {
                $error = "Booking failed!";
            }
        } catch (Exception $e) {
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">📝 Book Property</h1>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$property): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> Property not found.
            <a href="/staynest/properties.php" class="text-purple-600 hover:underline ml-2">← Back</a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Property Info -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <div class="h-40 rounded-xl overflow-hidden bg-gradient-to-r from-purple-400 to-blue-400">
                        <img src="<?php echo !empty($property['image_url']) ? $property['image_url'] : '/staynest/assets/images/default-property.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($property['name']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-white text-4xl\'><i class=\'fas fa-home\'></i></div>';">
                    </div>
                    <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                    <p class="text-purple-600 font-bold mt-2">Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?> / month</p>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Booking Details</h2>

                    <form method="POST" class="space-y-5">
                        <!-- Durasi -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Duration (months) *</label>
                            <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                                <?php foreach ([1, 2, 3, 6, 12] as $d): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="duration" value="<?php echo $d; ?>" 
                                               <?php echo (isset($_POST['duration']) && $_POST['duration'] == $d) || $d == 3 ? 'checked' : ''; ?> 
                                               class="hidden peer">
                                        <div class="text-center py-2 px-3 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                            <span class="text-sm font-medium peer-checked:text-purple-600"><?php echo $d; ?> month<?php echo $d > 1 ? 's' : ''; ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Guests -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Number of Guests</label>
                            <input type="number" name="guests" min="1" max="10" value="<?php echo $_POST['guests'] ?? 1; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500">
                        </div>

                        <!-- Personal Info -->
                        <div class="border-t border-gray-100 pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">👤 Tenant Information</h3>
                            
                            <div class="mb-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="use_account_data" id="useAccountData" checked class="w-4 h-4 text-purple-600 rounded">
                                    <span class="text-sm text-gray-600">Use my account data</span>
                                </label>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                    <input type="text" name="full_name" id="fullName" required
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                    <input type="email" name="email" id="email" required
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                                <input type="tel" name="phone" id="phone" required
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="+62 812 3456 7890"
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500">
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">📝 Notes / Catatan</label>
                                <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500" placeholder="Tambahkan catatan..."></textarea>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="border-t border-gray-100 pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">💳 Payment Method</h3>
                            <p class="text-sm text-gray-500 mb-3">Select your preferred payment method</p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <!-- Bank -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank Transfer</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <?php $banks = ['BCA', 'BRI', 'MANDIRI', 'BNI', 'BSI']; ?>
                                        <?php foreach ($banks as $bank): ?>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="payment_method" value="<?php echo $bank; ?>" 
                                                       <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == $bank ? 'checked' : ''; ?>
                                                       class="hidden peer">
                                                <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300 text-sm">
                                                    <?php echo $bank; ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- E-Wallet -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">E-Wallet</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <?php $ewallets = ['DANA', 'OVO', 'GOPAY']; ?>
                                        <?php foreach ($ewallets as $ew): ?>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="payment_method" value="<?php echo $ew; ?>" 
                                                       <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == $ew ? 'checked' : ''; ?>
                                                       class="hidden peer">
                                                <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300 text-sm">
                                                    <?php echo $ew; ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">💳 Booking Summary</h4>
                            <div class="flex justify-between text-sm text-gray-600"><span>Price per month</span><span>Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?></span></div>
                            <div class="flex justify-between text-sm text-gray-600 mt-1"><span>Duration</span><span id="durationDisplay">3 months</span></div>
                            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-gray-800">
                                <span>Total</span>
                                <span id="totalDisplay">Rp <?php echo number_format($property['price_per_month'] * 3, 0, ',', '.'); ?></span>
                            </div>
                            <div class="mt-2 p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                                <p class="text-xs text-yellow-700"><i class="fas fa-clock mr-1"></i> Payment must be completed within <strong>24 hours</strong></p>
                            </div>
                        </div>

                        <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                            <i class="fas fa-credit-card mr-2"></i> Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('useAccountData')?.addEventListener('change', function() {
    var fn = document.getElementById('fullName'), em = document.getElementById('email'), ph = document.getElementById('phone');
    if (this.checked) {
        fn.value = '<?php echo addslashes($user['full_name'] ?? ''); ?>';
        em.value = '<?php echo addslashes($user['email'] ?? ''); ?>';
        ph.value = '<?php echo addslashes($user['phone'] ?? ''); ?>';
        fn.readOnly = true; em.readOnly = true; ph.readOnly = true;
        fn.classList.add('bg-gray-100'); em.classList.add('bg-gray-100'); ph.classList.add('bg-gray-100');
    } else {
        fn.readOnly = false; em.readOnly = false; ph.readOnly = false;
        fn.classList.remove('bg-gray-100'); em.classList.remove('bg-gray-100'); ph.classList.remove('bg-gray-100');
        fn.value = ''; em.value = ''; ph.value = '';
    }
});
document.getElementById('useAccountData')?.dispatchEvent(new Event('change'));

document.querySelectorAll('input[name="duration"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var dur = parseInt(this.value);
        var price = <?php echo $property['price_per_month'] ?? 700000; ?>;
        var total = price * dur;
        document.getElementById('durationDisplay').textContent = dur + ' month' + (dur > 1 ? 's' : '');
        document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    });
});
</script>

<style>
.gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
input:focus, textarea:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
</style>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>