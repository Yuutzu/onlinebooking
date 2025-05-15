<?php
session_start();
include('../admin/config/config.php');
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['reservation_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

$reservation_id = intval($data['reservation_id']);

// Fetch reservation details including client_id and room_id
$query = "SELECT r.room_id, r.check_in, r.check_out, r.total_price, r.type, c.client_name, c.client_email 
          FROM reservations r
          JOIN clients c ON r.client_id = c.id
          WHERE r.reservation_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();

if (!$reservation) {
    echo json_encode(["status" => "error", "message" => "Reservation not found."]);
    exit;
}

$room_id = $reservation['room_id'];
$client_name = $reservation['client_name'];
$client_email = $reservation['client_email'];
$check_in = $reservation['check_in'];
$check_out = $reservation['check_out'];
$total_price = number_format($reservation['total_price'], 2);
$type = ucfirst($reservation['type']);

// Fetch hotel details from site_settings
$siteQuery = "SELECT site_name, site_email FROM site_settings LIMIT 1";
$siteResult = $mysqli->query($siteQuery);
$siteSettings = $siteResult->fetch_assoc();

$hotel_name = $siteSettings['site_name'] ?? 'Our Hotel';
$hotel_email = $siteSettings['site_email'] ?? 'contact@ourhotel.com';

// Delete the reservation
$deleteStmt = $mysqli->prepare("DELETE FROM reservations WHERE reservation_id = ?");
$deleteStmt->bind_param('i', $reservation_id);

if ($deleteStmt->execute()) {
    // Update room status to 'Available'
    $updateRoomStmt = $mysqli->prepare("UPDATE rooms SET room_status = 'Available' WHERE room_id = ?");
    $updateRoomStmt->bind_param('i', $room_id);
    $updateRoomStmt->execute();

    // Send email notification
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luxehavenhotelph@gmail.com';
        $mail->Password = 'lvlx qagp ojak ymsu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($hotel_email, "$hotel_name Team");
        $mail->addAddress($client_email, $client_name);

        $mail->isHTML(true);
        $mail->Subject = "Reservation Cancellation Notice - $hotel_name";
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
                h2 { color: #d9534f; }
                p { font-size: 16px; line-height: 1.5; }
                .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
                .highlight { font-weight: bold; color: #d9534f; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Dear $client_name,</h2>
                <p>We regret to inform you that your reservation at <b>$hotel_name</b> has been <span class='highlight'>cancelled</span>.</p>
                <p><b>Reservation Details:</b></p>
                <p>Check-in Date: <b>$check_in</b></p>
                <p>Check-out Date: <b>$check_out</b></p>
                <p>Room Type: <b>$type</b></p>
                <p>Total Price: <b>₱$total_price</b></p>
                <p>If you have any concerns or wish to rebook, please do not hesitate to contact us at <a href='mailto:$hotel_email' style='color: #4a1c1d;'>$hotel_email</a>.</p>
                <br>
                <p>Best regards,</p>
                <p><b>$hotel_name Management</b></p>
                <br>
                <div class='footer'>***<i>This is an auto-generated email. DO NOT REPLY.</i>***</div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Reservation cancelled, but email failed to send."]);
        exit;
    }

    echo json_encode(["status" => "success", "message" => "Reservation cancelled successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete reservation."]);
}

exit;
?>
