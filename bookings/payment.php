<?php
// bookings/payment.php - Halaman Payment dengan E-Wallet
$page_title = "Payment - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=payment.php');
    exit;
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = null;
$error = '';
$success = '';
$upload_error = '';
$cooldown_active = false;
$cooldown_data = null;

// ==============================================
// CEK COOLDOWN
// ==============================================
function checkCooldown($booking_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT cooldown_until FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
// CEK EXPIRED
// ==============================================
function checkExpired($booking_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT payment_expiry, status FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $now = new DateTime();
            $expiry = new DateTime($result['payment_expiry']);
            if ($now > $expiry && $result['status'] == 'pending') {
                $cooldown_time = new DateTime();
                $cooldown_time->modify('+2 minutes');
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'expired', cooldown_until = ? WHERE id = ?");
                $stmt->execute([$cooldown_time->format('Y-m-d H:i:s'), $booking_id]);
                return true;
            }
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// ==============================================
// AMBIL DATA BOOKING
// ==============================================
try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// ==============================================
// CEK STATUS BOOKING
// ==============================================
if (!$booking) {
    $cooldown_data = checkCooldown($booking_id);
    if ($cooldown_data['active']) {
        $error = "⏳ Booking is in cooldown. Please wait " . $cooldown_data['minutes'] . "m " . $cooldown_data['seconds'] . "s before booking again.";
        $cooldown_active = true;
    } else {
        header('Location: my_bookings.php');
        exit;
    }
} else {
    $is_expired = checkExpired($booking_id);
    if ($is_expired) {
        $cooldown_data = checkCooldown($booking_id);
        if ($cooldown_data['active']) {
            $error = "⏰ Payment has expired. Please wait " . $cooldown_data['minutes'] . "m " . $cooldown_data['seconds'] . "s before booking again.";
            $cooldown_active = true;
        } else {
            $error = "⏰ Payment has expired. Please book again.";
            $cooldown_active = false;
        }
        header('Refresh: 5; URL=my_bookings.php');
    }
    $cooldown_active = false;
}

// ==============================================
// PROSES UPLOAD BUKTI PEMBAYARAN
// ==============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_payment']) && $booking && !$cooldown_active) {
    $payment_method = $_POST['payment_method'] ?? 'BCA';
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $file = $_FILES['payment_proof'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        if (!in_array($file_ext, $allowed_ext)) {
            $upload_error = "❌ File type not allowed. Please upload JPG, PNG, GIF, or PDF.";
        } elseif ($file_size > 2097152) {
            $upload_error = "❌ File size too large. Max 2MB.";
        } else {
            $new_file_name = 'payment_' . $booking_id . '_' . time() . '.' . $file_ext;
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/payments/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
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
                        INSERT INTO payments (
                            booking_id, amount, payment_method, transaction_id, 
                            status, payment_date, payment_proof
                        ) VALUES (?, ?, ?, ?, 'success', NOW(), ?)
                    ");
                    $stmt->execute([
                        $booking_id,
                        $booking['total_price'],
                        $payment_method,
                        $transaction_id,
                        '/staynest/assets/uploads/payments/' . $new_file_name
                    ]);
                    
                    $_SESSION['success'] = "✅ Payment successful! Booking is now active.";
                    header('Location: booking_detail.php?id=' . $booking_id);
                    exit;
                    
                } catch (Exception $e) {
                    $upload_error = "❌ Payment failed: " . $e->getMessage();
                }
            } else {
                $upload_error = "❌ Failed to upload file. Please try again.";
            }
        }
    } else {
        $upload_error = "❌ Please upload your payment proof.";
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">💳 Payment</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($upload_error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($upload_error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($booking && !$cooldown_active): ?>
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <!-- Header Booking -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($booking['property_name']); ?></h2>
                    <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">⏳ Pending</span>
            </div>
            
            <!-- Detail Booking -->
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
                    <p class="font-bold text-red-600"><?php echo date('d M Y H:i', strtotime($booking['payment_expiry'])); ?></p>
                </div>
            </div>
            
            <!-- Virtual Account -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-purple-700"><i class="fas fa-info-circle mr-1"></i> Virtual Account</p>
                <p class="text-2xl font-bold text-purple-800 tracking-widest">
                    <?php echo '8880' . str_pad($booking['id'], 10, '0', STR_PAD_LEFT); ?>
                </p>
                <p class="text-xs text-purple-500 mt-1">Transfer to this virtual account number</p>
            </div>
            
            <!-- FORM UPLOAD -->
            <form method="POST" enctype="multipart/form-data" class="mb-6">
                <!-- Upload Bukti -->
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-purple-400 transition mb-6">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 block"></i>
                    <p class="text-gray-600 font-medium">Upload Payment Proof</p>
                    <p class="text-xs text-gray-400">JPG, PNG, GIF, PDF (Max 2MB)</p>
                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.gif,.pdf" 
                           class="mt-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                </div>
                
                <!-- ========================================== -->
                <!-- PAYMENT METHOD - DENGAN E-WALLET -->
                <!-- ========================================== -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-3">💳 Payment Method</label>
                    
                    <!-- Bank Transfer -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">
                            <i class="fas fa-university mr-1"></i> Bank Transfer
                        </p>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <?php $banks = ['BCA', 'BRI', 'BNI', 'MANDIRI', 'BSI']; ?>
                            <?php foreach ($banks as $bank): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="<?php echo $bank; ?>" 
                                           <?php echo $bank == 'BCA' ? 'checked' : ''; ?>
                                           class="hidden peer">
                                    <div class="text-center py-3 px-2 border-2 border-gray-200 rounded-xl peer-checked:border-purple-600 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                        <i class="fas fa-university text-purple-600 block mb-1"></i>
                                        <span class="text-xs md:text-sm font-medium peer-checked:text-purple-600"><?php echo $bank; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- E-Wallet -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">
                            <i class="fas fa-mobile-alt mr-1"></i> E-Wallet
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <!-- DANA -->
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="DANA" class="hidden peer">
                                <div class="text-center py-3 px-2 border-2 border-gray-200 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition hover:border-blue-300">
                                    <div class="w-10 h-10 mx-auto mb-1 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #108EE9, #0A6EBD);">
                                        <span class="text-white font-bold text-xs">DANA</span>
                                    </div>
                                    <span class="text-xs md:text-sm font-medium peer-checked:text-blue-600">DANA</span>
                                </div>
                            </label>
                            
                            <!-- GoPay -->
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="GOPAY" class="hidden peer">
                                <div class="text-center py-3 px-2 border-2 border-gray-200 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 transition hover:border-green-300">
                                    <div class="w-10 h-10 mx-auto mb-1 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #00AED6, #0084A8);">
                                        <span class="text-white font-bold text-xs">GO</span>
                                    </div>
                                    <span class="text-xs md:text-sm font-medium peer-checked:text-green-600">GoPay</span>
                                </div>
                            </label>
                            
                            <!-- OVO -->
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="OVO" class="hidden peer">
                                <div class="text-center py-3 px-2 border-2 border-gray-200 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 transition hover:border-purple-300">
                                    <div class="w-10 h-10 mx-auto mb-1 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #4C3494, #2E1F5E);">
                                        <span class="text-white font-bold text-xs">OVO</span>
                                    </div>
                                    <span class="text-xs md:text-sm font-medium peer-checked:text-purple-600">OVO</span>
                                </div>
                            </label>
                            
                            <!-- ShopeePay -->
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="SHOPEEPAY" class="hidden peer">
                                <div class="text-center py-3 px-2 border-2 border-gray-200 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50 transition hover:border-orange-300">
                                    <div class="w-10 h-10 mx-auto mb-1 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #EE4D2D, #C73E1D);">
                                        <span class="text-white font-bold text-[8px]">SHOPEE</span>
                                    </div>
                                    <span class="text-xs md:text-sm font-medium peer-checked:text-orange-600">ShopeePay</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Info E-Wallet -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-4">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>E-Wallet Payment:</strong> Transfer ke nomor <strong>0812-3456-7890</strong> (a/n StayNest) lalu upload bukti transfer.
                        </p>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="flex gap-4">
                    <button type="submit" name="upload_payment" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i> Confirm Payment
                    </button>
                    <a href="my_bookings.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                        Cancel
                    </a>
                </div>
            </form>
            
            <!-- Info -->
            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-xs text-yellow-700">
                    <i class="fas fa-clock mr-1"></i> 
                    You have <strong>24 hours</strong> to complete payment. 
                    If not paid, booking will be cancelled automatically.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>