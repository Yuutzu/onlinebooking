<?php
require_once './config/config.php';

$category_name = $_GET['category_name'];

$query = "SELECT room_id, room_name, room_description, room_price, room_picture FROM rooms WHERE room_category = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $category_name);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    // Append the correct path to the image filename
    $row['room_picture'] = "./dist/yawa//img/" . $row['room_picture'];
    $rooms[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rooms);
?>
