<?php
/**
 * Email Availability Check Endpoint
 * Used for real-time validation during registration
 */

session_start();
include('../admin/config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$email = trim($_POST['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Check if email exists in database
$check_query = "SELECT id FROM clients WHERE client_email = ?";
$stmt = $mysqli->prepare($check_query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

$exists = $result->num_rows > 0;

echo json_encode(['exists' => $exists]);
$stmt->close();
?>
