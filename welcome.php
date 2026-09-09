<?php
// config/database.php
$host = 'localhost';
$dbname = 'staynest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Database tidak ada, tetap lanjut dengan data fallback
    $pdo = null;
}
?>