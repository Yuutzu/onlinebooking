<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

$room_id = $_GET['room_id'];
$client_id = $_SESSION['client_id'];

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $gcash_name = $_POST['gcash_name'];
    $gcash_number = $_POST['gcash_number'];
    $gcash_ref = $_POST['gcash_ref'];
    
    $payment_screenshot = $_FILES["gcash_screenshot"]["name"];
    $target_dir = "../admin/dist/img/";
    $target_file = $target_dir . basename($payment_screenshot);
    move_uploaded_file($_FILES["gcash_screenshot"]["tmp_name"], $target_file);

    // Calculate Price
    $stmt = $mysqli->prepare("SELECT room_price FROM rooms WHERE room_id = ? AND room_status = 'Available'");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        alert("error", "Room is not available!");
        exit;
    }

    $room = $result->fetch_assoc();
    $price_per_night = $room['room_price'];

    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $interval = $date1->diff($date2);

    $total_price = $price_per_night * $interval->days;

    // Insert reservation
    $stmt = $mysqli->prepare("INSERT INTO reservations (client_id, room_id, check_in, check_out, total_price, gcash_name, gcash_number, gcash_ref, gcash_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissdssss', $client_id, $room_id, $check_in, $check_out, $total_price, $gcash_name, $gcash_number, $gcash_ref, $payment_screenshot);
    if ($stmt->execute()) {
        // Update room status
        $stmt = $mysqli->prepare("UPDATE rooms SET room_status = 'Booked' WHERE room_id = ?");
        $stmt->bind_param('i', $room_id);
        $stmt->execute();

        // Fetch client's email
        $stmt = $mysqli->prepare("SELECT client_name, client_email FROM clients WHERE id = ?");
        $stmt->bind_param('i', $client_id);
        $stmt->execute();
        $clientResult = $stmt->get_result();
        $client = $clientResult->fetch_assoc();

        $client_name = $client['client_name'];
        $client_email = $client['client_email'];

        // Fetch hotel details from site settings
        $siteQuery = "SELECT site_name, site_email FROM site_settings LIMIT 1";
        $siteResult = $mysqli->query($siteQuery);
        $siteSettings = $siteResult->fetch_assoc();
        
        $hotel_name = $siteSettings['site_name'] ?? 'Our Hotel';
        $hotel_email = $siteSettings['site_email'] ?? 'contact@ourhotel.com';

        // Send email confirmation
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
            $mail->Subject = "Reservation Confirmation - $hotel_name";
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
                    h2 { color: #5bc0de; }
                    p { font-size: 16px; line-height: 1.5; }
                    .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
                    .highlight { font-weight: bold; color: #5bc0de; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Dear $client_name,</h2>
                    <p>Your reservation at <b>$hotel_name</b> has been successfully placed. Below are your reservation details:</p>
                    <p><b>Reservation Details:</b></p>
                    <p>Check-in Date: <b>$check_in</b></p>
                    <p>Check-out Date: <b>$check_out</b></p>
                    <p>Total Price: <b>₱$total_price</b></p>
                    <p>Your payment has been received and is being processed. We look forward to hosting you at our hotel.</p>
                    <br>
                    <p><b>Terms and Conditions:</b></p>
                    <p>1. Cancellations must be made at least 48 hours before check-in for a full refund.</p>
                    <p>2. Guests are responsible for any damages to the room during their stay.</p>
                    <p>3. Check-in time is from 2:00 PM, and check-out time is by 12:00 PM.</p>
                    <p>4. No smoking or pets are allowed in the rooms.</p>
                    <p>5. The hotel reserves the right to refuse service to any guest violating hotel policies.</p>
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
            alert("error", "Booking Placed, but email failed to send!");
            exit;
        }

        alert("success", "Booking Placed and Confirmation Email Sent!");
        header("location: index.php");
        exit;
    } else {
        alert("error", "Reservation Failed!");
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?></title>
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png" href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>
    <?php require('./inc/links.php'); ?>
</head>
<body>
    <?php require('./inc/nav.php'); ?>

    <?php
    $stmt = $mysqli->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row1 = $res->fetch_object()) {
        $half_price = $row1->room_price / 2;
    ?>

    <div class="container-fluid">
        <div class="row">
            <div class="container mt-5 mb-5 m-auto">
                <div class="col-lg-12 m-auto d-flex justify-content-center mt-5">
                    <div class="col-4 mt-5">
                        <div class="roomContainer">
                            <img src="../admin/dist/img/<?php echo $row1->room_picture ?>" style="object-fit: cover; width: 100%; height: 100%;">
                        </div>
                    </div>
                    <div class="col-4 p-4 mt-5">
                        <p class="miniTitle"><?php echo $row1->room_category ?></p>
                        <h5 class="bigTitle mb-0" style="font-size: 30px;"> <?php echo $row1->room_name ?> </h5>
                        <hr class="mb-3">
                        <p class="someText">Note: Online booking payments are accepted via <strong>GCash</strong> only.</p>
                        <p class="someText">Total Price: <strong>PHP <?php echo number_format($row1->room_price, 2); ?></strong></p>
                        <p class="someText">Down Payment (50%): <strong>PHP <?php echo number_format($half_price, 2); ?></strong></p>
                        <form id="bookingForm" method="POST" enctype="multipart/form-data">
                            <div class="col-4 mt-2 d-grid">
                                <div class="mb-2 d-flex">
                                    <div class="me-3">
                                        <label class="form-label someText m-0">Check In Date</label>
                                        <input type="date" name="check_in" class="form-control shadow-none" required>
                                    </div>
                                    <div>
                                        <label class="form-label someText m-0">Check Out Date</label>
                                        <input type="date" name="check_out" class="form-control shadow-none" required>
                                    </div>
                                </div>
                                <div class="mb-2 d-grid mt-3">
                                    <button type="button" name="proceedBtn" id="proceedBtn" class="btn btn-primary btnAddCategory someText">Confirm Booking</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GCash Payment Modal -->
    <div class="modal fade" id="gcashModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GCash Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="../admin/dist/img/yawa (1).jpg" class="img-fluid mb-3" alt="GCash QR Code">
                    <p><strong>09519237937</strong></p>
                    <form id="paymentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="check_in">
                        <input type="hidden" name="check_out">
                        <div class="mb-2">
                            <input type="text" name="gcash_name" class="form-control" placeholder="Your Gcash Name" required>
                        </div>
                        <div class="mb-2">
                            <input type="number" name="gcash_number" class="form-control" placeholder="Your Gcash Number" required>
                        </div>
                        <div class="mb-2">
                            <input type="number" name="gcash_ref" class="form-control" placeholder="Your Gcash Ref" required>
                        </div>
                        <div class="mb-3">
                            <input type="file" name="gcash_screenshot" class="form-control" placeholder="Your Gcash Screenshot" required>
                        </div>
                        <button type="submit" name="confirm_payment" class="btn btn-success">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('proceedBtn').addEventListener('click', function() {
            const checkInDate = document.querySelector('input[name="check_in"]').value;
            const checkOutDate = document.querySelector('input[name="check_out"]').value;
            if (!checkInDate || !checkOutDate) {
                alert('Please select both check-in and check-out dates.');
                return;
            }

            const date1 = new Date(checkInDate);
            const date2 = new Date(checkOutDate);
            const diffTime = date2 - date1;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays <= 0) {
                alert('Invalid date range!');
                return;
            }

            const roomPrice = <?php echo $row1->room_price; ?>;
            const totalPrice = diffDays * roomPrice;
            const halfPrice = totalPrice / 2;

            if (confirm(`Reservation Summary:\n\nTotal Nights: ${diffDays}\nTotal Price: PHP ${totalPrice.toFixed(2)}\nDown Payment (50%): PHP ${halfPrice.toFixed(2)}\n\nDo you want to proceed?`)) {
                document.querySelector('#paymentForm input[name="check_in"]').value = checkInDate;
                document.querySelector('#paymentForm input[name="check_out"]').value = checkOutDate;
                const gcashModal = new bootstrap.Modal(document.getElementById('gcashModal'));
                gcashModal.show();
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    const checkInInput = document.querySelector("input[name='check_in']");
    const checkOutInput = document.querySelector("input[name='check_out']");

    // Function to disable past dates for Check-in Date
    function disablePastCheckInDate() {
        let today = new Date();
        let formattedToday = today.toISOString().split('T')[0]; // Format YYYY-MM-DD

        checkInInput.setAttribute("min", formattedToday); // Set min attribute
    }

    // Function to disable past dates for Check-out Date based on Check-in Date
    function disablePastCheckOutDate() {
        let checkInDate = checkInInput.value;

        if (checkInDate) {
            checkOutInput.setAttribute("min", checkInDate); // Check-out cannot be before check-in
        } else {
            checkOutInput.removeAttribute("min"); // Reset if no check-in date selected
        }
    }

    // Run when the page loads
    disablePastCheckInDate();

    // Run when check-in date is changed
    checkInInput.addEventListener("change", disablePastCheckOutDate);
});

    </script>

    <?php } ?>
</body>
</html>