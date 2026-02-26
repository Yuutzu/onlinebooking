<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');
require('./inc/alert.php');

if (isset($_POST['update_service'])) {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    $service_id = $_GET['service_id'];
    $service_name = $_POST['service_name'];
    $service_description = $_POST['service_description'];
    $service_price = $_POST['service_price']; // Get the price from the form
    $service_status = $_POST['service_status']; // Define service_status here

    // Check if an image has been uploaded
    $uploadHandler = new FileUploadHandler('dist/img/');
    if (isset($_FILES['service_pic']) && $_FILES['service_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['service_pic']);
        if (!$result['success']) {
            alert('error', $result['message']);
            exit;
        }
        $service_pic = $result['filename'];
        $update_image_query = ", service_picture = ?";
        $bind_param_types = 'ssssss'; // The type definition string when an image is uploaded
        $bind_param_values = [$service_name, $service_description, $service_price, $service_pic, $service_status, $service_id];
    } else {
        // If no image uploaded, leave the image part empty
        $service_pic = null;
        $update_image_query = "";
        $bind_param_types = 'sssss'; // The type definition string when no image is uploaded
        $bind_param_values = [$service_name, $service_description, $service_price, $service_status, $service_id];
    }

    // Prepare the update query dynamically based on whether an image is uploaded or not
    $query = "UPDATE room_services SET service_name = ?, service_description = ?, service_price = ? " . $update_image_query . ", service_status = ? WHERE service_id = ?";

    $stmt = $mysqli->prepare($query);

    // Bind parameters dynamically
    if ($service_pic) {
        $stmt->bind_param($bind_param_types, ...$bind_param_values);
    } else {
        $stmt->bind_param($bind_param_types, ...$bind_param_values);
    }

    // Execute the statement
    $stmt->execute();

    if ($stmt) {
        alert("success", "Services Successfully Updated!");
    } else {
        alert("error", "Please Try Again.");
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
    $service_id = $_GET['service_id'];
    $ret = "SELECT * FROM  room_services WHERE service_id = '$service_id'";
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
                        <h5 class="titleFont mb-1">Room Services</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none"
                                        style="color: #333333;">Admin Dashboard</a></li>
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none"
                                        style="color: #333333;">Room Services</a></li>
                                <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none"
                                        style="color: #333333;">Update Services</a></li>
                            </ol>
                        </nav>
                    </div>



                    <!-- FORM -->
                    <div class="mt-5">

                        <div class="row">
                            <div class="col-lg-7">
                                <div class="container container-text-header">
                                    Update Services - Please Fill Out Neccessary Information.
                                </div>

                                <form action="services_update.php?service_id=<?php echo $row->service_id; ?>" method="POST"
                                    onsubmit="confirmSave(event)" enctype="multipart/form-data">
                                    <div class="mt-3">
                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Service</label>
                                            <input type="text" name="service_name" class="form-control someText shadow-none"
                                                required value="<?php echo $row->service_name; ?>">
                                        </div>

                                        <div class="mb-2 mt-3">
                                            <label class="form-label someText">Service Description</label>
                                            <textarea type="text" class="form-control shadow-none someText"
                                                contenteditable="true" name="service_description" rows="4" id="desc"
                                                required><?php echo $row->service_description; ?></textarea>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Price</label>
                                            <input type="number" name="service_price"
                                                class="form-control someText shadow-none" required
                                                value="<?php echo $row->service_price; ?>">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Picture</label>
                                            <input type="file" name="service_pic" class="form-control shadow-none someText"
                                                accept="image/*">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Service Status</label>
                                            <select name="service_status" required
                                                class="form-control shadow-none someText">
                                                <option value="Available" <?php echo ($row->service_status == 'Available') ? 'selected' : ''; ?>>Available</option>
                                                <option value="Unvailable" <?php echo ($row->service_status == 'Unvailable') ? 'selected' : ''; ?>>Unvailable</option>
                                            </select>
                                        </div>

                                        <div class="mb-2 mt-3 d-grid">
                                            <button type="submit" name="update_service"
                                                class="btn btn-primary btnAddCategory someText">Update</button>
                                        </div>
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