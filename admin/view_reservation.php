<?php
require_once './config/config.php'; // Database connection

// Get the reservation_id from the URL
$reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

if ($reservation_id > 0) {
    // Fetch reservation details based on the reservation_id
    $query = "SELECT * FROM reservations WHERE reservation_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reservation = $result->fetch_assoc();

        // Return the data as a JSON response
        echo json_encode([
            'client_name' => $reservation['client_name'],
            'client_email' => $reservation['client_email'],
            'client_contact' => $reservation['client_contact'],
            'client_address' => $reservation['client_address'],
            'check_in' => $reservation['check_in'],
            'check_out' => $reservation['check_out'],
            'type' => $reservation['type'],
            'reservation_status' => $reservation['reservation_status'],
            'payment_method' => $reservation['payment_method'],
            'gcash_name' => $reservation['gcash_name'],
            'gcash_number' => $reservation['gcash_number'],
            'gcash_ref' => $reservation['gcash_ref'],
            'total_price' => $reservation['total_price'],
            'amount_paid' => $reservation['amount_paid'],
            'balance' => $reservation['balance'],
            'payment_remarks' => $reservation['payment_remarks'],
        ]);
    } else {
        echo json_encode(['error' => 'Reservation not found.']);
    }
} else {
    echo json_encode(['error' => 'Invalid reservation ID.']);
}

?>
