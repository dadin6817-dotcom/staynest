<?php
// admin/logout.php - Logout Admin StayNest (REVISI FINAL)

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================
// HAPUS SEMUA DATA SESSION
// ==============================================
$_SESSION = array();

// ==============================================
// HAPUS SESSION COOKIE
// ==============================================
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// ==============================================
// DESTROY SESSION
// ==============================================
session_destroy();

// ==============================================
// REDIRECT KE LOGIN ADMIN
// ==============================================
header('Location: login.php');
exit;
?>