<?php
// bookings/book.php - Halaman Booking Baru (Terintegrasi dengan My Booking)
$page_title = "Book Your Stay - StayNest";

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$unit_number = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$property = null;
$error_message = '';
$success_message = '';
$booking_success = false;
$booking_id = null;

// Get property details
if ($pdo && $property_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch();
        
        // Get unit price for StayNest Elora
        if ($property_id == 3 && $unit_number > 0) {
            $stmt2 = $pdo->prepare("SELECT price_per_month FROM unit_prices WHERE property_id = ? AND unit_number = ?");
            $stmt2->execute([$property_id, $unit_number]);
            $unit_price = $stmt2->fetch();
            if ($unit_price) {
                $property['price_per_month'] = $unit_price['price_per_month'];
            }
        }
    } catch(Exception $e) {
        $error_message = "Error loading property: " . $e->getMessage();
    }
}

// If property not found, redirect menggunakan meta refresh
if (!$property) {
    echo '<meta http-equiv="refresh" content="0;url=/staynest/properties.php">';
    exit;
}

// Check if rooms available
if ($property['available_rooms'] <= 0) {
    $error_message = "Sorry, this property is fully booked. Please check other properties.";
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_booking'])) {
    $customer_name = trim($_POST['name'] ?? '');
    $customer_email = trim($_POST['email'] ?? '');
    $customer_phone = trim($_POST['phone'] ?? '');
    $check_in_date = $_POST['checkin'] ?? '';
    $duration_months = (int)($_POST['duration'] ?? 1);
    $unit_number = (int)($_POST['unit_number'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    $errors = array();
    if (empty($customer_name)) $errors[] = "Full name is required";
    if (empty($customer_email)) $errors[] = "Email address is required";
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($customer_phone)) $errors[] = "Phone number is required";
    if (empty($check_in_date)) $errors[] = "Check-in date is required";
    
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (property_id, customer_name, customer_email, customer_phone, check_in_date, duration_months, status, unit_number, payment_status, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, 'unpaid', ?, NOW())
            ");
            $stmt->execute([$property_id, $customer_name, $customer_email, $customer_phone, $check_in_date, $duration_months, $unit_number, $notes]);
            $booking_id = $pdo->lastInsertId();
            
            // Update available rooms
            $pdo->prepare("UPDATE properties SET available_rooms = available_rooms - 1, occupied_rooms = occupied_rooms + 1 WHERE id = ?")
                ->execute([$property_id]);
            
            $pdo->commit();
            
            $booking_success = true;
            $success_message = "Booking successful! Your booking ID is #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
            
            // Store in session for my_bookings page
            $_SESSION['last_booking_id'] = $booking_id;
            $_SESSION['last_booking_email'] = $customer_email;
            $_SESSION['booking_success_time'] = time();
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $error_message = "Booking failed: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Calculate total price
$total_price = $property['price_per_month'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book - <?php echo htmlspecialchars($property['name']); ?> | StayNest ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .booking-card { transition: all 0.3s ease; }
        .booking-card:hover { transform: translateY(-5px); box-shadow: 0 20px 30px -15px rgba(0,0,0,0.15); }
        input:focus, select:focus, textarea:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .success-animation {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-mybooking {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
        }
        .btn-mybooking:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
    </style>
</head>
<body>

<!-- Navbar with My Booking Link -->
<nav class="fixed top-0 w-full z-50 bg-white/95 backdrop-blur shadow-sm py-4 px-6 md:px-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="/staynest/index.php" class="flex items-center gap-2">
            <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center"><i class="fas fa-home text-white text-sm"></i></div>
            <span class="text-xl font-bold gradient-text">StayNest</span>
        </a>
        <div class="hidden md:flex gap-6">
            <a href="/staynest/index.php" class="text-gray-600 hover:text-purple-600 transition">Home</a>
            <a href="/staynest/properties.php" class="text-gray-600 hover:text-purple-600 transition">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="text-purple-600 font-semibold transition">
                <i class="fas fa-bookmark"></i> My Bookings
            </a>
            <a href="/staynest/admin/login.php" class="text-gray-600 hover:text-purple-600 transition">Admin</a>
        </div>
        <button id="mobileMenuBtn" class="md:hidden text-2xl"><i class="fas fa-bars"></i></button>
    </div>
    <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-gray-100">
        <div class="flex flex-col gap-3">
            <a href="/staynest/index.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Home</a>
            <a href="/staynest/properties.php" class="px-4 py-2 hover:bg-purple-50 rounded-lg">Properties</a>
            <a href="/staynest/bookings/my_bookings.php" class="px-4 py-2 bg-purple-50 text-purple-600 rounded-lg font-semibold">
                <i class="fas fa-bookmark"></i> My Bookings
            </a>
            <a href="/staynest/admin/login.php" class="px-4 py-2 gradient-bg text-white rounded-lg text-center">Admin Panel</a>
        </div>
    </div>
</nav>
<div style="height: 80px;"></div>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    
    <?php if($booking_success): ?>
        <!-- SUCCESS PAGE - Redirect ke My Booking setelah sukses -->
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center success-animation">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-4xl text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-green-600 mb-2">Booking Successful! 🎉</h2>
            <p class="text-gray-500 mb-4"><?php echo $success_message; ?></p>
            <div class="bg-gray-50 rounded-xl p-4 mb-6 max-w-md mx-auto">
                <p class="text-sm text-gray-600">Booking ID: <strong>#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></strong></p>
                <p class="text-sm text-gray-600 mt-1">Property: <strong><?php echo htmlspecialchars($property['name']); ?></strong></p>
                <p class="text-sm text-gray-600 mt-1">Total Amount: <strong class="text-purple-600">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg mb-6 max-w-md mx-auto">
                <p class="text-sm text-yellow-700">
                    <i class="fas fa-hourglass-half mr-2"></i>
                    Please complete your payment within 1x24 hours to confirm your booking.
                </p>
            </div>
            
            <!-- Tombol langsung ke My Booking dan Booking Baru -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="my_bookings.php" class="btn-mybooking text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition inline-flex items-center justify-center gap-2">
                    <i class="fas fa-bookmark"></i> Go to My Bookings
                </a>
                <a href="confirm_booking.php?id=<?php echo $booking_id; ?>" class="gradient-bg text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition inline-flex items-center justify-center gap-2">
                    <i class="fas fa-credit-card"></i> Proceed to Payment
                </a>
                <a href="/staynest/properties.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-200 transition inline-flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> Browse More Properties
                </a>
            </div>
            
            <!-- Auto redirect ke My Booking setelah 5 detik -->
            <div class="mt-6 text-sm text-gray-500">
                <i class="fas fa-clock"></i> Redirecting to <a href="my_bookings.php" class="text-purple-600">My Bookings</a> in <span id="countdown">5</span> seconds...
            </div>
        </div>
        
        <script>
            // Auto redirect ke My Booking
            let seconds = 5;
            const countdownEl = document.getElementById('countdown');
            const interval = setInterval(() => {
                seconds--;
                if (countdownEl) countdownEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = 'my_bookings.php';
                }
            }, 1000);
        </script>
        
    <?php elseif($property['available_rooms'] <= 0): ?>
        <!-- FULLY BOOKED -->
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bed text-4xl text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-red-600 mb-2">Fully Booked! 😢</h2>
            <p class="text-gray-500 mb-6">Sorry, this property is fully booked. Please check other available properties.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/staynest/properties.php" class="gradient-bg text-white px-6 py-3 rounded-xl inline-flex items-center gap-2">
                    <i class="fas fa-search"></i> Browse Other Properties
                </a>
                <a href="my_bookings.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl inline-flex items-center gap-2">
                    <i class="fas fa-bookmark"></i> View My Bookings
                </a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- BOOKING FORM -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Property Info Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 booking-card">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>" class="rounded-xl w-full h-48 object-cover">
                    <?php if($property['is_vip']): ?>
                        <div class="absolute top-3 left-3 bg-yellow-400 text-purple-900 px-2 py-1 rounded-full text-xs font-bold">
                            <i class="fas fa-crown"></i> VIP
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($property['name']); ?></h2>
                <p class="text-gray-500 text-sm mt-1"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                
                <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Price per month:</span>
                        <span class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if($unit_number > 0): ?>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">Unit Number:</span>
                            <span class="font-semibold">Unit <?php echo str_pad($unit_number, 2, '0', STR_PAD_LEFT); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-gray-600">Available Rooms:</span>
                        <span class="font-semibold text-green-600"><?php echo $property['available_rooms']; ?> rooms left</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3 class="font-semibold mb-2">✨ Facilities</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-wifi"></i> WiFi</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-snowflake"></i> AC</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-car"></i> Parkir</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-shield-alt"></i> CCTV</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-tv"></i> Smart TV</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded"><i class="fas fa-tshirt"></i> Washing Machine</span>
                    </div>
                </div>
                
                <!-- Tombol View My Bookings -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="my_bookings.php" class="text-purple-600 hover:text-purple-800 text-sm flex items-center gap-1">
                        <i class="fas fa-bookmark"></i> View My Bookings →
                    </a>
                </div>
            </div>
            
            <!-- Booking Form Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 booking-card">
                <h2 class="text-xl font-bold mb-4">Complete Your Booking</h2>
                
                <?php if($error_message): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="unit_number" value="<?php echo $unit_number; ?>">
                    <input type="hidden" name="submit_booking" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Number *</label>
                        <input type="text" value="Unit <?php echo str_pad($unit_number, 2, '0', STR_PAD_LEFT); ?>" class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="Enter your full name">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="you@example.com">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" name="phone" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="+62 812 3456 7890">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                        <input type="date" name="checkin" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" id="checkinDate">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (months) *</label>
                        <select name="duration" id="duration" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500">
                            <option value="1">1 month - Rp <?php echo number_format($property['price_per_month'] * 1, 0, ',', '.'); ?></option>
                            <option value="2">2 months - Rp <?php echo number_format($property['price_per_month'] * 2, 0, ',', '.'); ?></option>
                            <option value="3">3 months - Rp <?php echo number_format($property['price_per_month'] * 3, 0, ',', '.'); ?></option>
                            <option value="6">6 months - Rp <?php echo number_format($property['price_per_month'] * 6, 0, ',', '.'); ?></option>
                            <option value="12">12 months - Rp <?php echo number_format($property['price_per_month'] * 12, 0, ',', '.'); ?> <span class="text-green-600">(Best Value!)</span></option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500" placeholder="Any special requests?"></textarea>
                    </div>
                    
                    <div class="bg-yellow-50 p-3 rounded-lg mb-4">
                        <p class="text-xs text-yellow-700 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            After booking, you will be redirected to <strong>My Bookings</strong> page where you can view all your bookings and proceed to payment.
                        </p>
                    </div>
                    
                    <div class="bg-purple-50 p-3 rounded-lg mb-4" id="totalPriceContainer">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Total to Pay:</span>
                            <span class="text-2xl font-bold text-purple-600" id="totalAmount">Rp <?php echo number_format($property['price_per_month'], 0, ',', '.'); ?></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="totalNote">* First month payment</p>
                    </div>
                    
                    <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-calendar-check"></i> Confirm Booking
                    </button>
                </form>
                
                <!-- Link ke My Booking -->
                <div class="mt-4 text-center">
                    <a href="my_bookings.php" class="text-sm text-purple-600 hover:text-purple-800">
                        <i class="fas fa-bookmark"></i> Already have bookings? View My Bookings
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
    // Mobile menu
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Set minimum date for check-in
    var today = new Date().toISOString().split('T')[0];
    var checkinInput = document.querySelector('input[name="checkin"]');
    if (checkinInput) {
        checkinInput.setAttribute('min', today);
    }
    
    // Update total price when duration changes
    var durationSelect = document.getElementById('duration');
    var totalAmountSpan = document.getElementById('totalAmount');
    var pricePerMonth = <?php echo $property['price_per_month']; ?>;
    
    if (durationSelect && totalAmountSpan) {
        durationSelect.addEventListener('change', function() {
            var duration = parseInt(this.value);
            var total = pricePerMonth * duration;
            totalAmountSpan.innerHTML = 'Rp ' + total.toLocaleString('id-ID');
        });
    }
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>