<?php
// bookings/my_bookings.php - Halaman My Bookings
$page_title = "My Bookings - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_bookings.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$bookings = [];
$active_bookings = [];
$completed_bookings = [];
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name, p.location as property_location
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.user_id = ?
        ORDER BY b.id DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();

    foreach ($bookings as $booking) {
        if ($booking['status'] == 'active' || $booking['status'] == 'pending') {
            $active_bookings[] = $booking;
        } else {
            $completed_bookings[] = $booking;
        }
    }
} catch (Exception $e) {
    $error = "Error loading bookings: " . $e->getMessage();
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">📅 My Bookings</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($active_bookings)): ?>
        <h2 class="text-xl font-semibold text-green-600 mb-4">🟢 Active Bookings</h2>
        <?php foreach ($active_bookings as $b): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition mb-4">
                <div class="flex flex-wrap gap-4 items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($b['property_name']); ?></h3>
                        <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($b['property_location']); ?></p>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-2">
                            <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo date('d M Y', strtotime($b['check_in'])); ?></span>
                            <span><i class="fas fa-calendar-check mr-1"></i> <?php echo date('d M Y', strtotime($b['check_out'])); ?></span>
                            <span><i class="fas fa-clock mr-1"></i> <?php echo $b['duration_months']; ?> months</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1"><i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($b['full_name']); ?></p>
                        <?php if (!empty($b['notes'])): ?>
                            <p class="text-sm text-gray-400 mt-1"><i class="fas fa-sticky-note mr-1"></i> <?php echo htmlspecialchars($b['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo $b['status'] == 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                            <?php echo $b['status'] == 'active' ? '✅ Active' : '⏳ Pending'; ?>
                        </span>
                        <p class="font-bold text-purple-600 mt-2">Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?></p>
                        <?php if ($b['status'] == 'active'): ?>
                            <div class="mt-2">
                                <a href="book_now.php?extend=1&booking_id=<?php echo $b['id']; ?>" class="bg-purple-600 text-white text-sm px-4 py-1 rounded-lg hover:bg-purple-700 transition inline-block">
                                    <i class="fas fa-plus mr-1"></i> Extend
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($completed_bookings)): ?>
        <h2 class="text-xl font-semibold text-gray-600 mb-4">📂 Completed / Extended</h2>
        <?php foreach ($completed_bookings as $b): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition mb-4 opacity-70">
                <div class="flex flex-wrap gap-4 items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($b['property_name']); ?></h3>
                        <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($b['property_location']); ?></p>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-2">
                            <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo date('d M Y', strtotime($b['check_in'])); ?></span>
                            <span><i class="fas fa-calendar-check mr-1"></i> <?php echo date('d M Y', strtotime($b['check_out'])); ?></span>
                            <span><i class="fas fa-clock mr-1"></i> <?php echo $b['duration_months']; ?> months</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">
                            <?php echo $b['status'] == 'extended' ? '🔄 Extended' : '✅ Completed'; ?>
                        </span>
                        <p class="font-bold text-gray-500 mt-2">Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center text-gray-500">
            <i class="fas fa-calendar-plus text-6xl text-purple-300 mb-4 block"></i>
            <p class="text-lg font-medium">No bookings yet</p>
            <a href="/staynest/properties.php" class="inline-block mt-4 gradient-bg text-white px-6 py-2 rounded-full hover:shadow-lg transition">Browse Properties →</a>
        </div>
    <?php endif; ?>
</div>

<style>
.gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
</style>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; 