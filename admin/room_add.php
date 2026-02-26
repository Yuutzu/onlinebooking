<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');
require('./inc/alert.php');

if (isset($_POST['save_category'])) {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    $room_name = $_POST['room_name'];
    $room_number = $_POST['room_number'];
    $room_description = $_POST['room_description'];
    $room_adult = $_POST['room_adult'];
    $room_child = $_POST['room_child'];
    $room_category = $_POST['room_category'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];

    $uploadHandler = new FileUploadHandler('dist/img/');
    $result = $uploadHandler->upload($_FILES['room_picture']);

    if (!$result['success']) {
        alert('error', $result['message']);
        exit;
    }

    $room_picture = $result['filename'];





    $duplicateQuery = "SELECT COUNT(*) AS count FROM rooms WHERE room_name = ? OR room_number = ?";
    $stmt1 = $mysqli->prepare($duplicateQuery);
    $stmt1->bind_param("ss", $room_name, $room_number);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Duplicate Found!
        alert("error", "Room already exist!");
    } else {
        $insertQuery = "INSERT INTO rooms(room_name, room_number, room_description, room_adult, room_child, room_category, room_price, room_status, room_picture) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt2 = $mysqli->prepare($insertQuery);
        //bind paramaters
        $rc = $stmt2->bind_param('sssiisdss', $room_name, $room_number, $room_description, $room_adult, $room_child, $room_category, $room_price, $room_status, $room_picture);
        $stmt2->execute();

        if ($stmt2) {
            alert('success', 'Room Created!');
        } else {
            alert('error', 'Please try again');
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Category</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        function confirmSave(event) {
            // Display confirmation dialog
            const userConfirmed = confirm("Do you want to save this information?");

            // If the user cancels, prevent the form submission
            if (!userConfirmed) {
                event.preventDefault();
            }
        }
    </script>

</head>

<body>



    <!-- HEADER -->
    <?php require('../admin/inc/side_header.php'); ?>

    <!-- Navigation -->
    <div class="col-lg-10 ms-auto">
        <?php require('./inc/nav.php'); ?>
    </div>

    <!-- Main Container -->
    <div class="container-fluid" id="main-content">

        <div class="row">
            <div class="col-lg-10 ms-auto">

                <!-- breadcrumbs -->
                <div class="mb-3 mt-4 ">
                    <h5 class="titleFont mb-1">Room</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none"
                                    style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none"
                                    style="color: #333333;">Room</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none"
                                    style="color: #333333;">Add Room</a></li>
                        </ol>
                    </nav>
                </div>



                <!-- FORM -->
                <div class="mt-5">

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="container container-text-header">
                                Add Room - Please Fill Out Neccessary Information.
                            </div>

                            <form action="room_add.php" method="POST" onsubmit="confirmSave(event)"
                                enctype="multipart/form-data">
                                <div class="mt-3 d-flex justify-content-between">

                                    <div class="col-lg-4">
                                        <div class="mb-2 me-2">
                                            <label class="form-label someText m-0">Room Name</label>
                                            <input type="text" name="room_name"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 me-2">
                                            <label class="form-label someText m-0">Room Number</label>
                                            <input type="text" name="room_number"
                                                class="form-control someText shadow-none" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="mb-2 me-2">
                                            <label class="form-label someText m-0">Adult</label>
                                            <input type="number" name="room_adult"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 me-2">
                                            <label class="form-label someText m-0">Child</label>
                                            <input type="number" name="room_child"
                                                class="form-control someText shadow-none" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Price</label>
                                            <input type="number" name="room_price"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Room Category</label>
                                            <select name="room_category" required
                                                class="form-control shadow-none someText">
                                                <option>Select Status</option>
                                                <!-- Fetching Details and Values -->
                                                <?php
                                                $ret = "SELECT * FROM  room_category";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();

                                                while ($row1 = $res->fetch_object()) {
                                                    ?>
                                                    <option value="<?php echo $row1->category_name; ?>">
                                                        <?php echo $row1->category_name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 mt-1">

                                    <label class="form-label someText">Room Status</label>
                                    <select name="room_status" required class="form-control shadow-none someText mb-2">
                                        <option>Select Status</option>
                                        <option value="Available">Available</option>
                                        <option value="Booked">Booked</option>
                                        <option value="Maintenance">Under Maintenance</option>
                                    </select>

                                    <label class="form-label someText">Room Category Description</label>
                                    <textarea type="text" class="form-control shadow-none someText"
                                        contenteditable="true" name="room_description" rows="4" id="desc"
                                        required></textarea>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label someText m-0">Picture</label>
                                    <input type="file" name="room_picture" class="form-control shadow-none someText">
                                </div>

                                <div class="mb-2 mt-3 d-grid">
                                    <button type="submit" name="save_category"
                                        class="btn btn-primary btnAddCategory someText">Save</button>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>

</html>