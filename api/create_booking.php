<?php
// create_booking.php - SUPER SIMPLE VERSION
session_start();
header('Content-Type: application/json');

// Koneksi database langsung (tanpa include)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'staynest_db';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
    exit();
}

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Harap login terlebih dahulu']);
    exit();
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Gunakan method POST']);
    exit();
}

// Ambil data dari POST (bukan JSON)
$unit_id = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : 0;
$fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
$checkin_date = isset($_POST['checkin_date']) ? mysqli_real_escape_string($conn, $_POST['checkin_date']) : '';
$duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;

// Validasi sederhana
if ($unit_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Unit ID tidak valid']);
    exit();
}

if (empty($fullname)) {
    echo json_encode(['success' => false, 'message' => 'Nama lengkap harus diisi']);
    exit();
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email harus diisi']);
    exit();
}

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Nomor telepon harus diisi']);
    exit();
}

if (empty($checkin_date)) {
    echo json_encode(['success' => false, 'message' => 'Tanggal check-in harus diisi']);
    exit();
}

if ($duration == 0) {
    echo json_encode(['success' => false, 'message' => 'Durasi harus diisi']);
    exit();
}

// Ambil data unit
$query = "SELECT * FROM units WHERE id = $unit_id";
$result = mysqli_query($conn, $query);
$unit = mysqli_fetch_assoc($result);

if (!$unit) {
    echo json_encode(['success' => false, 'message' => 'Unit tidak ditemukan']);
    exit();
}

$price_per_month = $unit['price_per_month'];
$total_price = $price_per_month * $duration;
$user_id = $_SESSION['user_id'];
$booking_ref = 'STY' . date('YmdHis') . rand(100, 999);

// Simpan booking
$sql = "INSERT INTO bookings (
    user_id, 
    unit_id, 
    booking_ref, 
    fullname, 
    email, 
    phone, 
    checkin_date, 
    duration, 
    price_per_month, 
    total_price, 
    status
) VALUES (
    $user_id, 
    $unit_id, 
    '$booking_ref', 
    '$fullname', 
    '$email', 
    '$phone', 
    '$checkin_date', 
    $duration, 
    $price_per_month, 
    $total_price, 
    'confirmed'
)";

if (mysqli_query($conn, $sql)) {
    $booking_id = mysqli_insert_id($conn);
    
    // Update status unit
    mysqli_query($conn, "UPDATE units SET status = 'booked' WHERE id = $unit_id");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking berhasil!',
        'data' => [
            'booking_id' => $booking_id,
            'booking_ref' => $booking_ref,
            'total_price' => $total_price
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menyimpan: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>