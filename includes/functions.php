<?php
// includes/functions.php - Kumpulan Fungsi Helper StayNest
// File ini berisi semua fungsi bantuan yang dipakai di seluruh halaman

// ==============================================
// FUNGSI GET PROPERTY IMAGE
// ==============================================
if (!function_exists('getPropertyImage')) {
    function getPropertyImage($property_id) {
        $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/uploads/';
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/staynest/assets/images/';
        
        // Mapping prefix gambar berdasarkan property_id
        $prefixes = [
            1 => 'babelan',
            2 => 'alamanda',
            3 => 'Vip'
        ];
        
        $prefix = $prefixes[$property_id] ?? 'default';
        $extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        
        // ==========================================
        // CARI DI FOLDER UPLOADS
        // ==========================================
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
        
        // ==========================================
        // CARI DI FOLDER IMAGES
        // ==========================================
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
        
        // ==========================================
        // DEFAULT IMAGE
        // ==========================================
        return '/staynest/assets/images/default-property.jpg';
    }
}

// ==============================================
// FUNGSI FORMAT RUPIAH
// ==============================================
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// ==============================================
// FUNGSI CEK LOGIN
// ==============================================
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// ==============================================
// FUNGSI CEK ADMIN
// ==============================================
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

// ==============================================
// FUNGSI REDIRECT
// ==============================================
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
}

// ==============================================
// FUNGSI GENERATE BOOKING CODE
// ==============================================
if (!function_exists('generateBookingCode')) {
    function generateBookingCode() {
        return 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

// ==============================================
// FUNGSI GET USER BY ID
// ==============================================
if (!function_exists('getUserById')) {
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
}

// ==============================================
// FUNGSI GET PROPERTY BY ID
// ==============================================
if (!function_exists('getPropertyById')) {
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
}

// ==============================================
// FUNGSI GET ALL PROPERTIES
// ==============================================
if (!function_exists('getAllProperties')) {
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
}

// ==============================================
// FUNGSI GET TOTAL BOOKINGS USER
// ==============================================
if (!function_exists('getTotalBookings')) {
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
}

// ==============================================
// FUNGSI GET TOTAL PROPERTIES
// ==============================================
if (!function_exists('getTotalProperties')) {
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
}

// ==============================================
// FUNGSI SANITIZE INPUT
// ==============================================
if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// ==============================================
// FUNGSI CHECK DATABASE CONNECTION
// ==============================================
if (!function_exists('isDatabaseConnected')) {
    function isDatabaseConnected() {
        global $pdo;
        return isset($pdo) && $pdo !== null;
    }
}

// ==============================================
// FUNGSI LOGOUT
// ==============================================
if (!function_exists('logout')) {
    function logout() {
        session_destroy();
        header('Location: /staynest/welcome.php');
        exit;
    }
}

// ==============================================
// FUNGSI CHECK EXPIRED BOOKING
// ==============================================
if (!function_exists('checkBookingExpired')) {
    function checkBookingExpired($booking_id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT payment_expiry, status FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['status'] == 'pending') {
                $now = new DateTime();
                $expiry = new DateTime($result['payment_expiry']);
                
                if ($now > $expiry) {
                    $cooldown_time = new DateTime();
                    $cooldown_time->modify('+2 minutes');
                    
                    $stmt = $pdo->prepare("UPDATE bookings SET status = 'expired', cooldown_until = ? WHERE id = ?");
                    $stmt->execute([$cooldown_time->format('Y-m-d H:i:s'), $booking_id]);
                    
                    return true;
                }
            }
            return false;
        } catch(Exception $e) {
            return false;
        }
    }
}

// ==============================================
// FUNGSI CHECK COOLDOWN
// ==============================================
if (!function_exists('checkCooldown')) {
    function checkCooldown($booking_id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT cooldown_until FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['cooldown_until'])) {
                $now = new DateTime();
                $cooldown = new DateTime($result['cooldown_until']);
                
                if ($now < $cooldown) {
                    $diff = $now->diff($cooldown);
                    return [
                        'active' => true,
                        'minutes' => $diff->i,
                        'seconds' => $diff->s
                    ];
                }
            }
            return ['active' => false];
        } catch (Exception $e) {
            return ['active' => false];
        }
    }
}

// ==============================================
// FUNGSI UPLOAD FILE
// ==============================================
if (!function_exists('uploadFile')) {
    function uploadFile($file, $target_dir, $prefix = 'file') {
        // Cek error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error'];
        }
        
        // Cek ukuran (max 2MB)
        if ($file['size'] > 2097152) {
            return ['success' => false, 'message' => 'File too large (max 2MB)'];
        }
        
        // Cek ekstensi
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        // Buat folder jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate nama file baru
        $new_filename = $prefix . '_' . time() . '.' . $file_ext;
        $target_path = $target_dir . $new_filename;
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => true, 'filename' => $new_filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
    }
}

// ==============================================
// FUNGSI GET USER AVATAR
// ==============================================
if (!function_exists('getUserAvatar')) {
    function getUserAvatar($full_name) {
        $initial = strtoupper(substr($full_name, 0, 1));
        return $initial;
    }
}

// ==============================================
// FUNGSI TIME AGO
// ==============================================
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
        
        if ($difference < 60) {
            return 'Baru saja';
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes . ' menit lalu';
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ' jam lalu';
        } elseif ($difference < 604800) {
            $days = floor($difference / 86400);
            return $days . ' hari lalu';
        } else {
            return date('d M Y', $timestamp);
        }
    }
}

// ==============================================
// FUNGSI CHECK ROLE
// ==============================================
if (!function_exists('hasRole')) {
    function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
}

// ==============================================
// FUNGSI GET GREETING
// ==============================================
if (!function_exists('getGreeting')) {
    function getGreeting() {
        $hour = date('H');
        if ($hour < 12) {
            return 'Good Morning';
        } elseif ($hour < 15) {
            return 'Good Afternoon';
        } elseif ($hour < 18) {
            return 'Good Evening';
        } else {
            return 'Good Night';
        }
    }
}
?>