<?php
// config/database.php - TANPA getPropertyImage()

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'staynest_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "<br>Buat database 'staynest_db' di phpMyAdmin");
}

// Buat tabel jika belum ada
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT,
        total_doors INT DEFAULT 0,
        available_rooms INT DEFAULT 0,
        occupied_rooms INT DEFAULT 0,
        price_per_month INT DEFAULT 0,
        is_vip TINYINT(1) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'available',
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        property_id INT NOT NULL,
        booking_code VARCHAR(50) UNIQUE NOT NULL,
        check_in DATE NOT NULL,
        check_out DATE NOT NULL,
        guests INT DEFAULT 1,
        total_price INT NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'full',
        status ENUM('pending', 'active', 'completed', 'cancelled', 'expired') DEFAULT 'pending',
        payment_status ENUM('unpaid', 'paid', 'failed') DEFAULT 'unpaid',
        payment_expiry DATETIME,
        cooldown_until DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        amount INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        payment_date DATETIME NOT NULL,
        payment_proof VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Insert admin default
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $count = $stmt->fetch();
    if ($count['total'] == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
        $stmt->execute(['admin', 'admin@staynest.com', $hash, 'Administrator']);
    }
    
    // Insert properties default
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
    $count = $stmt->fetch();
    if ($count['total'] == 0) {
        $pdo->exec("INSERT INTO properties (name, location, description, total_doors, available_rooms, occupied_rooms, price_per_month, is_vip, status) VALUES
            ('StayNest Vela', 'Babelan, Bekasi', 'Cozy boarding house with modern facilities', 2, 1, 1, 700000, 0, 'available'),
            ('StayNest Aera', 'Tambun Utara, Bekasi', 'Luxury boarding house with VIP facilities', 4, 2, 2, 700000, 1, 'available'),
            ('StayNest Elora', 'Babelan, Bekasi', 'Spacious boarding house with complete facilities', 12, 7, 5, 800000, 1, 'available')");
    }
} catch(PDOException $e) {
    // Abaikan
}

// Helper functions
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
?>