<?php
include('../admin/config/config.php'); // Include database connection

// Admin account details
$client_id = "Admin";
$client_name = "Admin";
$client_presented_id = "Admin";
$client_id_picture = "Admin";
$client_id_number = "Admin";
$client_phone = "Admin";
$client_email = "admin@gmail.com";
$client_password = password_hash("admin123", PASSWORD_DEFAULT); // Secure hashing
$client_status = "Activated";
$client_picture = "Admin";
$failed_attempts = 0;
$last_failed_attempt = "0000-00-00 00:00:00";
$role = "Admin";

// Prepare the SQL statement
$query = "INSERT INTO clients (client_id, client_name, client_presented_id, client_id_picture, client_id_number, client_phone, client_email, client_password, client_status, client_picture, failed_attempts, last_failed_attempt, role) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssssssssssiss", 
    $client_id, 
    $client_name, 
    $client_presented_id, 
    $client_id_picture, 
    $client_id_number, 
    $client_phone, 
    $client_email, 
    $client_password, 
    $client_status, 
    $client_picture, 
    $failed_attempts, 
    $last_failed_attempt, 
    $role
);

// Execute the statement
if ($stmt->execute()) {
    echo "✅ Admin account successfully inserted!";
} else {
    echo "❌ Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$mysqli->close();
?>
