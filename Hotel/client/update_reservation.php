<?php
$data = json_decode(file_get_contents("php://input"));
$reservation_id = $data->reservation_id;
$new_check_out_date = $data->new_check_out_date;
$new_total_price = $data->new_total_price;

// Assuming you already have a DB connection
$query = "UPDATE reservations SET check_out = ?, total_price = ? WHERE reservation_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$new_check_out_date, $new_total_price, $reservation_id]);

echo json_encode(['success' => true]);
?>
