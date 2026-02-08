<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

// Delete a single request
if (isset($_GET['deleteRequest'])) {
    $requestId = $_GET['deleteRequest']; // Get the ID of the request to be deleted

    // Prepare the DELETE query
    $adn = "DELETE FROM requests WHERE request_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $requestId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        alert("success", "Request Deleted Successfully!");
    } else {
        alert("error", "Failed to delete request. Please try again.");
    }
    $stmt->close();

    // Redirect back to the requests list page
    header("Location: requests.php");
    exit();
}

// Delete multiple selected requests
if (isset($_POST['delete_selected'])) {
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $query = "DELETE FROM requests WHERE request_id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $types = str_repeat('i', count($selected_ids));
            $stmt->bind_param($types, ...$selected_ids);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                alert("success", "Selected Requests Deleted Successfully!");
            } else {
                alert("error", "Failed to delete selected requests. Please try again.");
            }
        } else {
            alert("error", "Database error. Please try again.");
        }
    } else {
        alert("error", "No requests selected for deletion.");
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests</title>

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
                    <h5 class="titleFont mb-1">Requests</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Requests</a></li>
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
                        <button type="submit" class="btn btn-danger someText mt-3 mb-3" name="delete_selected">Delete Selected</button>
                    </div>

                </div>

<!-- Table -->
<div class="mt-5">
    <form action="multi_delete_request.php" method="POST">
        <table class="table table-striped table-hover table-responsive">
            <!-- Table Header -->
            <thead>
                <th scope="col" class="col-1"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col" class="col-1">No</th>
                <th scope="col" class="col-1">Request ID</th>
                <th scope="col" class="col-2">Reservation ID</th>
                <th scope="col" class="col-2">Products</th>
                <th scope="col" class="col-2">Services</th>
                <th scope="col" class="col-2">Total Price</th>
                <th scope="col" class="col-2">Status</th>
                <th scope="col">Operations</th>
            </thead>

            <!-- Table Body -->
            <tbody id="results">

                <!-- Fetching Details and Values -->
                <?php
                // Include the database connection file
                include('./config/config.php');

                // Fetch the requests data from the requests table
                $query = "SELECT * FROM requests ORDER BY request_id DESC";
                $stmt = $mysqli->prepare($query);
                $stmt->execute();
                $res = $stmt->get_result();
                $cnt = 1;

                while ($row = $res->fetch_object()) {
                    // Decode the JSON strings for products and services
                    $products = json_decode($row->products, true);  // Decoding JSON string to an array
                    $services = json_decode($row->services, true);  // Decoding JSON string to an array

                    // Format the products and services into readable format
                    $productList = [];
                    if (is_array($products)) {
                        foreach ($products as $product) {
                            $productList[] = $product;  // Just push the product name (you can also add the price here if needed)
                        }
                    }
                    $productString = implode(", ", $productList);  // Join the product names with commas

                    $serviceList = [];
                    if (is_array($services)) {
                        foreach ($services as $service) {
                            $serviceList[] = $service;  // Just push the service name (you can also add the price here if needed)
                        }
                    }
                    $serviceString = implode(", ", $serviceList);  // Join the service names with commas
                ?>

                    <tr>
                        <td>
                            <input type="checkbox" class="deleteCheckbox" style="vertical-align:middle;" name="selected_ids[]" value="<?php echo $row->request_id; ?>">
                        </td>
                        <td class="align-middle"><?php echo $cnt; ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($row->request_id); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($row->reservation_id); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($productString); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($serviceString); ?></td>
                        <td class="align-middle"><?php echo "₱" . number_format($row->total_price, 2); ?></td>
                        <td class="align-middle">
                            <span class="badge bg-<?php echo ($row->status == 'confirmed') ? 'success' : ($row->status == 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($row->status); ?>
                            </span>
                        </td>
                        <td class="d-flex align-middle">

                            <!-- Edit Button (Trigger Modal) -->
                            <a class="btn btn-success me-1 btn-sm" style="padding: 5px;" href="javascript:void(0);" 
                               data-bs-toggle="modal" data-bs-target="#updateStatusModal" 
                               data-request-id="<?php echo $row->request_id; ?>" 
                               data-current-status="<?php echo $row->status; ?>">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Delete Button -->
                            <a class="btn btn-danger btn-sm" style="padding: 5px;" href="javascript:void(0);" onClick="confirmDelete(<?php echo $row->request_id; ?>)">
                                <i class="bi bi-trash"></i>
                            </a>

                        </td>
                    </tr>

                <?php
                    $cnt++;
                }
                ?>

            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-end">
            <nav aria-label="...">
                <ul class="pagination" id="pagination">
                    <!-- Pagination Links will go here -->
                </ul>
            </nav>
        </div>
    </form>
</div>

<!-- Modal for Updating Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Request Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="update_request_status.php" method="POST" id="updateStatusForm">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <input type="hidden" id="request_id" name="request_id">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to populate the modal with the current data
document.addEventListener("DOMContentLoaded", function() {
    const updateButtons = document.querySelectorAll("[data-bs-toggle='modal']");

    updateButtons.forEach(button => {
        button.addEventListener("click", function() {
            const requestId = this.getAttribute("data-request-id");
            const currentStatus = this.getAttribute("data-current-status");

            // Set the value of the request_id field
            document.getElementById("request_id").value = requestId;

            // Set the selected value of the status dropdown based on the current status
            document.getElementById("status").value = currentStatus;
        });
    });
});

</script>

<script>
  // JavaScript to handle deletion confirmation
  function confirmDelete(requestId) {
    if (confirm("Are you sure you want to delete this request?")) {
      // Redirect to PHP file to delete the request
      window.location.href = 'requests.php?deleteRequest=' + requestId;
    }
  }
</script>





<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteButton = document.querySelector("button[name='delete_selected']");
    const form = document.querySelector("form");
    
    deleteButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission

        // Get all selected checkboxes
        const checkboxes = document.querySelectorAll(".deleteCheckbox:checked");

        if (checkboxes.length === 0) {
            alert("Please select at least one requests to delete.");
            return;
        }

        // Confirm before deleting
        if (confirm("Are you sure you want to delete the selected requests?")) {
            form.action = "multi_delete_requests.php"; // Set the form action
            form.submit(); // Submit the form
        }
    });

    // Select All Checkbox Logic
    document.getElementById("selectAllCheckbox").addEventListener("change", function () {
        const checkboxes = document.querySelectorAll(".deleteCheckbox");
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
});
</script>



            </div>
        </div>
    </div>


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

            let confirmDownload = confirm("Are you sure you want to generate a PDF for the requests?");
            if (confirmDownload) {
                window.location.href = "requests_generate_pdf.php"; // Redirect to PHP script for PDF generation
            }
        });
    </script>

</body>

</html>