<?php
require_once './config/config.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_ids"])) {
    $selected_ids = $_POST["selected_ids"];

    if (!empty($selected_ids)) {
        // Convert the array to a comma-separated string for SQL query
        $ids = implode(',', array_map('intval', $selected_ids));

        // First, retrieve the room IDs associated with the selected reservations
        $room_query = "SELECT room_id FROM reservations WHERE reservation_id IN ($ids)";
        $room_result = $mysqli->query($room_query);

        $room_ids = [];
        while ($row = $room_result->fetch_assoc()) {
            $room_ids[] = $row['room_id'];
        }

        // Delete the reservations
        $delete_query = "DELETE FROM reservations WHERE reservation_id IN ($ids)";
        $stmt = $mysqli->prepare($delete_query);

        if ($stmt->execute()) {
            // Update the room statuses to "Available"
            if (!empty($room_ids)) {
                $room_ids_str = implode(',', array_map('intval', $room_ids));
                $update_room_query = "UPDATE rooms SET room_status = 'Available' WHERE room_id IN ($room_ids_str)";
                $mysqli->query($update_room_query);
            }

            // Redirect back with a success message
            header("Location: online_reservation.php?message=Selected reservations deleted successfully, rooms set to Available&status=success");
            exit();
        } else {
            // Redirect back with an error message
            header("Location: online_reservation.php?message=Failed to delete selected reservations&status=error");
            exit();
        }
    }
} else {
    // Redirect back if no reservation is selected
    header("Location: online_reservation.php?message=No reservations selected for deletion&status=warning");
    exit();
}
?>
