<?php
session_start();
include('../admin/config/config.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['items']) || empty($data['items'])) {
    echo json_encode(["status" => "error", "message" => "No items selected."]);
    exit;
}

if (!isset($data['totalPrice']) || !is_numeric($data['totalPrice'])) {
    echo json_encode(["status" => "error", "message" => "Invalid total price."]);
    exit;
}

// Check if special request exists
$specialRequest = isset($data['specialRequest']) ? trim($data['specialRequest']) : "";

// Sanitize and prepare data
$items = implode(", ", array_map('htmlspecialchars', explode(", ", $data['items'])));
$totalPrice = floatval($data['totalPrice']);

// Insert request into the database
$sql = "INSERT INTO tbl_requests (items, total_price, special_request) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sds", $items, $totalPrice, $specialRequest);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Your request has been successfully submitted!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
