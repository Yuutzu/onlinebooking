<?php
session_start();
include('../admin/config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set("Asia/Manila"); // Set timezone
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $inquiry = trim($_POST['message']);
    
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $status = "Pending"; // Default status
    $remarks = ""; // Default remarks

    // Generate a unique 6-digit inquiry_id
    function generateInquiryID($mysqli) {
        do {
            $inquiry_id = rand(100000, 999999); // Generate a random 6-digit number
            $stmt = $mysqli->prepare("SELECT inquiry_id FROM inquiry WHERE inquiry_id = ?");
            $stmt->bind_param("i", $inquiry_id);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
        } while ($exists); // Ensure uniqueness

        return $inquiry_id;
    }

    $inquiry_id = generateInquiryID($mysqli);

    // Validate input
    if (empty($name) || empty($email) || empty($inquiry)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    try {
        $stmt = $mysqli->prepare("INSERT INTO inquiry (inquiry_id, date, time, name, email, inquiry, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $inquiry_id, $date, $time, $name, $email, $inquiry, $remarks, $status);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error. Please try again."]);
        }

        $stmt->close();
        $mysqli->close();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
