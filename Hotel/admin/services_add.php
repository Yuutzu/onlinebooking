<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_POST['save_service'])) {

    // Generate Category Number
    $length = 4;
    $_Number = substr(str_shuffle('0123A4567B89ABC'), 1, $length);

    $service_id = "SER-$_Number";
    $service_name = $_POST['service_name'];
    $service_description = $_POST['service_description'];
    $service_pic  = $_FILES["service_pic"]["name"];
    move_uploaded_file($_FILES["service_pic"]["tmp_name"], "dist/img/" . $_FILES["service_pic"]["name"]);
    $service_status = $_POST['service_status'];

    $duplicateQuery = "SELECT COUNT(*) AS count FROM room_services WHERE service_name = ?";
    $stmt1 = $mysqli->prepare($duplicateQuery);
    $stmt1->bind_param("s", $service_name);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Duplicate Found!
        alert("error", "Service already exist!");
    } else {
        $insertQuery = "INSERT INTO room_services(service_id, service_name, service_description, service_picture, service_status) VALUES (?,?,?,?,?)";
        $stmt2 = $mysqli->prepare($insertQuery);
        //bind paramaters
        $rc = $stmt2->bind_param('sssss', $service_id, $service_name, $service_description, $service_pic, $service_status);
        $stmt2->execute();

        if ($stmt2) {
            alert('success', 'Service Created!');
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
    <title>Room Services</title>

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
                    <h5 class="titleFont mb-1">Room Services</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Room Services</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Add Services</a></li>
                        </ol>
                    </nav>
                </div>



                <!-- FORM -->
                <div class="mt-5">

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="container container-text-header">
                                Add Services - Please Fill Out Neccessary Information.
                            </div>

                            <form action="services_add.php" method="POST" onsubmit="confirmSave(event)" enctype="multipart/form-data">
                                <div class="mt-3">
                                    <div class="mb-2">
                                        <label class="form-label someText m-0">Service</label>
                                        <input type="text" name="service_name" class="form-control someText shadow-none" required>
                                    </div>

                                    <div class="mb-2 mt-3">
                                        <label class="form-label someText">Service Description</label>
                                        <textarea type="text" class="form-control shadow-none someText" contenteditable="true" name="service_description" rows="4" id="desc" required></textarea>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label someText m-0">Picture</label>
                                        <input type="file" name="service_pic" class="form-control shadow-none someText">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label someText m-0">Service Status</label>
                                        <select name="service_status" required class="form-control shadow-none someText">
                                            <option>Select Status</option>
                                            <option value="Available">Available</option>
                                            <option value="Not Available">Not Available</option>
                                        </select>
                                    </div>

                                    <div class="mb-2 mt-3 d-grid">
                                        <button type="submit" name="save_service" class="btn btn-primary btnAddCategory someText">Save</button>
                                    </div>
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