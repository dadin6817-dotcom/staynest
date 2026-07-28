<?php
// api/check_availability.php - Check room availability
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$property_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT id, name, available_rooms, total_doors FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
    
    if(!$property) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Property not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'status' => 'success',
        'property_id' => $property['id'],
        'property_name' => $property['name'],
        'available' => $property['available_rooms'] > 0,
        'available_rooms' => $property['available_rooms'],
        'total_rooms' => $property['total_doors'],
        'message' => $property['available_rooms'] > 0 
            ? "{$property['available_rooms']} rooms available" 
            : "Sorry, this property is fully booked"
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>