<?php
// profile.php - Halaman Profile User
$page_title = "My Profile - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = null;
$success_message = '';
$error_message = '';

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch(Exception $e) {
    $error_message = "Error loading profile: " . $e->getMessage();
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $errors = array();
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    
    // Check if email already used by another user
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already used by another user";
        }
    } catch(Exception $e) {}
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $user_id]);
            
            // Update session
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            
            $success_message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
        } catch(Exception $e) {
            $error_message = "Update failed: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    $errors = array();
    if (empty($current_password)) $errors[] = "Current password is required";
    if (empty($new_password)) $errors[] = "New password is required";
    if (strlen($new_password) < 6) $errors[] = "New password must be at least 6 characters";
    if ($new_password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Verify current password
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $current = $stmt->fetch();
            
            if ($current && $current['password'] !== md5($current_password)) {
                $errors[] = "Current password is incorrect";
            }
        } catch(Exception $e) {}
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([md5($new_password), $user_id]);
            $success_message = "Password changed successfully!";
        } catch(Exception $e) {
            $error_message = "Password change failed: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!-- ========================================== -->
<!-- PROFILE PAGE -->
<!-- ========================================== -->
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
        <i class="fas fa-user-circle text-purple-600 mr-2"></i> My Profile
    </h1>
    
    <?php if($success_message): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if($error_message): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Profile Info -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-edit text-purple-600 mr-2"></i> Edit Profile
        </h2>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500">
                <p class="text-xs text-gray-400 mt-1">Username cannot be changed</p>
            </div>
            
            <button type="submit" name="update_profile" class="gradient-bg text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                <i class="fas fa-save mr-2"></i> Update Profile
            </button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-key text-purple-600 mr-2"></i> Change Password
        </h2>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Current Password *</label>
                <input type="password" name="current_password" required 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">New Password *</label>
                <input type="password" name="new_password" required 
                       placeholder="Min 6 characters"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Confirm New Password *</label>
                <input type="password" name="confirm_password" required 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 transition">
            </div>
            
            <button type="submit" name="change_password" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl font-semibold transition">
                <i class="fas fa-key mr-2"></i> Change Password
            </button>
        </form>
    </div>
    
    <div class="mt-6 text-center">
        <a href="index.php" class="text-gray-500 hover:text-purple-600 text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Homepage
        </a>
    </div>
</div>

<style>
    .gradient-bg { background: linear-gradient(135deg, #667eea, #764ba2); }
</style>

<?php require_once dirname(__FILE__) . '/includes/footer.php'; ?>