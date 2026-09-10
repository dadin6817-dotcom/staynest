<?php
// reset_admin.php - Reset Admin Password StayNest
// ⚠️ HAPUS FILE INI SETELAH DIGUNAKAN!

require_once 'config/database.php';

$message = '';
$message_type = '';

// Proses reset admin
if (isset($_POST['reset'])) {
    try {
        // Hash password baru
        $new_password = 'admin123';
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Hapus admin lama jika ada
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        // Buat admin baru
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, role, status) 
            VALUES (?, ?, ?, ?, 'admin', 'active')
        ");
        $stmt->execute([
            'admin',
            'admin@staynest.com',
            $hash,
            'Administrator'
        ]);
        
        $message = "✅ Admin berhasil dibuat ulang!<br>
                    Username: <strong>admin</strong><br>
                    Password: <strong>admin123</strong>";
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = "❌ Gagal reset admin: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Cek admin saat ini
$admin_check = null;
try {
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, status, created_at FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin_check = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admin_check = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin - StayNest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">
    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-2xl p-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-key text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-800 mb-2">Reset Admin</h1>
            <p class="text-gray-500 text-sm">Reset password admin ke default</p>
        </div>
        
        <!-- Pesan Sukses / Error -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl border-2 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo $message; ?>
            </div>
            
            <?php if ($message_type === 'success'): ?>
                <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
                    <p class="text-sm text-red-700 font-semibold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        PENTING: HAPUS file <code class="bg-red-100 px-2 py-0.5 rounded">reset_admin.php</code> setelah digunakan!
                    </p>
                </div>
                
                <a href="admin/login.php" 
                   class="w-full block text-center bg-gradient-to-r from-purple-600 to-pink-500 text-white py-4 rounded-xl font-bold hover:shadow-2xl transition transform hover:scale-105 mb-3">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login ke Admin
                </a>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Info Admin Saat Ini -->
        <?php if ($admin_check): ?>
            <div class="mb-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-xl">
                <p class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i> Admin Saat Ini:
                </p>
                <div class="text-xs text-blue-700 space-y-1">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($admin_check['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_check['email']); ?></p>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($admin_check['full_name']); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($admin_check['role']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($admin_check['status']); ?></p>
                    <p><strong>Dibuat:</strong> <?php echo date('d M Y H:i', strtotime($admin_check['created_at'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-6 p-4 bg-yellow-50 border-2 border-yellow-200 rounded-xl">
                <p class="text-sm text-yellow-700">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Tidak ada admin ditemukan!</strong> Klik tombol di bawah untuk membuat admin baru.
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Form Reset -->
        <?php if (!$message || $message_type !== 'success'): ?>
            <form method="POST">
                <button type="submit" 
                        name="reset" 
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition transform hover:scale-105 flex items-center justify-center gap-2"
                        onclick="return confirm('Yakin ingin reset password admin?')">
                    <i class="fas fa-sync-alt"></i>
                    Reset Admin Password
                </button>
            </form>
        <?php endif; ?>
        
        <!-- Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <p class="text-xs text-gray-600 text-center">
                <i class="fas fa-shield-alt mr-1"></i>
                File ini akan mereset admin menjadi:<br>
                <strong>Username:</strong> admin | <strong>Password:</strong> admin123
            </p>
        </div>
        
        <!-- Link Kembali -->
        <div class="mt-4 text-center">
            <a href="welcome.php" class="text-sm text-purple-600 hover:text-purple-800 transition">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Home
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-6">
        <p class="text-white/80 text-sm">
            <i class="fas fa-home mr-1"></i> StayNest - Admin Tools
        </p>
    </div>
</div>

</body>
</html>