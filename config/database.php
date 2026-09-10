<?php
// config/database.php - Koneksi Database

// ==============================================
// KONFIGURASI DATABASE
// ==============================================
$host = 'localhost';
$dbname = 'staynest_db';  // Ganti dengan nama database kamu
$username = 'root';
$password = '';

// ==============================================
// KONEKSI DATABASE DENGAN ERROR HANDLING
// ==============================================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Jika database tidak ditemukan, tampilkan pesan error yang jelas
    die("❌ Database connection failed! Please check your database configuration.<br>
         Error: " . $e->getMessage() . "<br>
         <br>
         <strong>SOLUTION:</strong><br>
         1. Create database named <strong>'staynest_db'</strong> in phpMyAdmin<br>
         2. Or change database name in config/database.php<br>
         3. Make sure MySQL is running in XAMPP/WAMP");
}

// ==============================================
// START SESSION (Jika Belum Dimulai)
// ==============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================
// CEK APAKAH TABEL PROPERTIES ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
    if ($stmt->rowCount() == 0) {
        // Buat tabel properties jika belum ada
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS properties (
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Insert data dummy jika tabel kosong
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] == 0) {
            $pdo->exec("
                INSERT INTO properties (name, location, description, total_doors, available_rooms, occupied_rooms, price_per_month, is_vip, status) VALUES
                ('StayNest Vela', 'Babelan, Bekasi', 'Cozy boarding house with modern facilities', 2, 1, 1, 700000, 0, 'available'),
                ('StayNest Aera', 'Tambun Utara, Bekasi', 'Luxury boarding house with VIP facilities', 4, 2, 2, 700000, 1, 'available'),
                ('StayNest Elora', 'Babelan, Bekasi', 'Spacious boarding house with complete facilities', 12, 7, 5, 800000, 1, 'available')
            ");
        }
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// CEK APAKAH TABEL USERS ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                role ENUM('user', 'admin') DEFAULT 'user',
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Buat admin default jika belum ada
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] == 0) {
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("
                INSERT INTO users (username, email, password, full_name, role, status) VALUES
                ('admin', 'admin@staynest.com', '$password_hash', 'Administrator', 'admin', 'active')
            ");
        }
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// CEK APAKAH TABEL BOOKINGS ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bookings (
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
            )
        ");
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// CEK APAKAH TABEL PAYMENTS ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                booking_id INT NOT NULL,
                amount INT NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                transaction_id VARCHAR(50) UNIQUE NOT NULL,
                status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
                payment_date DATETIME NOT NULL,
                payment_proof VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
            )
        ");
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// CEK APAKAH TABEL REVIEWS ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reviews (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                property_id INT NOT NULL,
                booking_id INT NOT NULL,
                rating INT DEFAULT 5,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
            )
        ");
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// FUNGSI HELPERS
// ==============================================

// Fungsi untuk mendapatkan data user
function getUserById($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

// Fungsi untuk mendapatkan data property
function getPropertyById($property_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

// Fungsi untuk mendapatkan semua properties
function getAllProperties($limit = null) {
    global $pdo;
    try {
        $sql = "SELECT * FROM properties WHERE status = 'available' ORDER BY is_vip DESC, id DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Fungsi untuk mendapatkan featured properties
function getFeaturedProperties($limit = 6) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY is_vip DESC, id DESC LIMIT " . (int)$limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Fungsi untuk mendapatkan total bookings user
function getTotalBookings($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Fungsi untuk mendapatkan total properties
function getTotalProperties() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'available'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Fungsi untuk cek koneksi database
function isDatabaseConnected() {
    global $pdo;
    return isset($pdo);
}

?> 