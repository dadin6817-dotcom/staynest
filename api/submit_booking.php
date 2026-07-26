<?php
// api/submit_booking.php - Submit new booking
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

// If not JSON, try POST form data
if(!$data) {
    $data = $_POST;
}

try {
    // Validate required fields
    $required_fields = ['property_id', 'customer_name', 'customer_email', 'customer_phone', 'check_in_date'];
    foreach($required_fields as $field) {
        if(empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $property_id = $data['property_id'];
    $customer_name = $data['customer_name'];
    $customer_email = $data['customer_email'];
    $customer_phone = $data['customer_phone'];
    $check_in_date = $data['check_in_date'];
    $duration_months = $data['duration_months'] ?? 1;
    
    // Check if property exists and has available rooms
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
    
    if(!$property) {
        throw new Exception("Property not found");
    }
    
    if($property['available_rooms'] <= 0) {
        throw new Exception("No rooms available for this property");
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (property_id, customer_name, customer_email, customer_phone, check_in_date, duration_months, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmt->execute([$property_id, $customer_name, $customer_email, $customer_phone, $check_in_date, $duration_months]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Update available rooms
    $stmt = $pdo->prepare("
        UPDATE properties 
        SET available_rooms = available_rooms - 1, 
            occupied_rooms = occupied_rooms + 1 
        WHERE id = ?
    ");
    $stmt->execute([$property_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Calculate total price
    $total_price = $property['price_per_month'] * $duration_months;
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking created successfully!',
        'booking_id' => $booking_id,
        'property_name' => $property['name'],
        'total_price' => $total_price,
        'formatted_price' => 'Rp ' . number_format($total_price, 0, ',', '.')
    ]);
    
} catch(Exception $e) {
    // Rollback transaction if started
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>