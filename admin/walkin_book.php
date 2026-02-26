<?php
require('../admin/config/config.php');
require_once('../admin/inc/FileUploadHandler.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a random 6-digit reservation ID
    $reservation_id = mt_rand(100000, 999999);

    // Get form values
    $room_id = $_POST['room_id'];
    $client_name = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $client_contact = $_POST['client_contact'];
    $client_address = $_POST['client_address'];
    $client_id_type = $_POST['client_id_type'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $payment_method = $_POST['payment_method'];
    $total_price = $_POST['total_price'];
    $amount_paid = $_POST['amount_paid'];
    $balance = $_POST['balance'];
    $payment_remarks = $_POST['payment_remarks']; // New field for payment remarks

    // Define the upload directory
    $uploadDir = "../dist/img/";

    // Initialize file upload handler
    $uploadHandler = new FileUploadHandler($uploadDir);

    // Initialize file name variables
    $client_id_image = "";
    $client_gcash_ref_image = "";

    // Handle client ID image upload
    if (!empty($_FILES['client_id_image']['name'])) {
        $uploadResult = $uploadHandler->upload($_FILES['client_id_image']);

        if (!$uploadResult['success']) {
            echo json_encode(["success" => false, "message" => $uploadResult['message']]);
            exit;
        }

        $client_id_image = $uploadResult['filename'];
    }

    // Handle GCash reference image upload (if applicable)
    if ($payment_method == "gcash" && !empty($_FILES['client_gcash_ref_image']['name'])) {
        $uploadResult = $uploadHandler->upload($_FILES['client_gcash_ref_image']);

        if (!$uploadResult['success']) {
            echo json_encode(["success" => false, "message" => $uploadResult['message']]);
            exit;
        }

        $client_gcash_ref_image = $uploadResult['filename'];
    }

    // Additional GCash fields (if payment method is GCash)
    $client_gcash_name = isset($_POST['client_gcash_name']) ? $_POST['client_gcash_name'] : NULL;
    $client_gcash_number = isset($_POST['client_gcash_number']) ? $_POST['client_gcash_number'] : NULL;
    $client_gcash_ref = isset($_POST['client_gcash_ref']) ? $_POST['client_gcash_ref'] : NULL;

    // Prepare SQL statement
    $sql = "INSERT INTO walkin_reservation 
            (reservation_id, room_id, client_name, client_email, client_contact, client_address, client_id_type, check_in_date, check_out_date, client_id_image, 
             payment_method, client_gcash_name, client_gcash_number, client_gcash_ref, client_gcash_ref_image, 
             total_price, amount_paid, balance, payment_remarks, reservation_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param(
            "iisssssssssssssddss",
            $reservation_id,
            $room_id,
            $client_name,
            $client_email,
            $client_contact,
            $client_address,
            $client_id_type,
            $check_in_date,
            $check_out_date,
            $client_id_image,
            $payment_method,
            $client_gcash_name,
            $client_gcash_number,
            $client_gcash_ref,
            $client_gcash_ref_image,
            $total_price,
            $amount_paid,
            $balance,
            $payment_remarks
        );

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Booking successfully submitted!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Statement preparation failed: " . $mysqli->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}

$mysqli->close();
?>