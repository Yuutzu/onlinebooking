<?php
require_once './config/config.php';

$query = "SELECT category_id, category_name FROM room_category";
$result = $mysqli->query($query);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

header('Content-Type: application/json');
echo json_encode($categories);
?>
