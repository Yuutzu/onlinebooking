<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_POST['save_category'])) {

    $room_id = $_GET['room_id'];
    $room_name = $_POST['room_name'];
    $room_number = $_POST['room_number'];
    $room_description = $_POST['room_description'];
    $room_adult = $_POST['room_adult'];
    $room_child = $_POST['room_child'];
    $room_category = $_POST['room_category'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];
    $room_picture  = $_FILES["room_picture"]["name"];
    move_uploaded_file($_FILES["room_picture"]["tmp_name"], "dist/img/" . $_FILES["room_picture"]["name"]);

    // Prepare the update query
    $query = "UPDATE rooms SET room_name = ?, room_number = ?, room_description = ?, room_adult = ?, room_child = ?, room_category = ?, room_price = ?, room_status = ?, room_picture = ? WHERE room_id = ?";
    $stmt = $mysqli->prepare($query);

    // Bind the parameters to the statement
    $rc = $stmt->bind_param('sssiisdssi', $room_name, $room_number, $room_description, $room_adult, $room_child, $room_category, $room_price, $room_status, $room_picture, $room_id);

    // Execute the statement
    $stmt->execute();

    if ($stmt) {
        alert('success', 'Room Updated Successfully!');
    } else {
        alert('error', 'Please try again');
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
            const userConfirmed = confirm("Do you want to update this information?");

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

    <!-- Fetching Details and Values -->
    <?php
    $room_id = $_GET['room_id'];
    $ret = "SELECT * FROM  rooms WHERE room_id = '$room_id'"; //sql tandaan mabuti
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();

    while ($row = $res->fetch_object()) {

    ?>

        <!-- Main Container -->
        <div class="container-fluid" id="main-content">

            <div class="row">
                <div class="col-lg-10 ms-auto">

                    <!-- breadcrumbs -->
                    <div class="mb-3 mt-4 ">
                        <h5 class="titleFont mb-1">Room</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Room</a></li>
                                <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Update Room</a></li>
                            </ol>
                        </nav>
                    </div>



                    <!-- FORM -->
                    <div class="mt-5">

                        <div class="row">
                            <div class="col-lg-7">
                                <div class="container container-text-header">
                                    Update Room - Please Fill Out Neccessary Information.
                                </div>

                                <form action="room_update.php?room_id=<?php echo $row->room_id; ?>" method="POST" onsubmit="confirmSave(event)" enctype="multipart/form-data">
                                    <div class="mt-3 d-flex justify-content-between">

                                        <div class="col-lg-4">
                                            <div class="mb-2 me-2">
                                                <label class="form-label someText m-0">Room Name</label>
                                                <input type="text" name="room_name" class="form-control someText shadow-none" value="<?php echo $row->room_name; ?>" required>
                                            </div>

                                            <div class="mb-2 me-2">
                                                <label class="form-label someText m-0">Room Number</label>
                                                <input type="text" name="room_number" class="form-control someText shadow-none" value="<?php echo $row->room_number; ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="mb-2 me-2">
                                                <label class="form-label someText m-0">Adult</label>
                                                <input type="number" name="room_adult" class="form-control someText shadow-none" value="<?php echo $row->room_adult; ?>" required>
                                            </div>

                                            <div class="mb-2 me-2">
                                                <label class="form-label someText m-0">Child</label>
                                                <input type="number" name="room_child" class="form-control someText shadow-none" value="<?php echo $row->room_child; ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Price</label>
                                                <input type="number" name="room_price" class="form-control someText shadow-none" value="<?php echo $row->room_price; ?>" required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Room Category</label>
                                                <select name="room_category" required class="form-control shadow-none someText">
                                                    <option disabled>Select Category</option>
                                                    <!-- Fetching Details and Values -->
                                                    <?php
                                                    $ret = "SELECT * FROM room_category";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->execute(); // Execute the query
                                                    $res = $stmt->get_result();

                                                    while ($row1 = $res->fetch_object()) {
                                                        // Check if the current category matches
                                                        $selected = ($row->room_category === $row1->category_name) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?php echo $row1->category_name; ?>" <?php echo $selected; ?>>
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
                                            <option value="" disabled>Select Status</option>
                                            <option value="Available" <?php echo ($row->room_status === 'Available') ? 'selected' : ''; ?>>Available</option>
                                            <option value="Booked" <?php echo ($row->room_status === 'Booked') ? 'selected' : ''; ?>>Booked</option>
                                            <option value="Maintenance" <?php echo ($row->room_status === 'Maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                        </select>

                                        <label class="form-label someText">Room Category Description</label>
                                        <textarea type="text" class="form-control shadow-none someText" contenteditable="true" name="room_description" rows="4" id="desc" required><?php echo $row->room_description ?></textarea>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label someText m-0">Picture</label>
                                        <!-- File Input -->
                                        <input type="file" name="room_picture" class="form-control shadow-none someText">

                                        <!-- Display Existing Picture -->
                                        <?php if (!empty($row->room_picture)) { ?>
                                            <div class="mt-2">
                                                <img src="./dist/img/<?php echo $row->room_picture; ?>" alt="Room Picture" style="width: 150px; height: auto;">
                                                <p class="someText mt-1">Current Picture: <?php echo $row->room_picture; ?></p>
                                            </div>
                                        <?php } ?>
                                    </div>


                                    <div class="mb-2 mt-3 d-grid">
                                        <button type="submit" name="save_category" class="btn btn-primary btnAddCategory someText">Save</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    <?php
                } ?>
                    </div>
                </div>
            </div>
        </div>

</body>

</html>