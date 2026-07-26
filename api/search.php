<?php
// api/search.php - Live search endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$query = $_GET['q'] ?? '';

try {
    if(strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, name, location, price_per_month, image_url, available_rooms 
        FROM properties 
        WHERE name LIKE ? OR location LIKE ? 
        LIMIT 10
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    $results = $stmt->fetchAll();
    
    echo json_encode($results);
    
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>