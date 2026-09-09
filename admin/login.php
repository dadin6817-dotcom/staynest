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
require_once dirname(__FILE__) . '/../includes/header.php';

$error = '';
$success = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success'] = 'Welcome Admin!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-r from-purple-600 to-purple-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-shield text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Admin Login</h1>
            <p class="text-gray-500 text-sm mt-2">Login to manage StayNest properties</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="username" 
                           placeholder="Enter username" 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" 
                           name="password" 
                           placeholder="Enter password" 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
            </div>
            
            <button type="submit" 
                    name="login"
                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <a href="../index.php" class="text-purple-600 hover:text-purple-800 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back to Home
            </a>
        </div>
        
        <!-- Demo Credentials -->
        <div class="mt-4 p-4 bg-gray-50 rounded-xl text-center">
            <p class="text-xs text-gray-500">Demo Credentials</p>
            <p class="text-sm text-gray-600 font-medium">Username: <span class="text-purple-600">admin</span> | Password: <span class="text-purple-600">admin123</span></p>
        </div>
    </div>
</div>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>