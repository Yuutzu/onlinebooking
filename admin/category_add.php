<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_POST['save_category'])) {

    // Generate Category Number
    $length = 4;
    $_Number = substr(str_shuffle('0123A4567B89ABC'), 1, $length);

    $category_id = "CAT-$_Number";
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    $duplicateQuery = "SELECT COUNT(*) AS count FROM room_category WHERE category_name = ?";
    $stmt1 = $mysqli->prepare($duplicateQuery);
    $stmt1->bind_param("s", $category_name);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Duplicate Found!
        alert("error", "Category already exist!");
    } else {
        $insertQuery = "INSERT INTO room_category(category_id, category_name, category_description) VALUES (?,?,?)";
        $stmt2 = $mysqli->prepare($insertQuery);
        //bind paramaters
        $rc = $stmt2->bind_param('sss', $category_id, $category_name, $category_description);
        $stmt2->execute();

        if ($stmt2) {
            alert('success', 'Category Created!');
        } else {
            alert('error', 'Please try again');
        }
    }
}

if (isset($_POST['update_category'])) {
    $id = $_POST['edit_category_id'];
    $cat_name = $_POST['edit_category_name'];
    $cat_description = $_POST['edit_category_description'];

    // Prepare the update query
    $query = "UPDATE room_category SET category_name = ?, category_description = ? WHERE category_id=?";
    $stmt = $mysqli->prepare($query);

    // Bind the parameters to the statement
    $rc = $stmt->bind_param('sss', $cat_name, $cat_description, $id);

    // Execute the statement
    $stmt->execute();


    if ($stmt) {
        echo '
                   <script>
                        alert("Room Category Successfully Updated.");
                    </script>
                ';
    } else {
        echo '
                   <script>
                        alert("Please Try Again.");
                    </script>
                ';
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
                    <h5 class="titleFont mb-1">Room Category</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Room Category</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Add Category</a></li>
                        </ol>
                    </nav>
                </div>



                <!-- FORM -->
                <div class="mt-5">

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="container container-text-header">
                                Add Category - Please Fill Out Neccessary Information.
                            </div>

                            <form action="category_add.php" method="POST" onsubmit="confirmSave(event)">
                                <div class="mt-3">
                                    <div class="mb-2">
                                        <label class="form-label someText m-0">Room Category</label>
                                        <input type="text" name="category_name" class="form-control someText shadow-none" required>
                                    </div>

                                    <div class="mb-2 mt-3">
                                        <label class="form-label someText">Room Category Description</label>
                                        <textarea type="text" class="form-control shadow-none someText" contenteditable="true" name="category_description" rows="4" id="desc" required></textarea>
                                    </div>

                                    <div class="mb-2 mt-3 d-grid">
                                        <button type="submit" name="save_category" class="btn btn-primary btnAddCategory someText">Save</button>
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