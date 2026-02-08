<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_ids"])) {
    $selected_ids = $_POST["selected_ids"];
    
    if (!empty($selected_ids)) {
        // Convert array to placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        
        // First, retrieve the room IDs associated with the selected reservations
        $room_query = "SELECT room_id FROM walkin_reservation WHERE reservation_id IN ($placeholders)";
        $stmt_room = $mysqli->prepare($room_query);
        $stmt_room->bind_param(str_repeat('s', count($selected_ids)), ...$selected_ids);
        $stmt_room->execute();
        $room_result = $stmt_room->get_result();

        $room_ids = [];
        while ($row = $room_result->fetch_assoc()) {
            $room_ids[] = $row['room_id'];
        }
        $stmt_room->close();

        // Prepare delete query for walk-in reservations
        $delete_query = "DELETE FROM walkin_reservation WHERE reservation_id IN ($placeholders)";
        $stmt_delete = $mysqli->prepare($delete_query);
        $stmt_delete->bind_param(str_repeat('s', count($selected_ids)), ...$selected_ids);

        if ($stmt_delete->execute()) {
            $stmt_delete->close();

            // Update the room statuses to "Available"
            if (!empty($room_ids)) {
                $room_placeholders = implode(',', array_fill(0, count($room_ids), '?'));
                $update_room_query = "UPDATE rooms SET room_status = 'Available' WHERE room_id IN ($room_placeholders)";
                $stmt_update = $mysqli->prepare($update_room_query);
                $stmt_update->bind_param(str_repeat('s', count($room_ids)), ...$room_ids);
                $stmt_update->execute();
                $stmt_update->close();
            }

            echo "<script>
                alert('Selected walk-in reservations have been deleted successfully.');
                window.location.href = 'walkin_reservation.php';
            </script>";
        } else {
            echo "<script>
                alert('Error deleting reservations.');
                window.location.href = 'walkin_reservation.php';
            </script>";
        }
    }
} else {
    echo "<script>
        alert('No reservations selected.');
        window.location.href = 'walkin_reservation.php';
    </script>";
}
?>
