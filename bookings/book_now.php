<?php
// bookings/book_now.php - Halaman Booking Properti (REVISI FINAL)
$page_title = "Book Now - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/functions.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=book_now.php');
    exit;
}

// ==============================================
// BACA PARAMETER - SUPPORT 'id' DAN 'property_id'
// ==============================================
$property_id = 0;

if (isset($_GET['property_id']) && $_GET['property_id'] > 0) {
    $property_id = (int)$_GET['property_id'];
} elseif (isset($_GET['id']) && $_GET['id'] > 0) {
    $property_id = (int)$_GET['id'];
}

$unit_number = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$property = null;
$error = '';
$is_extend = false;
$existing_booking = null;

// ==============================================
// FUNGSI CEK COOLDOWN
// ==============================================
function checkUserCooldown($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT cooldown_until FROM bookings 
            WHERE user_id = ? 
            AND status = 'expired'
            AND cooldown_until > NOW()
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        if ($result && !empty($result['cooldown_until'])) {
            $now = new DateTime();
            $cooldown = new DateTime($result['cooldown_until']);
            if ($now < $cooldown) {
                $diff = $now->diff($cooldown);
                return ['active' => true, 'minutes' => $diff->i, 'seconds' => $diff->s];
            }
        }
        return ['active' => false];
    } catch (Exception $e) {
        return ['active' => false];
    }
}

// ==============================================
// FUNGSI CEK UNIT SUDAH DIBOOKING
// ==============================================
function isUnitBooked($property_id, $unit_number, $check_in, $check_out) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM bookings 
            WHERE property_id = ? 
            AND unit_number = ? 
            AND status IN ('pending', 'active', 'extended')
            AND (
                (check_in <= ? AND check_out > ?) OR
                (check_in < ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )
        ");
        $stmt->execute([$property_id, $unit_number, $check_out, $check_in, $check_out, $check_in, $check_in, $check_out]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// ==============================================
// FUNGSI HITUNG HARGA
// ==============================================
function calculatePrice($property, $duration) {
    $price_per_month = $property['price_per_month'] ?? 700000;
    return $price_per_month * $duration;
}

// ==============================================
// CEK EXTEND
// ==============================================
if (isset($_GET['extend']) && $_GET['extend'] == 1 && isset($_GET['booking_id'])) {
    $is_extend = true;
    $booking_id = (int)$_GET['booking_id'];
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, p.name as property_name, p.location as property_location, 
                   p.price_per_month, p.image_url
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            WHERE b.id = ? AND b.user_id = ? AND b.status IN ('active', 'pending')
        ");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $existing_booking = $stmt->fetch();
        if ($existing_booking) {
            $property_id = $existing_booking['property_id'];
            $unit_number = $existing_booking['unit_number'] ?? 0;
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

// ==============================================
// CEK COOLDOWN
// ==============================================
if (empty($error) && !$is_extend) {
    $cooldown = checkUserCooldown($_SESSION['user_id']);
    if ($cooldown['active']) {
        $error = "⏳ You are in cooldown. Please wait " . $cooldown['minutes'] . "m " . $cooldown['seconds'] . "s.";
    }
}

// ==============================================
// AMBIL PROPERTI
// ==============================================
if (!$is_extend && $property_id > 0 && empty($error)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch();
        if (!$property) $error = "Property not found!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
} elseif (!$is_extend && $property_id == 0) {
    $error = "No property selected! Silakan pilih properti dulu.";
}

// ==============================================
// AMBIL DATA USER
// ==============================================
$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {}

// ==============================================
// PROSES BOOKING
// ==============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $property && empty($error)) {
    $duration = (int)($_POST['duration'] ?? 0);
    $guests = (int)($_POST['guests'] ?? 1);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $unit_number_input = isset($_POST['unit_number']) ? (int)$_POST['unit_number'] : 0;
    $payment_type = $_POST['payment_type'] ?? 'full';

    // Validasi
    if (!in_array($duration, [1, 2, 3, 6, 12])) $error = "Select valid duration!";
    if (empty($full_name)) $error = "Full name is required!";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Valid email is required!";
    if (empty($phone)) $error = "Phone number is required!";
    if ($unit_number_input <= 0) $error = "Please select a unit!";

    // Cek double booking
    if (empty($error)) {
        $check_in = date('Y-m-d');
        $check_out = date('Y-m-d', strtotime("+$duration months"));
        if (isUnitBooked($property_id, $unit_number_input, $check_in, $check_out)) {
            $error = "⚠️ Unit " . $unit_number_input . " sudah dibooking untuk periode ini!";
        }
    }

    // Insert booking
    if (empty($error)) {
        try {
            $booking_code = 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $total_price = calculatePrice($property, $duration);
            $check_in = date('Y-m-d');
            $check_out = date('Y-m-d', strtotime("+$duration months"));
            $payment_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    property_id, user_id, unit_number, booking_code, check_in, check_out,
                    duration_months, total_price, guests, full_name, email, phone, notes,
                    status, payment_status, payment_method, payment_expiry
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, ?)
            ");
            $stmt->execute([
                $property_id, $_SESSION['user_id'], $unit_number_input, $booking_code,
                $check_in, $check_out, $duration, $total_price, $guests,
                $full_name, $email, $phone, $notes, $payment_type, $payment_expiry
            ]);

            $booking_id = $pdo->lastInsertId();
            if ($booking_id > 0) {
                $_SESSION['success'] = "✅ Booking berhasil dibuat!";
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

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">
        📝 Book Property
    </h1>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$property): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> Property not found.
            <a href="/staynest/properties.php" class="text-purple-600 hover:underline ml-2 font-semibold">← Kembali ke Properties</a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Property Info -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <div class="h-40 rounded-xl overflow-hidden bg-gradient-to-r from-purple-400 to-blue-400">
                        <?php 
                            $img = getPropertyImage($property['id']);
                        ?>
                        <img src="<?php echo $img; ?>" 
                             alt="<?php echo htmlspecialchars($property['name']); ?>" 
                             class="w-full h-full object-cover"
                             onerror="this.src='/staynest/assets/images/default-property.jpg'">
                    </div>
                    <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                    <p class="text-purple-600 font-bold mt-2 text-lg"><?php echo formatRupiah($property['price_per_month']); ?> / bulan</p>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Detail Booking</h2>

                    <form method="POST" class="space-y-5">
                        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

                        <!-- Pilih Unit -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">🏠 Pilih Unit *</label>
                            <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                                <?php for ($i = 1; $i <= ($property['total_doors'] ?? 4); $i++): 
                                    $check_in = date('Y-m-d');
                                    $check_out = date('Y-m-d', strtotime('+3 months'));
                                    $booked = isUnitBooked($property_id, $i, $check_in, $check_out);
                                ?>
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="unit_number" value="<?php echo $i; ?>" 
                                           <?php echo ($unit_number == $i) ? 'checked' : ''; ?>
                                           <?php echo $booked ? 'disabled' : ''; ?>
                                           class="hidden peer" required>
                                    <div class="text-center py-2 px-2 border-2 rounded-lg transition peer-checked:border-purple-600 peer-checked:bg-purple-50 hover:border-purple-300 <?php echo $booked ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-gray-200'; ?>">
                                        <span class="text-sm font-medium"><?php echo $i; ?></span>
                                        <?php if ($booked): ?>
                                            <span class="block text-[8px] text-red-500">🔴 Booked</span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Durasi -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">⏱️ Durasi (bulan) *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <?php foreach ([1, 2, 3, 6, 12] as $d): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="duration" value="<?php echo $d; ?>" 
                                           <?php echo ($d == 3) ? 'checked' : ''; ?> 
                                           class="hidden peer" required>
                                    <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                        <span class="text-sm font-medium peer-checked:text-purple-600"><?php echo $d; ?> bulan</span>
                                        <span class="block text-xs text-gray-500"><?php echo formatRupiah(calculatePrice($property, $d)); ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Payment Type -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">💳 Metode Pembayaran</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <?php 
                                $payment_types = [
                                    'full' => 'Full Payment',
                                    'monthly' => 'Monthly',
                                    'quarterly' => 'Quarterly',
                                    'yearly' => 'Yearly'
                                ];
                                foreach ($payment_types as $key => $label):
                                ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_type" value="<?php echo $key; ?>" 
                                           <?php echo ($key == 'full') ? 'checked' : ''; ?> 
                                           class="hidden peer">
                                    <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-green-600 peer-checked:bg-green-50 transition hover:border-green-300">
                                        <span class="text-sm font-medium peer-checked:text-green-600"><?php echo $label; ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Guests -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">👥 Jumlah Penghuni</label>
                            <input type="number" name="guests" min="1" max="10" value="1" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                        </div>

                        <!-- Tenant Info -->
                        <div class="border-t border-gray-100 pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">👤 Data Penyewa</h3>

                            <div class="mb-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="useAccountData" name="use_account_data" checked class="w-4 h-4 text-purple-600 rounded">
                                    <span class="text-sm text-gray-600">Gunakan data akun saya</span>
                                </label>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Nama Lengkap *</label>
                                    <input type="text" name="full_name" id="fullName" required
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                    <input type="email" name="email" id="email" required
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">No. Telepon *</label>
                                <input type="tel" name="phone" id="phone" required
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="+62 812 3456 7890"
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">📝 Catatan</label>
                                <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition" placeholder="Catatan tambahan..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">💳 Ringkasan Booking</h4>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Durasi</span>
                                <span id="durationDisplay">3 bulan</span>
                            </div>
                            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-gray-800">
                                <span>Total</span>
                                <span id="totalDisplay"><?php echo formatRupiah(calculatePrice($property, 3)); ?></span>
                            </div>
                            <div class="mt-2 p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                                <p class="text-xs text-yellow-700"><i class="fas fa-clock mr-1"></i> Pembayaran harus diselesaikan dalam <strong>24 jam</strong></p>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-4 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105 text-lg">
                            <i class="fas fa-credit-card mr-2"></i> Lanjut ke Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
var userData = {
    full_name: '<?php echo addslashes($user['full_name'] ?? ''); ?>',
    email: '<?php echo addslashes($user['email'] ?? ''); ?>',
    phone: '<?php echo addslashes($user['phone'] ?? ''); ?>'
};

var useAccountData = document.getElementById('useAccountData');
if (useAccountData) {
    useAccountData.addEventListener('change', function() {
        var fn = document.getElementById('fullName');
        var em = document.getElementById('email');
        var ph = document.getElementById('phone');
        
        if (this.checked) {
            fn.value = userData.full_name;
            em.value = userData.email;
            ph.value = userData.phone;
            fn.readOnly = true;
            em.readOnly = true;
            ph.readOnly = true;
            fn.classList.add('bg-gray-100');
            em.classList.add('bg-gray-100');
            ph.classList.add('bg-gray-100');
        } else {
            fn.readOnly = false;
            em.readOnly = false;
            ph.readOnly = false;
            fn.classList.remove('bg-gray-100');
            em.classList.remove('bg-gray-100');
            ph.classList.remove('bg-gray-100');
        }
    });
    useAccountData.dispatchEvent(new Event('change'));
}

var pricePerMonth = <?php echo (int)($property['price_per_month'] ?? 700000); ?>;

function calculateTotal(duration) {
    return pricePerMonth * duration;
}

function formatRupiahJS(number) {
    return 'Rp ' + number.toLocaleString('id-ID');
}

document.querySelectorAll('input[name="duration"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var dur = parseInt(this.value);
        var total = calculateTotal(dur);
        document.getElementById('durationDisplay').textContent = dur + ' bulan';
        document.getElementById('totalDisplay').textContent = formatRupiahJS(total);
    });
});
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>