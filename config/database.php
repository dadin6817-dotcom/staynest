<?php
// config/database.php

// Membaca variabel otomatis dari Railway jika online, atau localhost jika di laptop
$host     = getenv('MYSQLHOST')     ?: 'localhost';
$dbname   = getenv('MYSQLDATABASE') ?: 'staynest_db';
$username = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$port     = getenv('MYSQLPORT')     ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>