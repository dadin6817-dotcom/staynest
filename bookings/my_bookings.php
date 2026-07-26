<?php
// bookings/my_bookings.php - Halaman My Bookings & Booking (Payment Logic Baru)
$page_title = "My Bookings & Booking - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// ==============================================
// HANDLE BOOKING DARI PARAMETER URL
// ==============================================
$book_property_id = isset($_GET['book']) ? (int)$_GET['book'] : 0;
$book_unit_number = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$booking_property = null;
$selected_unit = $book_unit_number;

if ($book_property_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$book_property_id]);
        $booking_property = $stmt->fetch();
        
        if ($book_property_id == 3 && $book_unit_number > 0) {
            $stmt2 = $pdo->prepare("SELECT price_per_month FROM unit_prices WHERE property_id = ? AND unit_number = ?");
            $stmt2->execute([$book_property_id, $book_unit_number]);
            $unit_price = $stmt2->fetch();
            if ($unit_price) {
                $booking_property['price_per_month'] = $unit_price['price_per_month'];
            }
        }
    } catch(Exception $e) {
        $booking_property = null;
    }
}

// Get customer email
$customer_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : (isset($_GET['email']) ? $_GET['email'] : '');
$bookings = array();
$search_performed = false;
$success_message = '';
$error_message = '';
$booking_success = false;
$new_booking_id = null;
$show_payment_section = false;
$payment_just_uploaded = false;

// Get properties for booking form
$properties = array();
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, location, price_per_month, available_rooms, is_vip FROM properties WHERE available_rooms > 0 ORDER BY is_vip DESC, name ASC");
        $properties = $stmt->fetchAll();
    } catch(Exception $e) {
        $properties = array();
    }
}

// Auto-cancel expired bookings (hanya untuk yang belum upload payment)
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE bookings b 
            SET b.status = 'cancelled', b.payment_status = 'expired'
            WHERE b.payment_status = 'pending' 
            AND b.payment_deadline < NOW()
            AND b.status != 'cancelled'
        ");
        $stmt->execute();
        
        // Update ketersediaan kamar
        $stmt2 = $pdo->prepare("
            UPDATE properties p
            JOIN bookings b ON b.property_id = p.id
            SET p.available_rooms = p.available_rooms + 1,
                p.occupied_rooms = p.occupied_rooms - 1
            WHERE b.payment_status = 'expired'
            AND b.status = 'cancelled'
        ");
        $stmt2->execute();
    } catch(Exception $e) {}
}

// Handle NEW BOOKING dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_booking_form'])) {
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email_input = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $check_in_date = $_POST['check_in_date'] ?? '';
    $duration_months = (int)($_POST['duration_months'] ?? 1);
    $unit_number = (int)($_POST['unit_number'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = array();
    if (empty($customer_name)) $errors[] = "Full name is required";
    if (empty($customer_email_input)) $errors[] = "Email address is required";
    if (!filter_var($customer_email_input, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($customer_phone)) $errors[] = "Phone number is required";
    if (empty($check_in_date)) $errors[] = "Check-in date is required";
    if ($property_id <= 0) $errors[] = "Please select a property";
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND available_rooms > 0");
            $stmt->execute([$property_id]);
            $property = $stmt->fetch();
            
            if (!$property) {
                $error_message = "Property not available or fully booked.";
            } else {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (property_id, customer_name, customer_email, customer_phone, check_in_date, duration_months, status, unit_number, payment_status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending_payment', ?, 'pending', ?)
                ");
                $stmt->execute([$property_id, $customer_name, $customer_email_input, $customer_phone, $check_in_date, $duration_months, $unit_number, $notes]);
                $new_booking_id = $pdo->lastInsertId();
                
                $pdo->prepare("UPDATE properties SET available_rooms = available_rooms - 1, occupied_rooms = occupied_rooms + 1 WHERE id = ?")
                    ->execute([$property_id]);
                
                $pdo->commit();
                
                $booking_success = true;
                $success_message = "Booking created! Please complete payment below.";
                $customer_email = $customer_email_input;
                $_SESSION['customer_email'] = $customer_email;
                $show_payment_section = true;
                $selected_property = $property;
                $selected_unit = $unit_number;
                $selected_duration = $duration_months;
                
                // Refresh bookings
                $stmt = $pdo->prepare("
                    SELECT b.*, p.name as property_name, p.location, p.price_per_month, p.image_url
                    FROM bookings b 
                    JOIN properties p ON b.property_id = p.id 
                    WHERE b.customer_email = ? 
                    ORDER BY b.booked_at DESC
                ");
                $stmt->execute([$customer_email]);
                $bookings = $stmt->fetchAll();
            }
        } catch(Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error_message = "Booking failed: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// ==============================================
// HANDLE PAYMENT UPLOAD - LANGSUNG CONFIRMED
// ==============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $payment_method = isset($_POST['payment_method_type']) ? $_POST['payment_method_type'] : '';
    $payment_channel = isset($_POST['payment_channel']) ? $_POST['payment_channel'] : '';
    $customer_email_post = isset($_POST['customer_email']) ? $_POST['customer_email'] : '';
    
    // Create uploads directory
    $target_dir = __DIR__ . '/../uploads/payments/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $proof_image = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $proof_image = 'uploads/payments/payment_' . $booking_id . '_' . time() . '.' . $file_extension;
        $target_file = __DIR__ . '/../' . $proof_image;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file);
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, p.price_per_month 
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking_data = $stmt->fetch();
        $amount = $booking_data['price_per_month'] * $booking_data['duration_months'];
        
        // Insert payment record dengan status CONFIRMED langsung
        $stmt2 = $pdo->prepare("
            INSERT INTO payments (booking_id, payment_method, payment_channel, amount, proof_image, status, payment_deadline, payment_date)
            VALUES (?, ?, ?, ?, ?, 'confirmed', NOW(), NOW())
        ");
        $stmt2->execute([$booking_id, $payment_method, $payment_channel, $amount, $proof_image]);
        
        // Update booking status menjadi CONFIRMED dan PAID langsung
        $stmt3 = $pdo->prepare("
            UPDATE bookings 
            SET payment_status = 'paid', 
                status = 'confirmed',
                payment_deadline = NULL
            WHERE id = ?
        ");
        $stmt3->execute([$booking_id]);
        
        $payment_just_uploaded = true;
        $success_message = "Payment confirmed! Your booking is now confirmed. 🎉";
        $show_payment_section = false;
        
        // Refresh bookings
        $stmt = $pdo->prepare("
            SELECT b.*, p.name as property_name, p.location, p.price_per_month, p.image_url,
                   pay.id as payment_id, pay.payment_method, pay.payment_channel, pay.amount as payment_amount, pay.status as payment_status
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            LEFT JOIN payments pay ON b.id = pay.booking_id
            WHERE b.customer_email = ? 
            ORDER BY b.booked_at DESC
        ");
        $stmt->execute([$customer_email_post]);
        $bookings = $stmt->fetchAll();
        
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $customer_email_post = isset($_POST['customer_email']) ? $_POST['customer_email'] : '';
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, p.id as property_id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ? AND b.customer_email = ?");
        $stmt->execute([$booking_id, $customer_email_post]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $update = $pdo->prepare("UPDATE bookings SET status = 'cancelled', payment_status = 'cancelled' WHERE id = ?");
            $update->execute([$booking_id]);
            
            $pdo->prepare("UPDATE properties SET available_rooms = available_rooms + 1, occupied_rooms = occupied_rooms - 1 WHERE id = ?")
                ->execute([$booking['property_id']]);
            
            $success_message = "Booking cancelled successfully!";
            $customer_email = $customer_email_post;
            $_SESSION['customer_email'] = $customer_email;
            
            $stmt = $pdo->prepare("
                SELECT b.*, p.name as property_name, p.location, p.price_per_month, p.image_url
                FROM bookings b 
                JOIN properties p ON b.property_id = p.id 
                WHERE b.customer_email = ? 
                ORDER BY b.booked_at DESC
            ");
            $stmt->execute([$customer_email]);
            $bookings = $stmt->fetchAll();
        } else {
            $error_message = "Unable to cancel booking.";
        }
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['customer_email']);
    echo '<meta http-equiv="refresh" content="0;url=my_bookings.php">';
    exit;
}

// Fetch bookings if email provided
if (!empty($customer_email)) {
    $search_performed = true;
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, p.name as property_name, p.location, p.price_per_month, p.image_url,
                   pay.id as payment_id, pay.payment_method, pay.payment_channel, pay.amount as payment_amount, pay.status as payment_status, pay.payment_deadline
            FROM bookings b 
            JOIN properties p ON b.property_id = p.id 
            LEFT JOIN payments pay ON b.id = pay.booking_id
            WHERE b.customer_email = ? 
            ORDER BY b.booked_at DESC
        ");
        $stmt->execute([$customer_email]);
        $bookings = $stmt->fetchAll();
        $_SESSION['customer_email'] = $customer_email;
    } catch(Exception $e) {
        $bookings = array();
    }
}

// Calculate total for selected booking
$selected_amount = 0;
if ($booking_property && $booking_success) {
    $selected_amount = $booking_property['price_per_month'];
} elseif ($booking_property) {
    $selected_amount = $booking_property['price_per_month'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings & Booking - StayNest ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .booking-card { transition: all 0.3s ease; background: white; border-radius: 1rem; overflow: hidden; }
        .booking-card:hover { transform: translateY(-5px); box-shadow: 0 20px 30px -15px rgba(0,0,0,0.15); }
        .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .status-confirmed { background: rgba(34,197,94,0.2); color: #15803d; }
        .status-pending { background: rgba(234,179,8,0.2); color: #854d0e; }
        .status-cancelled { background: rgba(239,68,68,0.2); color: #b91c1c; }
        .status-pending_payment { background: rgba(245,158,11,0.2); color: #d97706; }
        .payment-status-paid { background: rgba(34,197,94,0.2); color: #15803d; }
        .payment-status-unpaid { background: rgba(239,68,68,0.2); color: #b91c1c; }
        .payment-status-pending { background: rgba(234,179,8,0.2); color: #854d0e; }
        .timer { font-family: monospace; font-size: 1rem; font-weight: bold; color: #ef4444; }
        .payment-option { cursor: pointer; transition: all 0.3s ease; border: 2px solid #e2e8f0; border-radius: 12px; }
        .payment-option.selected { border-color: #667eea; background: #f3e8ff; }
        .payment-option:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input:focus, select:focus, textarea:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        @media (max-width: 768px) { .booking-card { margin-bottom: 1rem; } }
    </style>
</head>
<body>

<nav class="fixed top-0 w-full z-50 bg-white/95 backdrop-blur shadow-sm py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="/staynest/index.php" class="flex items-center gap-2">
            <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center"><i class="fas fa-home text-white text-sm"></i></div>
            <span class="text-xl font-bold gradient-text">StayNest</span>
        </a>
        <div class="hidden md:flex gap-6">
            <a href="/staynest/index.php" class="text-gray-600 hover:text-purple-600">Home</a>
            <a href="/staynest/properties.php" class="text-gray-600 hover:text-purple-600">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="text-gray-600 hover:text-purple-600 active font-semibold text-purple-600">My Bookings</a>
        </div>
        <button id="mobileMenuBtn" class="md:hidden text-2xl"><i class="fas fa-bars"></i></button>
    </div>
</nav>
<div style="height: 80px;"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-block p-3 bg-gradient-to-r from-purple-100 to-pink-100 rounded-2xl mb-4">
            <i class="fas fa-calendar-alt text-4xl text-purple-600"></i>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4 gradient-text">My Bookings & Booking 📅</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">Book a property and complete your payment</p>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if($success_message): ?>
        <div class="max-w-4xl mx-auto mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    <?php if($error_message): ?>
        <div class="max-w-4xl mx-auto mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <!-- MAIN BOOKING FORM + PAYMENT SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- LEFT COLUMN: BOOKING FORM -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center"><i class="fas fa-pen-alt text-white"></i></div>
                <h2 class="text-xl font-bold">Book a Property</h2>
            </div>
            <p class="text-gray-500 text-sm mb-4">Fill in the form below to book a property</p>
            
            <form method="POST" id="bookingForm">
                <input type="hidden" name="submit_booking_form" value="1">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Property *</label>
                    <select name="property_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" id="propertySelect">
                        <option value="">-- Select Property --</option>
                        <?php foreach($properties as $prop): ?>
                            <option value="<?php echo $prop['id']; ?>" <?php echo ($booking_property && $booking_property['id'] == $prop['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prop['name']); ?> - <?php echo htmlspecialchars($prop['location']); ?> 
                                (Rp <?php echo number_format($prop['price_per_month'], 0, ',', '.'); ?>/month)
                                <?php echo $prop['is_vip'] ? '⭐ VIP' : ''; ?>
                                - <?php echo $prop['available_rooms']; ?> left
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit Number</label>
                    <input type="number" name="unit_number" id="unitNumber" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="Enter unit number" value="<?php echo $selected_unit > 0 ? $selected_unit : ''; ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="customer_name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="Enter your full name">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="customer_email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="you@example.com">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input type="tel" name="customer_phone" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="+62 812 3456 7890">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                    <input type="date" name="check_in_date" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" id="checkinDate">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (months) *</label>
                    <select name="duration_months" class="w-full px-4 py-2 border rounded-lg" id="durationSelect">
                        <option value="1">1 month</option>
                        <option value="2">2 months</option>
                        <option value="3">3 months</option>
                        <option value="6">6 months</option>
                        <option value="12">12 months</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="Any special requests?"></textarea>
                </div>
                
                <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition">
                    <i class="fas fa-calendar-check mr-2"></i> Create Booking
                </button>
            </form>
        </div>
        
        <!-- RIGHT COLUMN: VIEW EXISTING BOOKINGS -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center"><i class="fas fa-list-alt text-white"></i></div>
                <h2 class="text-xl font-bold">My Bookings</h2>
            </div>
            
            <form method="GET" action="my_bookings.php" class="mb-6">
                <div class="flex gap-3">
                    <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($customer_email); ?>" class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500">
                    <button type="submit" class="gradient-bg text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                <?php if($search_performed): ?>
                    <div class="mt-2 text-right"><a href="?logout=1" class="text-xs text-red-500 hover:underline">Clear & Search Different Email</a></div>
                <?php endif; ?>
            </form>
            
            <?php if($search_performed): ?>
                <?php if(count($bookings) > 0): ?>
                    <?php 
                    $active_count = 0; $total_spent = 0;
                    foreach($bookings as $b) {
                        if($b['status'] == 'confirmed' && $b['payment_status'] == 'paid') {
                            $active_count++;
                            $total_spent += $b['price_per_month'] * $b['duration_months'];
                        }
                    }
                    ?>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="bg-blue-50 rounded-lg p-2 text-center"><p class="text-xs text-gray-500">Total</p><p class="text-lg font-bold text-blue-600"><?php echo count($bookings); ?></p></div>
                        <div class="bg-green-50 rounded-lg p-2 text-center"><p class="text-xs text-gray-500">Active</p><p class="text-lg font-bold text-green-600"><?php echo $active_count; ?></p></div>
                        <div class="bg-purple-50 rounded-lg p-2 text-center"><p class="text-xs text-gray-500">Spent</p><p class="text-sm font-bold text-purple-600">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></p></div>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        <?php foreach($bookings as $booking): ?>
                        <div class="border rounded-lg p-3 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div><h3 class="font-bold text-sm"><?php echo htmlspecialchars($booking['property_name']); ?></h3><p class="text-xs text-gray-500">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p></div>
                                <div class="text-right">
                                    <span class="status-badge status-<?php echo $booking['status']; ?> text-xs"><?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?></span>
                                    <?php if($booking['payment_status']): ?>
                                        <span class="status-badge payment-status-<?php echo $booking['payment_status']; ?> text-xs ml-1"><?php echo ucfirst($booking['payment_status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                <p><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></p>
                                <p><i class="fas fa-tag"></i> Rp <?php echo number_format($booking['price_per_month'], 0, ',', '.'); ?>/month</p>
                                <?php if($booking['unit_number'] > 0): ?><p><i class="fas fa-door-open"></i> Unit <?php echo str_pad($booking['unit_number'], 2, '0', STR_PAD_LEFT); ?></p><?php endif; ?>
                                <?php if($booking['payment_deadline'] && $booking['payment_status'] == 'pending'): ?>
                                    <p class="text-red-500 text-xs mt-1"><i class="fas fa-hourglass-half"></i> Deadline: <span class="timer" data-deadline="<?php echo $booking['payment_deadline']; ?>">--:--:--</span></p>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <a href="/staynest/detail.php?id=<?php echo $booking['property_id']; ?>" class="text-purple-600 text-xs hover:underline">View Property</a>
                                <?php if($booking['status'] == 'confirmed' && $booking['payment_status'] == 'paid'): ?>
                                    <span class="text-green-600 text-xs"><i class="fas fa-check-circle"></i> Paid</span>
                                <?php endif; ?>
                                <?php if($booking['status'] != 'cancelled' && $booking['status'] != 'confirmed' && $booking['payment_status'] != 'paid'): ?>
                                    <button onclick="openCancelModal(<?php echo $booking['id']; ?>, '<?php echo $customer_email; ?>')" class="text-red-600 text-xs hover:underline">Cancel</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8"><i class="fas fa-inbox text-4xl text-gray-300 mb-2 block"></i><p class="text-gray-500">No bookings found</p></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-8"><i class="fas fa-search text-4xl text-gray-300 mb-2 block"></i><p class="text-gray-500">Enter your email to view bookings</p></div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ============================================ -->
    <!-- PAYMENT SECTION - Muncul setelah booking -->
    <!-- ============================================ -->
    <?php if($booking_success && $new_booking_id > 0 && !$payment_just_uploaded): ?>
    <div class="mt-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
                <h2 class="text-xl font-bold text-green-600">Booking Created! 🎉</h2>
                <p class="text-gray-500 text-sm">Booking ID: #<?php echo str_pad($new_booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                <p class="text-gray-500 text-sm">Please complete payment below to confirm your booking</p>
            </div>
            
            <div class="border-t pt-4">
                <h3 class="font-bold text-lg mb-4 gradient-text">Complete Your Payment</h3>
                
                <!-- Informasi Deadline 1x24 jam (hanya untuk yang BELUM upload) -->
                <div class="bg-yellow-50 p-3 rounded-lg mb-4">
                    <p class="text-sm text-yellow-800 flex items-center gap-2">
                        <i class="fas fa-hourglass-half text-yellow-600"></i>
                        <strong>⚠️ Payment Required Within 1x24 Hours!</strong>
                    </p>
                    <p class="text-xs text-yellow-700 mt-1">
                        Your booking will be automatically cancelled if payment is not completed within <strong>1x24 hours</strong>.
                        Please upload your payment proof immediately.
                    </p>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="paymentForm">
                    <input type="hidden" name="booking_id" value="<?php echo $new_booking_id; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($customer_email); ?>">
                    <input type="hidden" name="submit_payment" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="payment-option p-3 rounded-xl text-center cursor-pointer" data-type="bank" id="bankOption">
                                <i class="fas fa-university text-xl text-purple-600 mb-1 block"></i>
                                <span class="text-sm">Bank Transfer</span>
                            </div>
                            <div class="payment-option p-3 rounded-xl text-center cursor-pointer" data-type="ewallet" id="ewalletOption">
                                <i class="fas fa-mobile-alt text-xl text-purple-600 mb-1 block"></i>
                                <span class="text-sm">E-Wallet</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Options -->
                    <div id="bankOptions" class="mb-4" style="display: none;">
                        <label class="block text-sm font-medium mb-2">Select Bank</label>
                        <div class="grid grid-cols-2 gap-2">
                            <?php 
                            $banks = ['BRI', 'BNI', 'BCA', 'MANDIRI', 'BSI'];
                            foreach($banks as $bank): ?>
                            <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-purple-50">
                                <input type="radio" name="payment_channel" value="<?php echo $bank; ?>" class="w-4 h-4 text-purple-600">
                                <span><?php echo $bank; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- E-Wallet Options -->
                    <div id="ewalletOptions" class="mb-4" style="display: none;">
                        <label class="block text-sm font-medium mb-2">Select E-Wallet</label>
                        <div class="grid grid-cols-3 gap-2">
                            <?php 
                            $wallets = ['GOPAY', 'DANA', 'OVO'];
                            foreach($wallets as $wallet): ?>
                            <label class="flex flex-col items-center gap-1 p-2 border rounded-lg cursor-pointer hover:bg-purple-50">
                                <input type="radio" name="payment_channel" value="<?php echo $wallet; ?>" class="w-4 h-4 text-purple-600">
                                <span class="text-sm"><?php echo $wallet; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <input type="hidden" name="payment_method_type" id="paymentMethodType" value="">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Amount to Pay</label>
                        <div class="bg-purple-50 p-3 rounded-lg text-center">
                            <span class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($selected_amount, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Upload Payment Proof</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-3 text-center cursor-pointer hover:border-purple-500 transition" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-1 block"></i>
                            <p class="text-xs text-gray-500">Click to upload (JPG, PNG, JPEG)</p>
                            <input type="file" name="payment_proof" id="paymentProof" class="hidden" accept="image/*" required>
                        </div>
                        <div id="filePreview" class="mt-2 hidden">
                            <div class="flex items-center gap-2 p-2 bg-green-50 rounded-lg text-sm">
                                <i class="fas fa-file-image text-green-600"></i>
                                <span id="fileName"></span>
                                <button type="button" onclick="clearFile()" class="ml-auto text-red-500"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 p-3 rounded-lg mb-4">
                        <p class="text-xs text-red-700 flex items-center gap-2">
                            <i class="fas fa-clock"></i>
                            <strong>❗ Payment Deadline:</strong> You have <strong id="deadlineCountdown">24 hours</strong> to complete payment!
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            ⚠️ After <strong>1x24 hours</strong>, your booking will be automatically cancelled if payment is not received.
                        </p>
                    </div>
                    
                    <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition">
                        <i class="fas fa-credit-card mr-2"></i> Submit Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold">Cancel Booking?</h3>
                <p class="text-gray-500 text-sm">Are you sure you want to cancel this booking?</p>
            </div>
            <form method="POST">
                <input type="hidden" name="booking_id" id="cancel_booking_id">
                <input type="hidden" name="customer_email" id="cancel_customer_email">
                <input type="hidden" name="cancel_booking" value="1">
                <div class="flex gap-3">
                    <button type="button" onclick="closeCancelModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 py-2 rounded-lg">No</button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg">Yes, Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Set minimum date for check-in
    var today = new Date().toISOString().split('T')[0];
    var checkinDate = document.getElementById('checkinDate');
    if (checkinDate) checkinDate.setAttribute('min', today);
    
    // Mobile menu
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            var mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu) mobileMenu.classList.toggle('hidden');
        });
    }
    
    // ============================================
    // PAYMENT METHOD SELECTION
    // ============================================
    var bankOption = document.getElementById('bankOption');
    var ewalletOption = document.getElementById('ewalletOption');
    var bankOptionsDiv = document.getElementById('bankOptions');
    var ewalletOptionsDiv = document.getElementById('ewalletOptions');
    var paymentMethodType = document.getElementById('paymentMethodType');
    
    function selectPaymentMethod(type) {
        if (type === 'bank') {
            bankOption.classList.add('selected');
            ewalletOption.classList.remove('selected');
            bankOptionsDiv.style.display = 'block';
            ewalletOptionsDiv.style.display = 'none';
            paymentMethodType.value = 'bank';
        } else if (type === 'ewallet') {
            ewalletOption.classList.add('selected');
            bankOption.classList.remove('selected');
            ewalletOptionsDiv.style.display = 'block';
            bankOptionsDiv.style.display = 'none';
            paymentMethodType.value = 'ewallet';
        }
    }
    
    if (bankOption) {
        bankOption.addEventListener('click', function() { selectPaymentMethod('bank'); });
    }
    if (ewalletOption) {
        ewalletOption.addEventListener('click', function() { selectPaymentMethod('ewallet'); });
    }
    
    // ============================================
    // FILE UPLOAD
    // ============================================
    var uploadArea = document.getElementById('uploadArea');
    var paymentProof = document.getElementById('paymentProof');
    var filePreview = document.getElementById('filePreview');
    var fileNameSpan = document.getElementById('fileName');
    
    if (uploadArea) {
        uploadArea.addEventListener('click', function() {
            if (paymentProof) paymentProof.click();
        });
    }
    if (paymentProof) {
        paymentProof.addEventListener('change', function() {
            if (this.files.length > 0) {
                var file = this.files[0];
                if (file.size > 2 * 1024 * 1024) {
                    alert('File too large! Max 2MB');
                    this.value = '';
                    return;
                }
                if (fileNameSpan) fileNameSpan.textContent = file.name;
                if (filePreview) filePreview.classList.remove('hidden');
                if (uploadArea) uploadArea.style.borderColor = '#22c55e';
            }
        });
    }
    
    function clearFile() {
        if (paymentProof) paymentProof.value = '';
        if (filePreview) filePreview.classList.add('hidden');
        if (uploadArea) uploadArea.style.borderColor = '#d1d5db';
    }
    
    // ============================================
    // CANCEL MODAL FUNCTIONS
    // ============================================
    function openCancelModal(bookingId, email) {
        document.getElementById('cancel_booking_id').value = bookingId;
        document.getElementById('cancel_customer_email').value = email;
        document.getElementById('cancelModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    document.getElementById('cancelModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });
    
    // ============================================
    // COUNTDOWN TIMER
    // ============================================
    function updateTimers() {
        var timers = document.querySelectorAll('.timer');
        timers.forEach(function(timer) {
            var deadline = new Date(timer.getAttribute('data-deadline')).getTime();
            var now = new Date().getTime();
            var distance = deadline - now;
            if (distance < 0) {
                timer.innerHTML = 'Expired';
                location.reload();
            } else {
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                timer.innerHTML = hours + 'h ' + minutes + 'm ' + seconds + 's';
            }
        });
    }
    
    setInterval(updateTimers, 1000);
    updateTimers();
    
    // ============================================
    // AUTO-SELECT PROPERTY FROM URL PARAMETER
    // ============================================
    var urlParams = new URLSearchParams(window.location.search);
    var bookId = urlParams.get('book');
    var bookUnit = urlParams.get('unit');
    
    if (bookId) {
        var propertySelect = document.getElementById('propertySelect');
        var unitInput = document.getElementById('unitNumber');
        if (propertySelect) propertySelect.value = bookId;
        if (unitInput && bookUnit) unitInput.value = bookUnit;
    }
    
    // ============================================
    // PAYMENT FORM VALIDATION
    // ============================================
    var paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            var selectedMethod = document.querySelector('input[name="payment_method_type"]').value;
            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select payment method!');
                return;
            }
            var selectedChannel = document.querySelector('input[name="payment_channel"]:checked');
            if (!selectedChannel) {
                e.preventDefault();
                alert('Please select payment channel!');
                return;
            }
            var proofFile = document.getElementById('paymentProof');
            if (!proofFile || !proofFile.files.length) {
                e.preventDefault();
                alert('Please upload payment proof!');
                return;
            }
        });
    }
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>