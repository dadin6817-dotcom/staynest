<?php
// admin/login.php - Login Admin (REVISI FINAL)
$page_title = "Admin Login - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login sebagai admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

require_once dirname(__FILE__) . '/../config/database.php';

$error = '';
$debug_info = '';

// ==============================================
// PROSES LOGIN
// ==============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            // Cek user di tabel users (BUKAN admin_users)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $password_valid = false;
                
                // Cek 1: Password hash (cara normal)
                if (password_verify($password, $user['password'])) {
                    $password_valid = true;
                }
                // Cek 2: Password plain text (fallback)
                elseif ($password === $user['password']) {
                    $password_valid = true;
                    
                    // Auto-update ke hash
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->execute([$new_hash, $user['id']]);
                }
                
                // Cek 3: Role harus admin
                $is_admin = isset($user['role']) && in_array($user['role'], ['admin', 'super_admin']);
                
                if ($password_valid && $is_admin) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                    $_SESSION['role'] = 'admin'; // Set jadi 'admin'
                    $_SESSION['email'] = $user['email'] ?? '';
                    
                    header('Location: index.php');
                    exit;
                } else {
                    if (!$password_valid) {
                        $error = 'Invalid password.';
                    } elseif (!$is_admin) {
                        $error = 'You are not an admin.';
                    } else {
                        $error = 'Invalid username or password.';
                    }
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'System error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); min-height: 100vh; }
        input { pointer-events: auto !important; user-select: text !important; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <!-- Icon -->
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-shield text-white text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-black text-gray-800 mb-2">Admin Login</h1>
                    <p class="text-gray-500 text-sm">Login to manage StayNest properties</p>
                </div>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border-2 border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" action="" autocomplete="off">
                    <!-- Username -->
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-semibold mb-2 text-sm">
                            <i class="fas fa-user text-purple-600 mr-1"></i> Username
                        </label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" 
                                   id="username"
                                   name="username" 
                                   placeholder="Enter username"
                                   required
                                   autofocus
                                   autocomplete="off"
                                   class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition text-gray-700">
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-semibold mb-2 text-sm">
                            <i class="fas fa-lock text-purple-600 mr-1"></i> Password
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="password" 
                                   id="password"
                                   name="password" 
                                   placeholder="Enter password"
                                   required
                                   autocomplete="new-password"
                                   class="w-full pl-12 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition text-gray-700">
                            <button type="button" 
                                    id="togglePassword"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600 cursor-pointer"
                                    style="background: none; border: none;">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" 
                            name="login"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition transform hover:scale-105 cursor-pointer">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Dashboard
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="flex items-center gap-4 my-6">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium">OR</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>
                
                <!-- Back to Home -->
                <a href="../welcome.php" 
                   class="w-full border-2 border-purple-600 text-purple-600 py-3 rounded-xl font-semibold hover:bg-purple-600 hover:text-white transition flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Password
        var togglePassword = document.getElementById('togglePassword');
        var passwordInput = document.getElementById('password');
        var eyeIcon = document.getElementById('eyeIcon');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.className = 'fas fa-eye-slash';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.className = 'fas fa-eye';
                }
            });
        }
        
        // Force input focus
        setTimeout(function() {
            var u = document.getElementById('username');
            if (u) u.focus();
        }, 100);
    });
    </script>
</body>
</html>