<?php
require_once './config/config.php';

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    $query = "SELECT room_description, room_price, room_picture FROM rooms WHERE room_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($room = $result->fetch_assoc()) {
        // Correct the image path
        $room['room_picture'] = "./dist/img/" . $room['room_picture'];

        header('Content-Type: application/json');
        echo json_encode($room);
    } else {
        echo json_encode([]);
    }
}
?>
