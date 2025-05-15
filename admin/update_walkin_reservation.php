<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reservation_id"], $_POST["reservation_status"])) {
    $reservation_id = intval($_POST["reservation_id"]);
    $reservation_status = $mysqli->real_escape_string($_POST["reservation_status"]);

    // Update the reservation status
    $update_query = "UPDATE walkin_reservation SET reservation_status = ? WHERE reservation_id = ?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("si", $reservation_status, $reservation_id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Reservation status updated successfully.');
            window.location.href = 'walkin_reservation.php';
        </script>";
    } else {
        echo "<script>
            alert('Error updating reservation.');
            window.location.href = 'walkin_reservation.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request.');
        window.location.href = 'walkin_reservation.php';
    </script>";
}
?>
