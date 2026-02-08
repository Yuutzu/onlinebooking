<?php
include('../admin/config/config.php');

// Fetch POST data from request
$data = json_decode(file_get_contents('php://input'), true);
$reservationId = $data['reservation_id'];

// Query to fetch reservation details from the database
$query = "SELECT reservation_id, room_id, check_in, check_out, total_price 
          FROM reservations WHERE reservation_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $reservationId);
$stmt->execute();
$result = $stmt->get_result();

// Check if a reservation was found
$reservation = $result->fetch_assoc();

// Debugging: Log the fetched reservation data
error_log("Fetched reservation data: " . print_r($reservation, true));

if ($reservation) {
    // Return the reservation data as a JSON response
    echo json_encode($reservation);
} else {
    // Return an error message if no reservation found
    echo json_encode(['error' => 'Reservation not found']);
}
?>
