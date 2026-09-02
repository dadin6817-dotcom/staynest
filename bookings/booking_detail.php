<?php
// bookings/booking_detail.php - Detail Booking
$page_title = "Booking Detail - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=booking_detail.php');
    exit;
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = null;
$success = '';

try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
} catch (Exception $e) {}

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

// Update note
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_note'])) {
    $new_note = trim($_POST['notes'] ?? '');
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET notes = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_note, $booking_id, $_SESSION['user_id']]);
        $booking['notes'] = $new_note;
        $success = "📝 Note updated successfully!";
    } catch (Exception $e) {}
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">📄 Booking Detail</h1>

    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($booking['property_name']); ?></h2>
                <p class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($booking['property_location']); ?></p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold <?php echo $booking['status'] == 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
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
        </div>

        <!-- Update Note -->
        <div class="border-t border-gray-100 pt-4 mt-4">
            <h3 class="font-semibold text-gray-700 mb-2">📝 Notes / Catatan</h3>
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-start">
                <textarea name="notes" rows="2" class="flex-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition"><?php echo htmlspecialchars($booking['notes'] ?? ''); ?></textarea>
                <button type="submit" name="update_note" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition flex-shrink-0 w-full md:w-auto">
                    <i class="fas fa-save mr-1"></i> Update Note
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2">* Update catatan kapan saja sesuai kebutuhan</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-4">
            <a href="my_bookings.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <?php if ($booking['status'] == 'active'): ?>
                <a href="book_now.php?extend=1&booking_id=<?php echo $booking['id']; ?>" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-plus mr-1"></i> Extend Booking
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>