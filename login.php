<?php
// login.php - Halaman Login User
$page_title = "Login - StayNest";

require_once dirname(__FILE__) . '/config/database.php';

$error = '';
$username = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username)) {
        $error = "Username is required!";
    } elseif (empty($password)) {
        $error = "Password is required!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && $user['password'] == md5($password) && $user['is_active'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);
                
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = "Invalid username/email or password!";
            }
        } catch(Exception $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}

require_once dirname(__FILE__) . '/includes/header.php';
?>

<!-- ========================================== -->
<!-- LOGIN FORM -->
<!-- ========================================== -->
<div class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-home text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Welcome Back!</h1>
            <p class="text-gray-500 mt-1">Login to your StayNest account</p>
        </div>
        
        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-gray-700 font-medium mb-2"><i class="fas fa-user mr-2"></i> Username or Email</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username or email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2"><i class="fas fa-lock mr-2"></i> Password</label>
                <input type="password" name="password" required placeholder="Enter your password" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>
                <a href="#" class="text-sm text-purple-600 hover:underline">Forgot password?</a>
            </div>
            
            <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">Don't have an account? <a href="register.php" class="text-purple-600 font-semibold hover:underline">Register here</a></p>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-gray-500 hover:text-purple-600 text-sm inline-block"><i class="fas fa-arrow-left mr-1"></i> Back to Homepage</a>
        </div>
    </div>
</div>

<style>
    .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
    input:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>