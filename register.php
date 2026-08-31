<?php
// register.php - Halaman Register User
$page_title = "Register - StayNest";

require_once dirname(__FILE__) . '/config/database.php';

$error = '';
$success = '';
$full_name = '';
$username = '';
$email = '';
$phone = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    $errors = array();
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username or email already exists!";
            }
        } catch(Exception $e) {}
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, username, email, phone, password, role) 
                VALUES (?, ?, ?, ?, ?, 'user')
            ");
            $stmt->execute([$full_name, $username, $email, $phone, md5($password)]);
            
            $success = "Registration successful! Please login.";
            $full_name = $username = $email = $phone = '';
            
        } catch(Exception $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

require_once dirname(__FILE__) . '/includes/header.php';
?>

<!-- ========================================== -->
<!-- REGISTER FORM -->
<!-- ========================================== -->
<div class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); position: relative; overflow: hidden;">
    
    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
    </div>
    
    <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl p-8 w-full max-w-md border border-white/10 relative z-10">
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">Create Account</h1>
            <p class="text-gray-300 mt-1">Join StayNest today ✨</p>
        </div>
        
        <?php if($success): ?>
            <div class="bg-green-500/20 border border-green-400/30 text-green-200 px-4 py-3 rounded-xl mb-6 backdrop-blur-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
                <a href="login.php" class="text-green-300 font-semibold hover:underline ml-2">Login now →</a>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-xl mb-6 backdrop-blur-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-user mr-2 text-purple-400"></i> Full Name *</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your full name" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-user-tag mr-2 text-purple-400"></i> Username *</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?>" placeholder="Choose a username" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-envelope mr-2 text-purple-400"></i> Email *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-phone mr-2 text-purple-400"></i> Phone Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+62 812 3456 7890" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-lock mr-2 text-purple-400"></i> Password *</label>
                <input type="password" name="password" required placeholder="Min 6 characters" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <div>
                <label class="block text-gray-300 font-medium mb-2 text-sm"><i class="fas fa-check-circle mr-2 text-purple-400"></i> Confirm Password *</label>
                <input type="password" name="confirm_password" required placeholder="Re-enter password" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 transition">
            </div>
            
            <button type="submit" class="w-full py-3 rounded-xl font-semibold text-white flex items-center justify-center gap-2 transition transform hover:scale-105" style="background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">Already have an account? <a href="login.php" class="text-purple-400 font-semibold hover:text-purple-300 transition">Login here</a></p>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-300 text-sm inline-block transition"><i class="fas fa-arrow-left mr-1"></i> Back to Homepage</a>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }
    .animate-pulse {
        animation: pulse 4s ease-in-out infinite;
    }
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>