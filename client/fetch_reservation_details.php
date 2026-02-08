<?php
session_start();
include('../admin/config/config.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['reservation_id']) || !is_numeric($data['reservation_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid reservation ID."]);
    exit;
}

$reservation_id = intval($data['reservation_id']);

// Fetch reservation details
$query = "
    SELECT r.reservation_id, r.check_in, r.check_out, r.payment_method, 
           r.gcash_name, r.gcash_number, r.gcash_ref, r.gcash_screenshot,
           r.total_price, r.type, r.reservation_status, c.client_name, c.client_email,
           rm.room_name, rm.room_number, rm.room_description, 
           rm.room_category, rm.room_price, rm.room_picture
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN rooms rm ON r.room_id = rm.room_id
    WHERE r.reservation_id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$reservation_details = $result->fetch_assoc();

if (!$reservation_details) {
    echo json_encode(["status" => "error", "message" => "Reservation not found."]);
    exit;
}

// Ensure total_price is a valid number
$reservation_details['total_price'] = isset($reservation_details['total_price']) ? floatval($reservation_details['total_price']) : 0.00;
$reservation_details['room_price'] = isset($reservation_details['room_price']) ? floatval($reservation_details['room_price']) : 0.00;

// Return JSON response
echo json_encode(["status" => "success", "data" => $reservation_details]);

$stmt->close();
$mysqli->close();
?>
