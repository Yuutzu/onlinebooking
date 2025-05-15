<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');



if (isset($_GET['deleteCategory'])) {
    $id = $_GET['deleteCategory'];
    $adn = "DELETE FROM room_category WHERE category_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
        alert("success", "Category Deleted Successfully!");
    } else {
        alert("error", "Please Try Again");
    }
}

if (isset($_POST['delete_selected'])) {
    // Decode selected category IDs from the form input
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $query = "DELETE FROM room_category WHERE category_id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $stmt->bind_param(str_repeat('s', count($selected_ids)), ...$selected_ids);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                alert("success", "Category Deleted Successfully!");
            } else {
                alert("error", "Please try again.");
            }
        } else {
            alert("error", "Please try again.");
        }
    } else {
        alert("error", "Please try again.");
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



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
                    <h5 class="titleFont mb-1">Payments</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Payments</a></li>
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
                        <a class="btn btn-primary someText btnAddCategory" href="#" id="downloadReservation">Download</a>
                    </div>

                </div>

                <!-- Table -->
                <div class="mt-5">
                    <form action="reservations.php" method="POST">
                        <table class="table table-striped table-hover table-responsive">
                            <!-- Table Header -->
                            <thead>
                                <th scope="col" class="col-1">#</th>
                                <th scope="col" class="col-2">Reservation ID</th>
                                <th scope="col" class="col-2">Client ID</th>
                                <th scope="col" class="col-2">Payment Method</th>
                                <th scope="col" class="col-2">Gcash Name</th>
                                <th scope="col" class="col-2">Gcash Number</th>
                                <th scope="col" class="col-2">Gcash Ref</th>
                                <th scope="col" class="col-2">Gcash Screenshot</th>
                                <th scope="col" class="col-2">Total Price</th>
                                <th scope="col" class="col-2">Type</th>
                                <th scope="col">Status</th>
                            </thead>

                            <!-- Table Body -->
                            <tbody id="results">
                                <!-- Fetching Details and Values -->
                                <?php
                                $ret = "SELECT reservation_id, client_id, payment_method, gcash_name, gcash_number, gcash_ref, gcash_screenshot, total_price, type, reservation_status, 'Online' AS source_type FROM reservations UNION ALL SELECT reservation_id, NULL AS client_id, payment_method, client_gcash_name AS gcash_name, client_gcash_number AS gcash_number, client_gcash_ref AS gcash_ref, client_gcash_ref_image AS gcash_screenshot, total_price, reservation_type AS type, reservation_status, 'Walk-in' AS source_type FROM walkin_reservation ORDER BY reservation_id DESC";
                                
                                $stmt = $mysqli->prepare($ret);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                $cnt = 1;

                                while ($row = $res->fetch_object()) {
                                ?>
                                    <tr>
                                        <td class="align-middle"><?php echo $cnt; ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->reservation_id); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->client_id ? $row->client_id : 'N/A'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->payment_method); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->gcash_name ? $row->gcash_name : 'N/A'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->gcash_number ? $row->gcash_number : 'N/A'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->gcash_ref ? $row->gcash_ref : 'N/A'); ?></td>
                                        <td class="align-middle">
                                            <?php if (!empty($row->gcash_screenshot)) { ?>
                                                <a href="<?php echo '../admin/dist/img/' . htmlspecialchars($row->gcash_screenshot); ?>" target="_blank">View</a>
                                            <?php } else {
                                                echo "N/A";
                                            } ?>
                                        </td>
                                        <td class="align-middle">₱<?php echo number_format($row->total_price, 2); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row->source_type); ?></td>
                                        <td class="align-middle">
                                            <span class="badge bg-<?php echo ($row->reservation_status == 'confirmed') ? 'success' : ($row->reservation_status == 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo $row->reservation_status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php $cnt++;
                                } ?>
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end">
                            <nav aria-label="...">
                                <ul class="pagination" id="pagination">
                                    <!-- Pagination Here -->
                                </ul>
                            </nav>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Importing AJAX for Search -->
    <?php require('./ajax/payments_ajax.php'); ?>

    <script>
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.deleteCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>

    <script>
        document.getElementById("downloadReservation").addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default link behavior

            let tableRows = document.querySelectorAll("#results tr"); // Get table rows
            if (tableRows.length === 0) {
                alert("Error: No data available to generate a PDF.");
                return;
            }

            let confirmDownload = confirm("Are you sure you want to generate a PDF for the payments?");
            if (confirmDownload) {
                window.location.href = "payments_generate_pdf.php"; // Redirect to PHP script for PDF generation
            }
        });
    </script>

</body>

</html>