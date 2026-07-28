<?php
// admin/add_property.php - Add New Property with Music
$page_title = "Add Property - StayNest Admin";

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $total_doors = (int)($_POST['total_doors'] ?? 0);
    $available_rooms = (int)($_POST['available_rooms'] ?? 0);
    $price_per_month = (int)($_POST['price_per_month'] ?? 0);
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $image_url = $_POST['image_url'] ?? '/api/placeholder/400/300';
    $description = $_POST['description'] ?? '';
    
    $occupied_rooms = $total_doors - $available_rooms;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO properties (name, location, total_doors, available_rooms, occupied_rooms, price_per_month, is_vip, image_url, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $location, $total_doors, $available_rooms, $occupied_rooms, $price_per_month, $is_vip, $image_url, $description]);
        
        $success_message = "Property added successfully!";
        
        // Clear form
        $_POST = [];
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - StayNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Music Player untuk Admin -->
    <script src="/staynest/assets/js/music-player.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .sidebar { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%); }
        .nav-item { transition: all 0.3s ease; }
        .nav-item:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); }
        .nav-item.active { background: rgba(255,255,255,0.2); }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <div class="sidebar w-64 text-white flex flex-col flex-shrink-0">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <span class="text-xl font-bold">StayNest Admin</span>
            </div>
            
            <nav class="space-y-2">
                <a href="index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-tachometer-alt w-5"></i> <span>Dashboard</span>
                </a>
                <a href="manage_bookings.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-calendar-check w-5"></i> <span>Manage Bookings</span>
                </a>
                <a href="add_property.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fas fa-plus-circle w-5"></i> <span>Add Property</span>
                </a>
                <a href="../index.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition mt-8">
                    <i class="fas fa-globe w-5"></i> <span>View Website</span>
                </a>
            </nav>
        </div>
        
        <div class="mt-auto p-6 border-t border-white/20">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-semibold"><?php echo $_SESSION['admin_name'] ?? 'Administrator'; ?></p>
                    <p class="text-xs text-white/70">Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center gap-2 text-white/70 hover:text-white transition text-sm">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-8">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Add New Property</h1>
                    <p class="text-gray-500 text-sm mt-1">Add a new boarding house to the system</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <i class="fas fa-headphones text-purple-500"></i>
                    <span>Background music is playing</span>
                </div>
            </div>
            
            <!-- Messages -->
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
            
            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Property Name *</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   placeholder="e.g., Kost Eksklusif" class="form-input">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Location *</label>
                            <input type="text" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                                   placeholder="e.g., Babelan, Bekasi" class="form-input">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Total Doors *</label>
                            <input type="number" name="total_doors" required value="<?php echo $_POST['total_doors'] ?? 0; ?>"
                                   min="1" class="form-input">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Available Rooms *</label>
                            <input type="number" name="available_rooms" required value="<?php echo $_POST['available_rooms'] ?? 0; ?>"
                                   min="0" class="form-input">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Price per Month (Rp) *</label>
                            <input type="number" name="price_per_month" required value="<?php echo $_POST['price_per_month'] ?? 0; ?>"
                                   min="0" step="10000" class="form-input">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Image URL</label>
                            <input type="text" name="image_url" value="<?php echo htmlspecialchars($_POST['image_url'] ?? '/api/placeholder/400/300'); ?>"
                                   placeholder="https://example.com/image.jpg" class="form-input">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for default placeholder</p>
                        </div>
                        
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_vip" value="1" <?php echo isset($_POST['is_vip']) ? 'checked' : ''; ?>
                                       class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                                <span class="text-gray-700 font-medium">Mark as VIP Property</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea name="description" rows="4" class="form-textarea" 
                                  placeholder="Describe the property..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                            <i class="fas fa-save mr-2"></i> Add Property
                        </button>
                        <button type="reset" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>