<?php
// bookings/book_now.php - Halaman Booking Properti
$page_title = "Book Now - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=book_now.php');
    exit;
}

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$unit_number = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$property = null;
$error = '';
$is_extend = false;
$existing_booking = null;

// ==============================================
// CEK COOLDOWN SEBELUM BOOKING
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
                return [
                    'active' => true,
                    'minutes' => $diff->i,
                    'seconds' => $diff->s
                ];
            }
        }
        return ['active' => false];
    } catch (Exception $e) {
        return ['active' => false];
    }
}

// ==============================================
// CEK DOUBLE BOOKING
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
    
    if ($duration >= 12) {
        return ($property['price_per_year'] ?? $price_per_month * 12) / 12 * $duration;
    } elseif ($duration >= 6) {
        return ($property['price_per_6months'] ?? $price_per_month * 6) / 6 * $duration;
    } elseif ($duration >= 3) {
        return ($property['price_per_3months'] ?? $price_per_month * 3) / 3 * $duration;
    }
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
                   p.price_per_month, p.price_per_3months, p.price_per_6months, p.price_per_year, p.image_url
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
                'price_per_3months' => $existing_booking['price_per_3months'],
                'price_per_6months' => $existing_booking['price_per_6months'],
                'price_per_year' => $existing_booking['price_per_year'],
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
// CEK COOLDOWN USER
// ==============================================
if (empty($error) && !$is_extend) {
    $cooldown = checkUserCooldown($_SESSION['user_id']);
    if ($cooldown['active']) {
        $error = "⏳ You are in cooldown. Please wait " . $cooldown['minutes'] . "m " . $cooldown['seconds'] . "s before booking again.";
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
        $error = "Error loading property: " . $e->getMessage();
    }
} elseif (!$is_extend && $property_id == 0) {
    $error = "No property selected!";
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
    $unit_number_input = isset($_POST['unit_number']) ? (int)$_POST['unit_number'] : 0;
    $payment_type = $_POST['payment_type'] ?? 'full';

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
        $unit_number_input = $existing_booking['unit_number'] ?? 0;
    }

    // Validasi
    if (!in_array($duration, [1, 2, 3, 6, 12])) $error = "Select valid duration!";
    if (empty($full_name)) $error = "Full name is required!";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Valid email is required!";
    if (empty($phone)) $error = "Phone number is required!";
    if ($unit_number_input <= 0) $error = "Unit number is required!";
    if (!in_array($payment_type, ['full', 'monthly', 'quarterly', 'yearly'])) $error = "Invalid payment type!";

    // CEK COOLDOWN LAGI (untuk booking baru)
    if (empty($error) && !$is_extend_booking) {
        $cooldown = checkUserCooldown($_SESSION['user_id']);
        if ($cooldown['active']) {
            $error = "⏳ You are in cooldown. Please wait " . $cooldown['minutes'] . "m " . $cooldown['seconds'] . "s before booking again.";
        }
    }

    // CEK DOUBLE BOOKING
    if (empty($error) && !$is_extend_booking) {
        $check_in = date('Y-m-d');
        $check_out = date('Y-m-d', strtotime("+$duration months"));
        if (isUnitBooked($property_id, $unit_number_input, $check_in, $check_out)) {
            $error = "⚠️ Unit " . $unit_number_input . " is already booked for this period!";
        }
    }

    if (empty($error)) {
        try {
            // EXTEND
            if ($is_extend_booking && $booking_id > 0) {
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status IN ('active', 'pending')");
                $stmt->execute([$booking_id, $_SESSION['user_id']]);
                $old = $stmt->fetch();
                if ($old) {
                    $price_per_month = calculatePrice($property, $duration) / $duration;
                    $new_total = $old['total_price'] + ($price_per_month * $duration);
                    $new_check_out = date('Y-m-d', strtotime($old['check_out'] . " +$duration months"));
                    $new_duration = $old['duration_months'] + $duration;

                    $stmt = $pdo->prepare("
                        UPDATE bookings SET 
                            check_out = ?,
                            duration_months = ?,
                            total_price = ?,
                            status = 'extended',
                            updated_at = NOW(),
                            full_name = ?,
                            email = ?,
                            phone = ?,
                            guests = ?,
                            notes = ?
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([
                        $new_check_out,
                        $new_duration,
                        $new_total,
                        $full_name,
                        $email,
                        $phone,
                        $guests,
                        $notes,
                        $booking_id,
                        $_SESSION['user_id']
                    ]);

                    $_SESSION['success'] = "✅ Booking extended successfully!";
                    header('Location: my_bookings.php');
                    exit;
                } else {
                    $error = "Original booking not found!";
                }
            }
            // BOOKING BARU
            else {
                $booking_code = 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                $total_price = calculatePrice($property, $duration);
                $check_in = date('Y-m-d');
                $check_out = date('Y-m-d', strtotime("+$duration months"));
                $payment_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $stmt = $pdo->prepare("
                    INSERT INTO bookings (
                        property_id,
                        user_id,
                        unit_number,
                        booking_code,
                        check_in,
                        check_out,
                        duration_months,
                        total_price,
                        guests,
                        full_name,
                        email,
                        phone,
                        notes,
                        status,
                        payment_status,
                        payment_method,
                        payment_expiry
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                $stmt->execute([
                    $property_id,
                    $_SESSION['user_id'],
                    $unit_number_input,
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
                    'pending',
                    'unpaid',
                    $payment_type,
                    $payment_expiry
                ]);

                $booking_id = $pdo->lastInsertId();
                if ($booking_id > 0) {
                    header('Location: payment.php?id=' . $booking_id);
                    exit;
                } else {
                    $error = "Booking failed!";
                }
            }
        } catch (Exception $e) {
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">
        <?php echo $is_extend ? '🔄 Extend Booking' : '📝 Book Property'; ?>
    </h1>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$property): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> Property not found.
            <a href="/staynest/properties.php" class="text-purple-600 hover:underline ml-2">← Back to Properties</a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Property Info -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <div class="h-40 rounded-xl overflow-hidden bg-gradient-to-r from-purple-400 to-blue-400">
                        <?php 
                            $img = !empty($property['image_url']) ? $property['image_url'] : '/staynest/assets/images/default-property.jpg';
                            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img)) $img = '/staynest/assets/images/default-property.jpg';
                        ?>
                        <img src="<?php echo $img; ?>" 
                             alt="<?php echo htmlspecialchars($property['name'] ?? 'Property'); ?>" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-white text-4xl\'><i class=\'fas fa-home\'></i></div>';">
                    </div>
                    <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($property['name'] ?? 'Property'); ?></h3>
                    <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location'] ?? ''); ?></p>
                    <p class="text-purple-600 font-bold mt-2">Rp <?php echo number_format($property['price_per_month'] ?? 700000, 0, ',', '.'); ?> / month</p>
                    
                    <?php if ($is_extend && $existing_booking): ?>
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-700"><i class="fas fa-info-circle mr-1"></i> Current booking ends: <strong><?php echo date('d M Y', strtotime($existing_booking['check_out'])); ?></strong></p>
                            <p class="text-sm text-blue-700 mt-1">👤 <?php echo htmlspecialchars($existing_booking['full_name']); ?></p>
                            <p class="text-sm text-blue-700 mt-1">🏠 Unit: <?php echo $existing_booking['unit_number'] ?? '-'; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Booking Details</h2>

                    <form method="POST" class="space-y-5">
                        <?php if ($is_extend && $existing_booking): ?>
                            <input type="hidden" name="is_extend" value="1">
                            <input type="hidden" name="booking_id" value="<?php echo $existing_booking['id']; ?>">
                            <input type="hidden" name="unit_number" value="<?php echo $existing_booking['unit_number'] ?? 0; ?>">
                        <?php else: ?>
                            <input type="hidden" name="unit_number" value="<?php echo $unit_number; ?>">
                        <?php endif; ?>

                        <!-- Unit Number -->
                        <?php if (!$is_extend): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">🏠 Select Unit *</label>
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
                                               class="hidden peer">
                                        <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg transition peer-checked:border-purple-600 peer-checked:bg-purple-50 hover:border-purple-300 <?php echo $booked ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'border-gray-200'; ?>">
                                            <span class="text-sm font-medium peer-checked:text-purple-600"><?php echo $i; ?></span>
                                            <?php if ($booked): ?>
                                                <span class="block text-[8px] text-red-500">🔴 Booked</span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($unit_number == 0): ?>
                                    <p class="text-sm text-yellow-600 mt-2"><i class="fas fa-info-circle mr-1"></i> Please select a unit</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Durasi -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Duration (months) *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <?php foreach ([1, 2, 3, 6, 12] as $d): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="duration" value="<?php echo $d; ?>" 
                                           <?php echo (isset($_POST['duration']) && $_POST['duration'] == $d) || $d == 3 ? 'checked' : ''; ?> 
                                           class="hidden peer">
                                    <div class="text-center py-2 px-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                        <span class="text-sm font-medium peer-checked:text-purple-600"><?php echo $d; ?> month<?php echo $d > 1 ? 's' : ''; ?></span>
                                        <?php 
                                        $price = calculatePrice($property, $d);
                                        ?>
                                        <span class="block text-xs text-gray-500">Rp <?php echo number_format($price, 0, ',', '.'); ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Payment Type -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">💳 Payment Type</label>
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
                                           <?php echo (isset($_POST['payment_type']) && $_POST['payment_type'] == $key) || $key == 'full' ? 'checked' : ''; ?> 
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
                            <label class="block text-gray-700 font-medium mb-2">Number of Guests</label>
                            <input type="number" name="guests" min="1" max="10" 
                                   value="<?php echo isset($_POST['guests']) ? $_POST['guests'] : ($existing_booking['guests'] ?? 1); ?>" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                        </div>

                        <!-- Tenant Information -->
                        <div class="border-t border-gray-100 pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">👤 Tenant Information</h3>

                            <?php if ($is_extend && $existing_booking): ?>
                                <div class="mb-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                                    <p class="text-sm font-semibold text-yellow-800 mb-2"><i class="fas fa-info-circle mr-1"></i> Data Extension Options</p>
                                    <div class="flex flex-col gap-2">
                                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-yellow-100 transition">
                                            <input type="radio" name="use_old_data" value="1" checked class="w-4 h-4 text-purple-600">
                                            <span><span class="font-medium">Use Existing Data</span> <span class="text-xs text-gray-500 block">👤 <?php echo htmlspecialchars($existing_booking['full_name']); ?> | 📧 <?php echo htmlspecialchars($existing_booking['email']); ?></span></span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-yellow-100 transition">
                                            <input type="radio" name="use_old_data" value="0" class="w-4 h-4 text-purple-600">
                                            <span><span class="font-medium">Use New Data</span> <span class="text-xs text-gray-500 block">Fill in new tenant information below</span></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!$is_extend): ?>
                                <div class="mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="use_account_data" id="useAccountData" checked class="w-4 h-4 text-purple-600 rounded">
                                        <span class="text-sm text-gray-600">Use my account data</span>
                                    </label>
                                    <p class="text-xs text-gray-400 mt-1">Uncheck to fill in different tenant data</p>
                                </div>
                            <?php endif; ?>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                    <input type="text" name="full_name" id="fullName" required
                                           value="<?php echo htmlspecialchars($is_extend && $existing_booking ? $existing_booking['full_name'] : ($user['full_name'] ?? '')); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                    <input type="email" name="email" id="email" required
                                           value="<?php echo htmlspecialchars($is_extend && $existing_booking ? $existing_booking['email'] : ($user['email'] ?? '')); ?>"
                                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                                <input type="tel" name="phone" id="phone" required
                                       value="<?php echo htmlspecialchars($is_extend && $existing_booking ? $existing_booking['phone'] : ($user['phone'] ?? '')); ?>"
                                       placeholder="+62 812 3456 7890"
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                            </div>

                            <div class="mt-4">
                                <label class="block text-gray-700 font-medium mb-2">📝 Notes / Catatan</label>
                                <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition" placeholder="Tambahkan catatan untuk properti ini..."><?php echo htmlspecialchars($_POST['notes'] ?? ($existing_booking['notes'] ?? '')); ?></textarea>
                                <p class="text-xs text-gray-400 mt-1">* Catatan ini bisa diupdate kapan saja</p>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">💳 Booking Summary</h4>
                            <div class="flex justify-between text-sm text-gray-600"><span>Duration</span><span id="durationDisplay">3 months</span></div>
                            <?php if ($is_extend && $existing_booking): ?>
                                <div class="flex justify-between text-sm text-gray-600 mt-1"><span>Current total</span><span>Rp <?php echo number_format($existing_booking['total_price'], 0, ',', '.'); ?></span></div>
                            <?php endif; ?>
                            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-gray-800">
                                <span>Total</span>
                                <span id="totalDisplay">Rp <?php echo number_format(calculatePrice($property, 3), 0, ',', '.'); ?></span>
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
<?php if ($is_extend && $existing_booking): ?>
document.querySelectorAll('input[name="use_old_data"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var fn = document.getElementById('fullName'), em = document.getElementById('email'), ph = document.getElementById('phone');
        if (this.value == 1) {
            fn.value = '<?php echo addslashes($existing_booking['full_name']); ?>';
            em.value = '<?php echo addslashes($existing_booking['email']); ?>';
            ph.value = '<?php echo addslashes($existing_booking['phone']); ?>';
            fn.readOnly = true; em.readOnly = true; ph.readOnly = true;
            fn.classList.add('bg-gray-100'); em.classList.add('bg-gray-100'); ph.classList.add('bg-gray-100');
        } else {
            fn.value = ''; em.value = ''; ph.value = '';
            fn.readOnly = false; em.readOnly = false; ph.readOnly = false;
            fn.classList.remove('bg-gray-100'); em.classList.remove('bg-gray-100'); ph.classList.remove('bg-gray-100');
        }
    });
});
document.querySelector('input[name="use_old_data"][value="1"]')?.click();
<?php endif; ?>

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

function calculateTotal(duration) {
    var pricePerMonth = <?php echo $property['price_per_month'] ?? 700000; ?>;
    var price3 = <?php echo $property['price_per_3months'] ?? $property['price_per_month'] * 3; ?>;
    var price6 = <?php echo $property['price_per_6months'] ?? $property['price_per_month'] * 6; ?>;
    var price12 = <?php echo $property['price_per_year'] ?? $property['price_per_month'] * 12; ?>;
    
    if (duration >= 12) return (price12 / 12) * duration;
    if (duration >= 6) return (price6 / 6) * duration;
    if (duration >= 3) return (price3 / 3) * duration;
    return pricePerMonth * duration;
}

document.querySelectorAll('input[name="duration"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var dur = parseInt(this.value);
        var total = calculateTotal(dur);
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