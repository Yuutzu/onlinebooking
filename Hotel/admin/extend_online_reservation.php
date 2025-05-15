<?php
// extend_online_reservation.php
require('../admin/config/config.php');

// Check if data was received from the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $reservationId = isset($_POST['reservation_id']) ? (int) $_POST['reservation_id'] : 0;
    $newCheckOutDate = isset($_POST['new_check_out_date']) ? $_POST['new_check_out_date'] : '';
    $newTotalPrice = isset($_POST['new_total_price']) ? (float) $_POST['new_total_price'] : 0;

    // Validate the inputs
    if ($reservationId > 0 && !empty($newCheckOutDate)) {
        // Prepare the update query for online reservations
        $query = "UPDATE reservations
                  SET check_out = ?, total_price = ?
                  WHERE reservation_id = ? AND type = 'Online'";

        if ($stmt = $mysqli->prepare($query)) {
            // Bind the parameters
            $stmt->bind_param("sdi", $newCheckOutDate, $newTotalPrice, $reservationId);

            // Execute the statement
            if ($stmt->execute()) {
                // Success response
                echo json_encode(['success' => true]);
            } else {
                // Error executing the query
                echo json_encode(['success' => false, 'error' => 'Database update failed.']);
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the query
            echo json_encode(['success' => false, 'error' => 'Query preparation failed.']);
        }
    } else {
        // Invalid data
        echo json_encode(['success' => false, 'error' => 'Invalid reservation ID or date.']);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
