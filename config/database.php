<?php
// config/database.php

// Ambil variabel dari Environment Railway, atau fallback ke localhost
$host     = $_ENV['MYSQLHOST']     ?? $_SERVER['MYSQLHOST']     ?? getenv('MYSQLHOST')     ?: '127.0.0.1';
$dbname   = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?: 'staynest_db';
$username = $_ENV['MYSQLUSER']     ?? $_SERVER['MYSQLUSER']     ?? getenv('MYSQLUSER')     ?: 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?: '';
$port     = $_ENV['MYSQLPORT']     ?? $_SERVER['MYSQLPORT']     ?? getenv('MYSQLPORT')     ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>