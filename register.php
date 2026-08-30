<?php
// register.php - Halaman Register User
$page_title = "Register - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

$error = '';
$success = '';
$full_name = '';
$username = '';
$email = '';
$phone = '';

// If already logged in, redirect to homepage
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
    
    // Validation
    $errors = array();
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check if username already exists
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
            
            // Clear form
            $full_name = $username = $email = $phone = '';
            
        } catch(Exception $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - StayNest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); }
        .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
        input:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
            <p class="text-gray-500 mt-1">Join StayNest today</p>
        </div>
        
        <?php if($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
                <a href="login.php" class="text-green-700 font-semibold hover:underline ml-2">Login now</a>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($full_name); ?>" 
                       placeholder="Enter your full name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Username *</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?>" 
                       placeholder="Choose a username" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" 
                       placeholder="Enter your email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" 
                       placeholder="+62 812 3456 7890" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Password *</label>
                <input type="password" name="password" required 
                       placeholder="Min 6 characters" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Confirm Password *</label>
                <input type="password" name="confirm_password" required 
                       placeholder="Re-enter password" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
                <i class="fas fa-user-plus mr-2"></i> Create Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Already have an account? 
                <a href="login.php" class="text-purple-600 font-semibold hover:underline">Login here</a>
            </p>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-gray-500 hover:text-purple-600 text-sm inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Back to Homepage
            </a>
        </div>
    </div>

</body>
</html>