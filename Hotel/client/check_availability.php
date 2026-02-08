<?php
include('../admin/config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $adult = intval($_POST['adult']);
    $child = intval($_POST['child']);

    // Query to get available rooms that match capacity
    $sql = "SELECT COUNT(*) AS room_count FROM rooms r
            WHERE r.room_status = 'Available' 
            AND r.room_adult >= ? 
            AND r.room_child >= ?
            AND r.room_id NOT IN (
                SELECT room_id FROM reservations 
                WHERE reservation_status = 'confirmed'
                AND (
                    (check_in BETWEEN ? AND ?) OR (check_out BETWEEN ? AND ?) OR 
                    (check_in <= ? AND check_out >= ?)
                )
            )";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iissssss", $adult, $child, $check_in, $check_out, $check_in, $check_out, $check_in, $check_out);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['room_count'] > 0) {
        echo json_encode(["success" => true, "room_count" => $row['room_count']]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
