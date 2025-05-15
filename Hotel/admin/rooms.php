<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');



if (isset($_GET['deleteCategory'])) {
    $id = $_GET['deleteCategory'];
    $adn = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
        alert("success", "Room Deleted Successfully!");
    } else {
        alert("error", "Please Try Again");
    }
}

if (isset($_POST['delete_selected'])) {
    // Decode selected category IDs from the form input
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $query = "DELETE FROM rooms WHERE room_id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                alert("success", "Category Deleted Successfully!");
            } else {
                alert("error", "Please try again 1.");
            }
        } else {
            alert("error", "Please try again 2.");
        }
    } else {
        alert("error", "No Selected Item!");
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
        function confirmDelete(url) {
            if (confirm("Are you sure you want to delete this category?")) {
                window.location.href = url; // Redirect if confirmed
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
                    <h5 class="titleFont mb-1">Rooms</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Room </a></li>
                        </ol>
                    </nav>
                </div>

<!-- Add and Search -->
<div class="mb-3 mt-5 d-flex align-items-end justify-content-between">
    <div style="width: 50%;">
        <label class="form-label someText m-0">Search: &nbsp;</label>
        <input type="text" name="search" id="search" class="form-control shadow-none w-50 someText">
    </div>

    <div>
        <a class="btn btn-primary someText btnAddCategory" href="room_add.php">+ Create</a>
        <button type="button" class="btn btn-danger someText mt-3 mb-3" id="deleteSelectedBtn">Delete Selected</button>
    </div>
</div>

<!-- Table -->
<div class="mt-5">
    <form action="multi_delete_room.php" method="POST" id="deleteForm">
        <table class="table table-striped table-hover table-responsive">
            <thead>
                <th scope="col"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col">No</th>
                <th scope="col">ID</th>
                <th scope="col">Room</th>
                <th scope="col">Number</th>
                <th scope="col" class="col-2">Room Description</th>
                <th scope="col">Category</th>
                <th scope="col">Adult</th>
                <th scope="col">Child</th>
                <th scope="col">Status</th>
                <th scope="col">Price</th>
                <th scope="col">Picture</th>
                <th scope="col">Operations</th>
            </thead>
            <tbody id="results">
                <!-- Data will be inserted here by AJAX -->
            </tbody>
        </table>
    </form>
    
    <!-- Pagination -->
    <nav>
        <ul class="pagination" id="pagination">
            <!-- Pagination links will be inserted here by AJAX -->
        </ul>
    </nav>
</div>



<script>
    document.getElementById('selectAllCheckbox').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.deleteCheckbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('deleteSelectedBtn').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('.deleteCheckbox:checked');
        if (checkboxes.length === 0) {
            alert("Please select at least one room to delete.");
        } else {
            if (confirm("Are you sure you want to delete the selected rooms? This action cannot be undone.")) {
                document.getElementById('deleteForm').submit();
            }
        }
    });
</script>



            </div>
        </div>
    </div>

    <!-- Importing AJAX for Search -->
    <?php require('./ajax/rooms_ajax.php'); ?>

    <script>
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.deleteCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>

</body>

</html>