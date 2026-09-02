<?php
// bookings/book_now.php - Halaman Booking Properti
$page_title = "Book Now - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=book_now.php');
    exit;
}

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;
$error = '';
$success = '';
$is_extend = false;
$existing_booking = null;

// Cek apakah ini perpanjangan (extend)
if (isset($_GET['extend']) && $_GET['extend'] == 1 && isset($_GET['booking_id'])) {
    $is_extend = true;
    $booking_id = (int)$_GET['booking_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, p.name as property_name, p.location as property_location, p.price_per_month 
                               FROM bookings b 
                               JOIN properties p ON b.property_id = p.id 
                               WHERE b.id = ? AND b.user_id = ? AND b.status = 'active'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $existing_booking = $stmt->fetch();
        
        if ($existing_booking) {
            $property_id = $existing_booking['property_id'];
            $property = [
                'id' => $existing_booking['property_id'],
                'name' => $existing_booking['property_name'],
                'location' => $existing_booking['property_location'],
                'price_per_month' => $existing_booking['price_per_month']
            ];
        } else {
            $error = "Active booking not found!";
        }
    } catch(Exception $e) {
        $error = "Error loading booking: " . $e->getMessage();
    }
}

// Jika bukan extend, ambil data properti dari database
if (!$is_extend && $property_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch();
        if (!$property) {
            $error = "Property not found!";
        }
    } catch(Exception $e) {
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
} catch(Exception $e) {}

// Proses Booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $duration = (int)($_POST['duration'] ?? 0);
    $guests = (int)($_POST['guests'] ?? 1);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $use_account_data = isset($_POST['use_account_data']) ? true : false;
    $is_extend_booking = isset($_POST['is_extend']) ? true : false;
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    
    if ($use_account_data && $user) {
        $full_name = $user['full_name'];
        $email = $user['email'];
        $phone = $user['phone'];
    }
    
    $allowed_durations = [1, 2, 3, 6, 12];
    if (!in_array($duration, $allowed_durations)) {
        $error = "Please select a valid duration!";
    }
    
    if (empty($full_name)) $error = "Full name is required!";
    if (empty($email)) $error = "Email is required!";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Valid email is required!";
    if (empty($phone)) $error = "Phone number is required!";
    
    if (empty($error)) {
        try {
            // ==========================================
            // KALAU EXTEND BOOKING
            // ==========================================
            if ($is_extend_booking && $booking_id > 0) {
                // Ambil booking lama
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'active'");
                $stmt->execute([$booking_id, $_SESSION['user_id']]);
                $old_booking = $stmt->fetch();
                
                if ($old_booking) {
                    // Hitung total baru
                    $price_per_month = $old_booking['total_price'] / $old_booking['duration_months'];
                    $new_total = $old_booking['total_price'] + ($price_per_month * $duration);
                    $new_check_out = date('Y-m-d', strtotime($old_booking['check_out'] . " +$duration months"));
                    $new_duration = $old_booking['duration_months'] + $duration;
                    
                    // Update booking
                    $stmt = $pdo->prepare("
                        UPDATE bookings SET 
                            check_out = ?,
                            duration_months = ?,
                            total_price = ?,
                            status = 'extended',
                            updated_at = NOW()
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$new_check_out, $new_duration, $new_total, $booking_id, $_SESSION['user_id']]);
                    
                    // Insert ke history
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO booking_histories (booking_id, action, extended_months, new_check_out, created_at)
                            VALUES (?, 'extended', ?, ?, NOW())
                        ");
                        $stmt->execute([$booking_id, $duration, $new_check_out]);
                    } catch(Exception $e) {
                        // History table mungkin belum ada
                    }
                    
                    $_SESSION['success'] = "Booking extended successfully! New check-out: " . date('d M Y', strtotime($new_check_out));
                    header('Location: my_bookings.php');
                    exit;
                } else {
                    $error = "Original booking not found!";
                }
            }
            
            // ==========================================
            // KALAU BOOKING BARU
            // ==========================================
            else {
                $booking_code = 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                $price_per_month = $property['price_per_month'] ?? 700000;
                $total_price = $price_per_month * $duration;
                $check_in = date('Y-m-d');
                $check_out = date('Y-m-d', strtotime("+$duration months"));
                
                // Cek apakah kolom created_at ada
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'created_at'");
                    if ($stmt->rowCount() == 0) {
                        $pdo->exec("ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                        $pdo->exec("ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                    }
                } catch(Exception $e) {}
                
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (
                        property_id, user_id, booking_code, check_in, check_out,
                        duration_months, total_price, guests, full_name, email, phone, notes,
                        status, payment_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')
                ");
                $result = $stmt->execute([
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
                    $notes
                ]);
                
                if ($result) {
                    $booking_id = $pdo->lastInsertId();
                    if ($booking_id > 0) {
                        header('Location: booking_detail.php?id=' . $booking_id);
                        exit;
                    } else {
                        $error = "Booking failed: Could not retrieve booking ID.";
                    }
                } else {
                    $error = "Booking failed: Database insert error.";
                }
            }
        } catch(Exception $e) {
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
    
    <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if(!$property): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> Property not found.
            <a href="/staynest/properties.php" class="text-purple-600 hover:underline ml-2">← Back to Properties</a>
        </div>
    <?php else: ?>
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Property Info -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                <div class="h-40 bg-gradient-to-r from-purple-400 to-blue-400 rounded-xl flex items-center justify-center text-white text-4xl mb-4">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($property['name'] ?? 'Property'); ?></h3>
                <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($property['location'] ?? ''); ?></p>
                <p class="text-purple-600 font-bold mt-2">Rp <?php echo number_format($property['price_per_month'] ?? 700000, 0, ',', '.'); ?> / month</p>
                
                <?php if($is_extend && $existing_booking): ?>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Current booking ends: <strong><?php echo date('d M Y', strtotime($existing_booking['check_out'])); ?></strong>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Booking Details</h2>
                
                <form method="POST" class="space-y-5">
                    <?php if($is_extend && $existing_booking): ?>
                        <input type="hidden" name="is_extend" value="1">
                        <input type="hidden" name="booking_id" value="<?php echo $existing_booking['id']; ?>">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-1"></i>
                                You are extending your booking for <strong><?php echo htmlspecialchars($property['name']); ?></strong>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Durasi -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Duration (months) *</label>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                            <?php $durations = [1, 2, 3, 6, 12]; ?>
                            <?php foreach($durations as $d): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="duration" value="<?php echo $d; ?>" 
                                       <?php echo isset($_POST['duration']) && $_POST['duration'] == $d ? 'checked' : ($d == 3 ? 'checked' : ''); ?>
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
                        <input type="number" name="guests" min="1" max="10" value="<?php echo $_POST['guests'] ?? ($existing_booking['guests'] ?? 1); ?>" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                    </div>
                    
                    <!-- Personal Info -->
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">👤 Tenant Information</h3>
                        
                        <div class="mb-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="use_account_data" id="useAccountData" checked
                                       class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                <span class="text-sm text-gray-600">Use my account data</span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1">Uncheck to fill in different tenant data</p>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" name="full_name" id="fullName" required 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? $_POST['full_name'] ?? ($existing_booking['full_name'] ?? '')); ?>" 
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                <input type="email" name="email" id="email" required 
                                       value="<?php echo htmlspecialchars($user['email'] ?? $_POST['email'] ?? ($existing_booking['email'] ?? '')); ?>" 
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" required 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? $_POST['phone'] ?? ($existing_booking['phone'] ?? '')); ?>" 
                                   placeholder="+62 812 3456 7890"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-gray-700 font-medium mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition"><?php echo htmlspecialchars($_POST['notes'] ?? ($existing_booking['notes'] ?? '')); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">💳 Booking Summary</h4>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Price per month</span>
                            <span>Rp <?php echo number_format($property['price_per_month'] ?? 700000, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-1">
                            <span>Duration</span>
                            <span id="durationDisplay">3 months</span>
                        </div>
                        <?php if($is_extend && $existing_booking): ?>
                            <div class="flex justify-between text-sm text-gray-600 mt-1">
                                <span>Current total</span>
                                <span>Rp <?php echo number_format($existing_booking['total_price'], 0, ',', '.'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-gray-800">
                            <span>Total</span>
                            <span id="totalDisplay">Rp <?php echo number_format(($property['price_per_month'] ?? 700000) * 3, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i> 
                        <?php echo $is_extend ? 'Confirm Extension' : 'Confirm Booking'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Toggle form fields based on checkbox
document.getElementById('useAccountData')?.addEventListener('change', function() {
    var fullName = document.getElementById('fullName');
    var email = document.getElementById('email');
    var phone = document.getElementById('phone');
    
    var userFullName = '<?php echo addslashes($user['full_name'] ?? ''); ?>';
    var userEmail = '<?php echo addslashes($user['email'] ?? ''); ?>';
    var userPhone = '<?php echo addslashes($user['phone'] ?? ''); ?>';
    var existingFullName = '<?php echo addslashes($existing_booking['full_name'] ?? ''); ?>';
    var existingEmail = '<?php echo addslashes($existing_booking['email'] ?? ''); ?>';
    var existingPhone = '<?php echo addslashes($existing_booking['phone'] ?? ''); ?>';
    
    if (this.checked) {
        fullName.value = userFullName || existingFullName;
        email.value = userEmail || existingEmail;
        phone.value = userPhone || existingPhone;
        fullName.readOnly = true;
        email.readOnly = true;
        phone.readOnly = true;
        fullName.classList.add('bg-gray-100');
        email.classList.add('bg-gray-100');
        phone.classList.add('bg-gray-100');
    } else {
        fullName.readOnly = false;
        email.readOnly = false;
        phone.readOnly = false;
        fullName.classList.remove('bg-gray-100');
        email.classList.remove('bg-gray-100');
        phone.classList.remove('bg-gray-100');
        fullName.value = existingFullName || '';
        email.value = existingEmail || '';
        phone.value = existingPhone || '';
    }
});

document.getElementById('useAccountData')?.dispatchEvent(new Event('change'));

// Update summary when duration changes
document.querySelectorAll('input[name="duration"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var duration = parseInt(this.value);
        var pricePerMonth = <?php echo $property['price_per_month'] ?? 700000; ?>;
        var total = pricePerMonth * duration;
        <?php if($is_extend && $existing_booking): ?>
            total += <?php echo $existing_booking['total_price']; ?>;
        <?php endif; ?>
        
        document.getElementById('durationDisplay').textContent = duration + ' month' + (duration > 1 ? 's' : '');
        document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    });
});
</script>

<style>
.gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
input:focus, textarea:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
</style>

<?php require_once dirname(__FILE__) . '/../includes/footer.php; ?>