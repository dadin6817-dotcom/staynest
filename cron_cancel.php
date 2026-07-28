<?php
// cron_cancel.php - Auto cancel expired payments (Letakkan di folder root staynest/)
// File ini berfungsi untuk membatalkan booking yang melewati batas waktu pembayaran 1x24 jam

require_once __DIR__ . '/config/database.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Buat folder logs jika belum ada
$logs_dir = __DIR__ . '/logs';
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0777, true);
}

try {
    // Update bookings yang melewati deadline pembayaran
    $stmt = $pdo->prepare("
        UPDATE bookings b 
        SET b.status = 'cancelled', 
            b.payment_status = 'expired'
        WHERE b.payment_status = 'unpaid' 
        AND b.payment_deadline < NOW()
        AND b.status = 'confirmed'
    ");
    $stmt->execute();
    
    $cancelled_count = $stmt->rowCount();
    
    // Update ketersediaan kamar untuk booking yang dibatalkan
    if ($cancelled_count > 0) {
        $stmt2 = $pdo->prepare("
            UPDATE properties p
            JOIN bookings b ON b.property_id = p.id
            SET p.available_rooms = p.available_rooms + 1,
                p.occupied_rooms = p.occupied_rooms - 1
            WHERE b.payment_status = 'expired'
            AND b.status = 'cancelled'
        ");
        $stmt2->execute();
    }
    
    // Log hasil (dengan pengecekan error)
    $log_message = date('Y-m-d H:i:s') . " - Expired bookings cancelled: " . $cancelled_count . " bookings\n";
    $log_file = $logs_dir . '/cron_log.txt';
    
    // Coba tulis log, jika gagal abaikan
    @file_put_contents($log_file, $log_message, FILE_APPEND);
    
    echo "✅ " . date('Y-m-d H:i:s') . " - Expired bookings cancelled: " . $cancelled_count . " bookings\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Log error (dengan pengecekan)
    $error_log = $logs_dir . '/cron_error.txt';
    @file_put_contents($error_log, date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
}
?>