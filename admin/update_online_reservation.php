<?php
require_once './config/config.php'; // Ensure database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? null;
    $reservation_status = $_POST['reservation_status'] ?? null;

    if ($reservation_id && $reservation_status) {
        $query = "UPDATE reservations SET reservation_status = ? WHERE reservation_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $reservation_status, $reservation_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Reservation updated successfully.'); window.location.href='online_reservation.php';</script>";
        } else {
            echo "<script>alert('Failed to update reservation.'); window.location.href='online_reservation.php';</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Invalid input data.'); window.location.href='online_reservation.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request method.'); window.location.href='online_reservation.php';</script>";
}

$mysqli->close();
