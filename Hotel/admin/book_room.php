<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_POST['book_room'])) {
    $client_id = $_SESSION['client_id']; // Assuming client_id is stored in session
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $payment_method = $_POST['payment_method'];
    $total_price = $_POST['total_price'];
    $reservation_status = 'pending';
    
    $gcash_name = $gcash_number = $gcash_ref = $gcash_screenshot = NULL;
    if ($payment_method == 'Gcash') {
        $gcash_name = $_POST['gcash_name'];
        $gcash_number = $_POST['gcash_number'];
        $gcash_ref = $_POST['gcash_ref'];
        $gcash_screenshot = $_POST['gcash_screenshot'];
    }

    $query = "INSERT INTO reservations (client_id, room_id, check_in, check_out, payment_method, gcash_name, gcash_number, gcash_ref, gcash_screenshot, total_price, reservation_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iissssssdds', $client_id, $room_id, $check_in, $check_out, $payment_method, $gcash_name, $gcash_number, $gcash_ref, $gcash_screenshot, $total_price, $reservation_status);
    $stmt->execute();

    if ($stmt) {
        alert('success', 'Room successfully booked!');
    } else {
        alert('error', 'Booking failed. Please try again.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room</title>
    <?php require('./inc/links.php'); ?>
</head>
<body>
    <?php require('../admin/inc/side_header.php'); ?>
    <div class="col-lg-10 ms-auto">
        <?php require('./inc/nav.php'); ?>
    </div>
    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto">
                <div class="mb-3 mt-4">
                    <h5 class="titleFont mb-1">Book a Room</h5>
                </div>
                <div class="mt-5">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="container container-text-header">
                                Please Fill Out the Booking Information.
                            </div>
                            <form action="book_room.php" method="POST">
                                <div class="mt-3">
                                    <div class="mb-2">
                                        <label class="form-label">Select Room</label>
                                        <select name="room_id" class="form-control" required>
                                            <?php
                                            $roomsQuery = "SELECT room_id, room_name FROM rooms WHERE room_status = 'available'";
                                            $roomsResult = $mysqli->query($roomsQuery);
                                            while ($room = $roomsResult->fetch_assoc()) {
                                                echo "<option value='{$room['room_id']}'>{$room['room_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Check-in Date</label>
                                        <input type="date" name="check_in" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Check-out Date</label>
                                        <input type="date" name="check_out" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Payment Method</label>
                                        <select name="payment_method" class="form-control" id="payment_method" required>
                                            <option value="Cash">Cash</option>
                                            <option value="Gcash">Gcash</option>
                                        </select>
                                    </div>
                                    <div id="gcash_details" style="display: none;">
                                        <div class="mb-2">
                                            <label class="form-label">Gcash Name</label>
                                            <input type="text" name="gcash_name" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Gcash Number</label>
                                            <input type="text" name="gcash_number" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Gcash Reference No.</label>
                                            <input type="text" name="gcash_ref" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Upload Gcash Screenshot</label>
                                            <input type="file" name="gcash_screenshot" class="form-control">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Total Price</label>
                                        <input type="number" name="total_price" class="form-control" required>
                                    </div>
                                    <div class="mb-2 mt-3 d-grid">
                                        <button type="submit" name="book_room" class="btn btn-primary">Book Now</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("payment_method").addEventListener("change", function() {
            document.getElementById("gcash_details").style.display = this.value === "Gcash" ? "block" : "none";
        });
    </script>
</body>
</html>
