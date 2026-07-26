<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Koneksi database
$conn = mysqli_connect('localhost', 'root', '', 'staynest_db');

// Ambil data unit
$unit_id = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 1;
$query = "SELECT * FROM units WHERE id = $unit_id";
$result = mysqli_query($conn, $query);
$unit = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Form</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
        .unit-name {
            text-align: center;
            color: #667eea;
            margin-bottom: 20px;
        }
        .price {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            opacity: 0.9;
        }
        .total {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .total span {
            font-size: 24px;
            font-weight: bold;
            color: #ff6b6b;
        }
        .btn-mybooking {
            background: linear-gradient(135deg, #667eea, #764ba2);
            margin-top: 10px;
        }
        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>✈️ Booking Unit</h2>
        <div class="unit-name">
            <strong><?php echo $unit['unit_name']; ?></strong>
        </div>
        <div class="price">
            Rp <?php echo number_format($unit['price_per_month'], 0, ',', '.'); ?>
            <small style="font-size: 14px;">/month</small>
        </div>
        
        <div id="messageBox" class="message"></div>
        
        <form id="bookingForm" method="POST" action="api/create_booking.php">
            <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="fullname" id="fullname" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" name="phone" id="phone" required>
            </div>
            
            <div class="form-group">
                <label>Tanggal Check-in</label>
                <input type="date" name="checkin_date" id="checkin_date" required>
            </div>
            
            <div class="form-group">
                <label>Durasi (bulan)</label>
                <select name="duration" id="duration">
                    <option value="1">1 bulan</option>
                    <option value="2">2 bulan</option>
                    <option value="3">3 bulan</option>
                    <option value="6">6 bulan</option>
                    <option value="12">12 bulan</option>
                </select>
            </div>
            
            <div class="total">
                Total Harga: <span id="totalPrice">Rp 0</span>
            </div>
            
            <button type="submit">✅ Konfirmasi Booking</button>
            <button type="button" class="btn-mybooking" onclick="window.location.href='my_booking.php'">
                📋 Lihat My Booking
            </button>
        </form>
    </div>
</div>

<script>
// Set minimum date
const today = new Date().toISOString().split('T')[0];
document.getElementById('checkin_date').min = today;
document.getElementById('checkin_date').value = today;

// Calculate total price
const pricePerMonth = <?php echo $unit['price_per_month']; ?>;
const durationSelect = document.getElementById('duration');
const totalSpan = document.getElementById('totalPrice');

function updateTotal() {
    const duration = parseInt(durationSelect.value);
    const total = pricePerMonth * duration;
    totalSpan.textContent = 'Rp ' + total.toLocaleString('id-ID');
}

durationSelect.addEventListener('change', updateTotal);
updateTotal();

// Handle form submission with AJAX
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageBox = document.getElementById('messageBox');
    
    fetch('api/create_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageBox.className = 'message success';
            messageBox.textContent = '✓ ' + data.message + ' Redirecting...';
            setTimeout(() => {
                window.location.href = 'my_booking.php';
            }, 1500);
        } else {
            messageBox.className = 'message error';
            messageBox.textContent = '✗ ' + data.message;
        }
    })
    .catch(error => {
        messageBox.className = 'message error';
        messageBox.textContent = '✗ Terjadi kesalahan: ' + error;
    });
});
</script>
</body>
</html>