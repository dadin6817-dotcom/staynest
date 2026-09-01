<?php
// bookings/my_bookings.php - Halaman My Bookings
// ==============================================
// SEMUA LOGIKA PHP DIATAS SEBELUM INCLUDE HEADER
// ==============================================

$page_title = "My Bookings - StayNest";

// Load database dulu
require_once dirname(__FILE__) . '/../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_bookings.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$bookings = [];
$active_bookings = [];
$completed_bookings = [];
$error = '';
$success = '';

// ==============================================
// CEK & TAMBAHKAN KOLOM created_at JIKA BELUM ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'created_at'");
    $hasCreatedAt = $stmt->rowCount() > 0;
    
    if (!$hasCreatedAt) {
        // Tambahkan kolom created_at
        $pdo->exec("ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        $pdo->exec("ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
} catch (Exception $e) {
    // Abaikan error jika kolom sudah ada
}

// ==============================================
// AMBIL BOOKING DARI DATABASE
// ==============================================
try {
    // Gunakan COALESCE agar aman jika kolom created_at belum ada
    $orderBy = "COALESCE(b.created_at, b.id) DESC";
    
    $stmt = $pdo->prepare("
        SELECT 
            b.*, 
            p.name as property_name, 
            p.location as property_location 
        FROM bookings b 
        JOIN properties p ON b.property_id = p.id 
        WHERE b.user_id = ? 
        ORDER BY " . $orderBy . "
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
    
    foreach ($bookings as $booking) {
        if ($booking['status'] == 'active' || $booking['status'] == 'pending') {
            $active_bookings[] = $booking;
        } elseif ($booking['status'] == 'completed' || $booking['status'] == 'extended' || $booking['status'] == 'cancelled') {
            $completed_bookings[] = $booking;
        }
    }
} catch (Exception $e) {
    // Jika error, coba tanpa ORDER BY
    try {
        $stmt = $pdo->prepare("
            SELECT 
                b.*, 
                p.name as property_name, 
                p.location as property_location 
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            WHERE b.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll();
        
        foreach ($bookings as $booking) {
            if ($booking['status'] == 'active' || $booking['status'] == 'pending') {
                $active_bookings[] = $booking;
            } elseif ($booking['status'] == 'completed' || $booking['status'] == 'extended' || $booking['status'] == 'cancelled') {
                $completed_bookings[] = $booking;
            }
        }
    } catch (Exception $e2) {
        $error = "Error loading bookings: " . $e2->getMessage();
    }
}

// ==============================================
// PROSES EXTEND BOOKING
// ==============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['extend_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    $extend_months = (int)$_POST['extend_months'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if ($booking && ($booking['status'] == 'active' || $booking['status'] == 'pending')) {
            $new_check_out = date('Y-m-d', strtotime($booking['check_out'] . " +$extend_months months"));
            $price_per_month = $booking['total_price'] / $booking['duration_months'];
            $new_total = $booking['total_price'] + ($price_per_month * $extend_months);
            
            $stmt = $pdo->prepare("
                UPDATE bookings SET 
                    check_out = ?,
                    duration_months = duration_months + ?,
                    total_price = ?,
                    status = 'extended'
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$new_check_out, $extend_months, $new_total, $booking_id, $user_id]);
            
            // Insert history (jika tabel ada)
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO booking_histories (booking_id, action, extended_months, new_check_out)
                    VALUES (?, 'extended', ?, ?)
                ");
                $stmt->execute([$booking_id, $extend_months, $new_check_out]);
            } catch (Exception $e) {
                // History table mungkin belum ada, abaikan
            }
            
            $success = "Booking extended successfully!";
            header('Location: my_bookings.php');
            exit;
        } else {
            $error = "Booking not found or not active!";
        }
    } catch (Exception $e) {
        $error = "Extend failed: " . $e->getMessage();
    }
}

// ==============================================
// INCLUDE HEADER
// ==============================================
require_once dirname(__FILE__) . '/../includes/header.php';
?>

<!-- ========================================== -->
<!-- KONTEN HTML -->
<!-- ========================================== -->
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">📅 My Bookings</h1>
    
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> 
            <?php echo htmlspecialchars($error); ?>
            <?php if (strpos($error, 'created_at') !== false): ?>
                <br><br>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-3 rounded-lg text-sm">
                    <strong>🔧 Solution:</strong> Run this SQL in phpMyAdmin:
                    <pre class="bg-gray-100 p-2 rounded mt-2 text-xs overflow-x-auto">ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;</pre>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Active Bookings -->
    <?php if (!empty($active_bookings)): ?>
        <h2 class="text-xl font-semibold text-green-600 mb-4">🟢 Active Bookings</h2>
        <div class="space-y-4 mb-8">
            <?php foreach ($active_bookings as $booking): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex flex-wrap gap-4 items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($booking['property_name']); ?></h3>
                            <p class="text-gray-500 text-sm">
                                <i class="fas fa-map-marker-alt mr-1"></i> 
                                <?php echo htmlspecialchars($booking['property_location']); ?>
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-2">
                                <span>
                                    <i class="fas fa-calendar-alt mr-1"></i> 
                                    <?php echo date('d M Y', strtotime($booking['check_in'])); ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar-check mr-1"></i> 
                                    <?php echo date('d M Y', strtotime($booking['check_out'])); ?>
                                </span>
                                <span>
                                    <i class="fas fa-clock mr-1"></i> 
                                    <?php echo $booking['duration_months']; ?> months
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-user mr-1"></i> 
                                <?php echo htmlspecialchars($booking['full_name']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo $booking['status'] == 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo $booking['status'] == 'active' ? '✅ Active' : '⏳ Pending'; ?>
                            </span>
                            <p class="font-bold text-purple-600 mt-2">
                                Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
                            </p>
                            
                            <?php if ($booking['status'] == 'active'): ?>
                                <div class="mt-2">
                                    <form method="POST" class="inline-flex items-center gap-2">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="extend_months" class="text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:border-purple-500">
                                            <option value="1">+1 month</option>
                                            <option value="2">+2 months</option>
                                            <option value="3">+3 months</option>
                                            <option value="6">+6 months</option>
                                        </select>
                                        <button type="submit" name="extend_booking" class="bg-purple-600 text-white text-sm px-4 py-1 rounded-lg hover:bg-purple-700 transition">
                                            <i class="fas fa-plus mr-1"></i> Extend
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Completed / Extended Bookings -->
    <?php if (!empty($completed_bookings)): ?>
        <h2 class="text-xl font-semibold text-gray-600 mb-4">📂 Completed / Extended</h2>
        <div class="space-y-4">
            <?php foreach ($completed_bookings as $booking): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition opacity-70">
                    <div class="flex flex-wrap gap-4 items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($booking['property_name']); ?></h3>
                            <p class="text-gray-500 text-sm">
                                <i class="fas fa-map-marker-alt mr-1"></i> 
                                <?php echo htmlspecialchars($booking['property_location']); ?>
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-2">
                                <span>
                                    <i class="fas fa-calendar-alt mr-1"></i> 
                                    <?php echo date('d M Y', strtotime($booking['check_in'])); ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar-check mr-1"></i> 
                                    <?php echo date('d M Y', strtotime($booking['check_out'])); ?>
                                </span>
                                <span>
                                    <i class="fas fa-clock mr-1"></i> 
                                    <?php echo $booking['duration_months']; ?> months
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo $booking['status'] == 'extended' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'; ?>">
                                <?php if ($booking['status'] == 'extended'): ?>
                                    🔄 Extended
                                <?php elseif ($booking['status'] == 'cancelled'): ?>
                                    ❌ Cancelled
                                <?php else: ?>
                                    ✅ Completed
                                <?php endif; ?>
                            </span>
                            <p class="font-bold text-gray-500 mt-2">
                                Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Empty State -->
    <?php if (empty($bookings) && empty($error)): ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center text-gray-500">
            <i class="fas fa-calendar-plus text-6xl text-purple-300 mb-4 block"></i>
            <p class="text-lg font-medium">No bookings yet</p>
            <p class="text-sm">Start exploring properties and book your stay!</p>
            <a href="/staynest/properties.php" class="inline-block mt-4 gradient-bg text-white px-6 py-2 rounded-full hover:shadow-lg transition">
                Browse Properties →
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .gradient-bg {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
</style>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>