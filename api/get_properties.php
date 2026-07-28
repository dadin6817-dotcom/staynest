<?php
// api/get_properties.php - Get all properties with filters
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $location = $_GET['location'] ?? '';
    $min_price = $_GET['min_price'] ?? 0;
    $max_price = $_GET['max_price'] ?? 10000000;
    $available_only = isset($_GET['available_only']) ? $_GET['available_only'] === 'true' : false;
    
    // Build query
    $sql = "SELECT * FROM properties WHERE 1=1";
    $params = [];
    
    if($search) {
        $sql .= " AND (name LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if($location) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$location%";
    }
    
    if($min_price > 0) {
        $sql .= " AND price_per_month >= ?";
        $params[] = $min_price;
    }
    
    if($max_price < 10000000) {
        $sql .= " AND price_per_month <= ?";
        $params[] = $max_price;
    }
    
    if($available_only) {
        $sql .= " AND available_rooms > 0";
    }
    
    $sql .= " ORDER BY is_vip DESC, id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'count' => count($properties),
        'data' => $properties
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>