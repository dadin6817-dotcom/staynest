<?php
// admin/manage_bookings.php - Manage All Bookings with Music
$page_title = "Manage Bookings - StayNest Admin";

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, p.id as property_id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if($booking) {
            if($new_status == 'cancelled' && $booking['status'] != 'cancelled') {
                $pdo->prepare("UPDATE properties SET available_rooms = available_rooms + 1, occupied_rooms = occupied_rooms - 1 WHERE id = ?")
                    ->execute([$booking['property_id']]);
            }
            
            if($new_status == 'confirmed' && $booking['status'] == 'cancelled') {
                $pdo->prepare("UPDATE properties SET available_rooms = available_rooms - 1, occupied_rooms = occupied_rooms + 1 WHERE id = ?")
                    ->execute([$booking['property_id']]);
            }
            
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $booking_id]);
            
            $success_message = "Booking #$booking_id status updated to " . ucfirst($new_status);
        }
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle delete booking
if(isset($_GET['delete'])) {
    $booking_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, p.id as property_id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if($booking && $booking['status'] == 'confirmed') {
            $pdo->prepare("UPDATE properties SET available_rooms = available_rooms + 1, occupied_rooms = occupied_rooms - 1 WHERE id = ?")
                ->execute([$booking['property_id']]);
        }
        
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $success_message = "Booking #$booking_id deleted successfully!";
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "
    SELECT b.*, p.name as property_name, p.location, p.price_per_month 
    FROM bookings b 
    JOIN properties p ON b.property_id = p.id 
    WHERE 1=1
";
$params = [];

if($status_filter != 'all') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

if($search) {
    $sql .= " AND (b.customer_name LIKE ? OR b.customer_email LIKE ? OR p.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY b.booked_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - StayNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Music Player untuk Admin -->
    <script src="/staynest/assets/js/music-player.js"></script>
    
    <style>
        /* RESET CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
        }
        
        .nav-item {
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Status Badge Styles */
        .status-badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-confirmed {
            background: rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        
        .status-pending {
            background: rgba(234, 179, 8, 0.2);
            color: #854d0e;
        }
        
        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #b91c1c;
        }
        
        .status-pending_payment {
            background: rgba(245, 158, 11, 0.2);
            color: #d97706;
        }
        
        /* Filter Input Styles - FIXED (no CSS errors) */
        .filter-input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            width: 100%;
        }
        
        .filter-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            width: 100%;
            background-color: white;
        }
        
        .filter-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #667eea;
        }
        
        /* Table Styles */
        .table-row:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <div class="sidebar w-64 text-white flex flex-col flex-shrink-0">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <span class="text-xl font-bold">StayNest Admin</span>
            </div>
            
            <nav class="space-y-2">
                <a href="index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_bookings.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Manage Bookings</span>
                </a>
                <a href="add_property.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-plus-circle w-5"></i>
                    <span>Add Property</span>
                </a>
                <a href="../index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition mt-8">
                    <i class="fas fa-globe w-5"></i>
                    <span>View Website</span>
                </a>
            </nav>
        </div>
        
        <div class="mt-auto p-6 border-t border-white/20">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-semibold"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrator'; ?></p>
                    <p class="text-xs text-white/70">Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center gap-2 text-white/70 hover:text-white transition text-sm">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-8">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manage Bookings</h1>
                    <p class="text-gray-500 text-sm mt-1">View and manage all customer bookings</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <i class="fas fa-headphones text-purple-500"></i>
                    <span>Background music is playing</span>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if($success_message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if($error_message): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Name, email, or property..." class="filter-input">
                    </div>
                    <div class="w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="filter-select">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="pending_payment" <?php echo $status_filter == 'pending_payment' ? 'selected' : ''; ?>>Pending Payment</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="manage_bookings.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Bookings Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">ID</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Property</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Customer</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Check-in</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Duration</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($bookings) > 0): ?>
                                <?php foreach($bookings as $booking): ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-sm font-mono">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($booking['property_name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['location']); ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['customer_email']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['customer_phone']); ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm"><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td class="py-3 px-4 text-sm"><?php echo $booking['duration_months']; ?> month(s)</td>
                                    <td class="py-3 px-4 text-sm font-semibold text-purple-600">
                                        Rp <?php echo number_format($booking['price_per_month'] * $booking['duration_months'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-xs px-2 py-1 rounded-full font-semibold <?php 
                                                        echo $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : 
                                                            ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 
                                                            ($booking['status'] == 'pending_payment' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')); 
                                                    ?>">
                                                <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="pending_payment" <?php echo $booking['status'] == 'pending_payment' ? 'selected' : ''; ?>>Pending Payment</option>
                                                <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="?delete=<?php echo $booking['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this booking?')"
                                           class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-12 text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                        <p>No bookings found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>