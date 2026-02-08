<?php
// get_room_price.php
require('../admin/config/config.php');

// Get the room_id from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$roomId = isset($data['room_id']) ? (int) $data['room_id'] : 0;

if ($roomId > 0) {
    // Query to fetch room price based on room_id
    $query = "SELECT room_price FROM rooms WHERE room_id = ?";
    
    if ($stmt = $mysqli->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
        $stmt->bind_result($roomPrice);
        $stmt->fetch();
        $stmt->close();

        if ($roomPrice) {
            echo json_encode(['success' => true, 'room_price' => $roomPrice]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Room not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Query preparation failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid room_id']);
}
?>
