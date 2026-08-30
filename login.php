<?php
// login.php - Halaman Login User
$page_title = "Login - StayNest";

require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/header.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StayNest ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s infinite ease-in-out;
        }
        
        .bubble:nth-child(1) { width: 200px; height: 200px; top: -50px; left: -50px; animation-delay: 0s; }
        .bubble:nth-child(2) { width: 300px; height: 300px; bottom: -100px; right: -100px; animation-delay: -5s; }
        .bubble:nth-child(3) { width: 150px; height: 150px; top: 50%; left: 80%; animation-delay: -10s; }
        .bubble:nth-child(4) { width: 100px; height: 100px; bottom: 20%; left: 10%; animation-delay: -15s; }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(20px, 30px) scale(1.05); }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(25px);
            border-radius: 32px;
            padding: 48px 40px;
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
            color: white;
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s ease;
        }
        
        .logo-icon:hover {
            transform: scale(1.05) rotate(-5deg);
        }
        
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-align: center;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin-top: 8px;
            font-weight: 400;
        }
        
        .error-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fca5a5;
            font-size: 14px;
            animation: shake 0.4s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        
        .error-box i {
            font-size: 18px;
            color: #ef4444;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }
        
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .form-options label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            cursor: pointer;
        }
        
        .form-options input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .form-options a {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .form-options a:hover {
            color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #a78bfa;
        }
        
        .back-home {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-home:hover {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .back-home i {
            margin-right: 6px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <!-- Animated Bubbles -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <div class="login-card">
        <!-- Logo -->
        <div class="logo-icon">
            <i class="fas fa-home"></i>
        </div>
        
        <h1>Welcome Back!</h1>
        <p class="subtitle">Login to your StayNest account</p>
        
        <?php if($error): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username or Email</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username or email">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            
            <div class="form-options">
                <label>
                    <input type="checkbox"> Remember me
                </label>
                <a href="#">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Back to Homepage
        </a>
    </div>

</body>
</html>