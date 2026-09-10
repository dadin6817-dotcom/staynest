<?php
// admin/login.php - Halaman Login Admin
$page_title = "Admin Login - StayNest";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login sebagai admin, redirect ke dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

require_once dirname(__FILE__) . '/../config/database.php';

$error = '';
$success = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            // Cek user dengan role admin
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['success'] = 'Welcome Admin!';
                    header('Location: index.php');
                    exit;
                } else {
                    // Coba cek apakah password disimpan plain text (untuk migrasi)
                    if ($password === $user['password']) {
                        // Update password ke hash
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update->execute([$new_hash, $user['id']]);
                        
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Invalid username or password.';
                    }
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'System error. Please try again.';
        }
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<!-- ========================================== -->
<!-- ADMIN LOGIN PAGE - MODERN DESIGN -->
<!-- ========================================== -->
<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-20 px-4 relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-96 h-96 bg-white/10 rounded-full -top-20 -left-20 animate-blob"></div>
        <div class="absolute w-96 h-96 bg-yellow-300/10 rounded-full top-1/2 -right-20 animate-blob animation-delay-2000"></div>
        <div class="absolute w-96 h-96 bg-pink-300/10 rounded-full -bottom-20 left-1/3 animate-blob animation-delay-4000"></div>
    </div>
    
    <div class="relative z-10 w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30">
            <!-- Icon -->
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform hover:scale-110 transition">
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
            
            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-50 border-2 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="">
                <!-- Username -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-user text-purple-600 mr-1"></i> Username
                    </label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               name="username" 
                               placeholder="Enter username" 
                               required
                               autocomplete="username"
                               class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition text-gray-700">
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-lock text-purple-600 mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" 
                               name="password" 
                               id="passwordInput"
                               placeholder="Enter password" 
                               required
                               autocomplete="current-password"
                               class="w-full pl-12 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition text-gray-700">
                        <button type="button" 
                                id="togglePassword"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600 transition">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        name="login"
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Login to Dashboard
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
        
        <!-- Footer Info -->
        <div class="text-center mt-6">
            <p class="text-white/80 text-sm">
                <i class="fas fa-shield-alt mr-1"></i>
                Secure Admin Area - StayNest
            </p>
        </div>
    </div>
</section>

<style>
    @keyframes blob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>

<script>
// Toggle Password Visibility
document.addEventListener('DOMContentLoaded', function() {
    var togglePassword = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('passwordInput');
    var eyeIcon = document.getElementById('eyeIcon');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        });
    }
});
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>