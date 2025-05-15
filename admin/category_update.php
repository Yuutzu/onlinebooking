<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');



if (isset($_POST['update_category'])) {

    $category_id = $_GET['category_id'];
    $cat_name = $_POST['category_name'];
    $cat_description = $_POST['category_description'];


    // Prepare the update query
    $query = "UPDATE room_category SET category_name = ?, category_description = ? WHERE category_id=?";
    $stmt = $mysqli->prepare($query);

    // Bind the parameters to the statement
    $rc = $stmt->bind_param('sss', $cat_name, $cat_description, $category_id);

    // Execute the statement
    $stmt->execute();


    if ($stmt) {
        alert("success", "Category Successfully Updated!");
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
    $category_id = $_GET['category_id'];
    $ret = "SELECT * FROM  room_category WHERE category_id = '$category_id'";
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
                        <h5 class="titleFont mb-1">Room Category Update</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                                <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Room Category</a></li>
                                <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Update Category</a></li>
                            </ol>
                        </nav>
                    </div>



                    <!-- FORM -->
                    <div class="mt-5">

                        <div class="row">
                            <div class="col-lg-7">
                                <div class="container container-text-header">
                                    Update Category - Please Fill Out Neccessary Information.
                                </div>

                                <form action="category_update.php?category_id=<?php echo $category_id; ?>" method="POST" onsubmit="confirmSave(event)">
                                    <div class="mt-3">
                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Room Category</label>
                                            <input type="text" name="category_name" class="form-control someText shadow-none" value="<?php echo $row->category_name; ?>" required>
                                        </div>

                                        <div class="mb-2 mt-3">
                                            <label class="form-label someText">Room Category Description</label>
                                            <textarea type="text" class="form-control shadow-none someText" contenteditable="true" name="category_description" rows="4" id="desc" required><?php echo $row->category_description; ?></textarea>
                                        </div>
                                        <div class="mb-2 mt-3 d-grid">
                                            <button type="submit" name="update_category" class="btn btn-primary btnAddCategory someText">Update</button>
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