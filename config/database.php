<?php
// config/database.php - Koneksi Database StayNest

// ==============================================
// KONFIGURASI DATABASE
// ==============================================
$host = 'localhost';
$dbname = 'staynest_db';
$username = 'root';
$password = '';

// ==============================================
// KONEKSI DATABASE DENGAN ERROR HANDLING
// ==============================================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>
        <h2 style='color: #856404; margin-bottom: 15px;'>❌ Database Connection Failed!</h2>
        <p style='color: #856404; margin-bottom: 20px;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <hr style='border-color: #ffc107; margin: 20px 0;'>
        <h3 style='color: #856404; margin-bottom: 10px;'>Solusi:</h3>
        <ol style='color: #856404; line-height: 1.8;'>
            <li>Buka <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin)</li>
            <li>Buat database baru dengan nama <strong>staynest_db</strong></li>
            <li>Pastikan <strong>MySQL</strong> sudah running di XAMPP Control Panel</li>
            <li>Pastikan username <strong>root</strong> dan password <strong>kosong</strong></li>
        </ol>
    </div>
    ");
}

// ==============================================
// START SESSION (Jika Belum Dimulai)
// ==============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================
// FUNGSI RESET ADMIN PASSWORD
// ==============================================
function resetAdminPassword() {
    global $pdo;
    try {
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        
        // Cek apakah admin ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            // Update password
            $update = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE id = ?");
            $update->execute([$new_hash, $admin['id']]);
        } else {
            // Buat admin baru
            $insert = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
            $insert->execute(['admin', 'admin@staynest.com', $new_hash, 'Administrator']);
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ==============================================
// CEK APAKAH TABEL PROPERTIES ADA
// ==============================================
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
    if ($stmt->rowCount() == 0) {
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Insert data dummy jika tabel kosong
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] == 0) {
            $pdo->exec("
                INSERT INTO properties (name, location, description, total_doors, available_rooms, occupied_rooms, price_per_month, is_vip, status) VALUES
                ('StayNest Vela', 'Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi', 'Cozy boarding house with modern facilities. Close to public transportation and shopping centers.', 2, 1, 1, 700000, 0, 'available'),
                ('StayNest Aera', 'Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi', 'Luxury boarding house with VIP facilities. Complete with AC, WiFi, and private bathroom.', 4, 2, 2, 700000, 1, 'available'),
                ('StayNest Elora', 'Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi', 'Spacious boarding house with complete facilities. Includes swimming pool, gym, and 24-hour security.', 12, 7, 5, 800000, 1, 'available')
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Buat admin default jika belum ada
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] == 0) {
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
            $stmt->execute(['admin', 'admin@staynest.com', $password_hash, 'Administrator']);
        }
    } else {
        // Tabel users sudah ada, cek apakah admin ada
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] == 0) {
            // Buat admin baru jika belum ada
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
            $stmt->execute(['admin', 'admin@staynest.com', $password_hash, 'Administrator']);
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
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
                booking_id INT,
                rating INT DEFAULT 5,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
} catch(PDOException $e) {
    // Abaikan error jika tabel sudah ada
}

// ==============================================
// FUNGSI HELPERS
// ==============================================

/**
 * Get user by ID
 */
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

/**
 * Get user by username
 */
function getUserByUsername($username) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Get property by ID
 */
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

/**
 * Get all properties
 */
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

/**
 * Get featured properties
 */
function getFeaturedProperties($limit = 6) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY is_vip DESC, id DESC LIMIT " . (int)$limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Get total bookings by user
 */
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

/**
 * Get total properties
 */
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

/**
 * Get total users
 */
function getTotalUsers() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

/**
 * Get pending bookings count
 */
function getPendingBookings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

/**
 * Get property image
 */
function getPropertyImage($property_id) {
    $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
    $image_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
    
    $prefixes = [1 => 'babelan', 2 => 'alamanda', 3 => 'Vip'];
    $prefix = $prefixes[$property_id] ?? 'default';
    $extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    
    // Cari di uploads
    if (is_dir($upload_path)) {
        $files = scandir($upload_path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            if (stripos($file, $prefix) !== false) {
                return '/staynest/assets/uploads/' . $file;
            }
        }
    }
    
    // Cari di images
    if (is_dir($image_path)) {
        $files = scandir($image_path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions)) continue;
            if (stripos($file, $prefix) !== false) {
                return '/staynest/assets/images/' . $file;
            }
        }
    }
    
    return '/staynest/assets/images/default-property.jpg';
}

/**
 * Format rupiah
 */
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Redirect helper
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Generate booking code
 */
function generateBookingCode() {
    return 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Check database connection
 */
function isDatabaseConnected() {
    global $pdo;
    return isset($pdo) && $pdo !== null;
}

?>