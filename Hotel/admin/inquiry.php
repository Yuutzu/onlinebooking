<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

// Delete a single inquiry
if (isset($_GET['deleteInquiry'])) {
    $id = $_GET['deleteInquiry'];
    $adn = "DELETE FROM inquiry WHERE inquiry_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        alert("success", "Inquiry Deleted Successfully!");
    } else {
        alert("error", "Failed to delete inquiry. Please try again.");
    }
    $stmt->close();
}

// Delete multiple selected inquiries
if (isset($_POST['delete_selected'])) {
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $query = "DELETE FROM inquiry WHERE inquiry_id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $types = str_repeat('i', count($selected_ids));
            $stmt->bind_param($types, ...$selected_ids);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                alert("success", "Selected Inquiries Deleted Successfully!");
            } else {
                alert("error", "Failed to delete selected inquiries. Please try again.");
            }
        } else {
            alert("error", "Database error. Please try again.");
        }
    } else {
        alert("error", "No inquiries selected for deletion.");
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



    <script>
        function confirmDelete(url) {
            if (confirm("Are you sure you want to delete this inquiry?")) {
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
                    <h5 class="titleFont mb-1">Inquiry</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Inquiry</a></li>
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
    <form action="multi_delete_inquiry.php" method="POST">
        <table class="table table-striped table-hover table-responsive">
            <thead>
                <th scope="col" class="col-1"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col" class="col-1">No</th>
                <th scope="col" class="col-1">ID</th>
                <th scope="col" class="col-2">Date</th>
                <th scope="col" class="col-2">Time</th>
                <th scope="col" class="col-2">Name</th>
                <th scope="col" class="col-2">Email</th>
                <th scope="col" class="col-2">Inquiry</th>
                <th scope="col" class="col-2">Remarks</th>
                <th scope="col" class="col-2">Status</th>
                <th scope="col">Operations</th>
            </thead>
            <tbody id="results">
                <?php
                $ret = "SELECT * FROM inquiry ORDER BY inquiry_id DESC";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                $cnt = 1;
                while ($row = $res->fetch_object()) {
                    // Determine Bootstrap badge class based on status
                    $badgeClass = "";
                    switch ($row->status) {
                        case "Pending":
                            $badgeClass = "bg-warning";
                            break;
                        case "Confirmed":
                            $badgeClass = "bg-success";
                            break;
                        case "Cancelled":
                            $badgeClass = "bg-danger";
                            break;
                        default:
                            $badgeClass = "bg-secondary";
                    }
                ?>
                <tr>
                    <td><input type="checkbox" class="deleteCheckbox" name="selected_ids[]" value="<?php echo $row->inquiry_id; ?>"></td>
                    <td class="align-middle"><?php echo $cnt; ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->inquiry_id); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->date); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->time); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->name); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->email); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($row->inquiry); ?></td>
                    <td class="align-middle"><span id="remarks_<?php echo $row->inquiry_id; ?>"><?php echo htmlspecialchars($row->remarks); ?></span></td>
                    <td class="align-middle">
                        <span class="badge <?php echo $badgeClass; ?>" id="status_<?php echo $row->inquiry_id; ?>">
                            <?php echo htmlspecialchars($row->status); ?>
                        </span>
                    </td>
                    <td class="d-flex align-middle">
                        <!-- Edit Button -->
                        <button type="button" class="btn btn-success me-1 btn-sm open-modal" data-bs-toggle="modal" data-bs-target="#updateModal" 
                            data-id="<?php echo $row->inquiry_id; ?>" data-remarks="<?php echo htmlspecialchars($row->remarks); ?>" 
                            data-status="<?php echo $row->status; ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <!-- Delete Button -->
                        <a class='btn btn-danger btn-sm someText' style='padding: 5px;' href='#'
                            onClick='confirmDelete("inquiry.php?deleteInquiry=<?php echo $row->inquiry_id; ?>")'>
                            <i class='bi bi-trash iicon2'></i>
                        </a>
                    </td>
                </tr>
                <?php $cnt++; } ?>
            </tbody>
        </table>
    </form>
</div>


<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Inquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <input type="hidden" id="inquiry_id" name="inquiry_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Modal Handling and AJAX Update -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Handle Edit Button Click
        document.querySelectorAll(".open-modal").forEach(button => {
            button.addEventListener("click", function() {
                let inquiryId = this.getAttribute("data-id");
                let remarks = this.getAttribute("data-remarks");
                let status = this.getAttribute("data-status");
                
                document.getElementById("inquiry_id").value = inquiryId;
                document.getElementById("remarks").value = remarks;
                document.getElementById("status").value = status;
            });
        });

        // Handle Save Changes Click with Confirmation Prompt
        document.getElementById("saveChanges").addEventListener("click", function() {
            if (confirm("Are you sure you want to update this inquiry?")) {
                let formData = new FormData(document.getElementById("updateForm"));

                fetch("update_inquiry.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message); // Show success message
                        location.reload();   // Refresh the page
                    } else {
                        alert(data.message); // Show error message
                    }
                })
                .catch(error => {
                    alert("An error occurred: " + error);
                });
            }
        });

    });
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
            alert("Please select at least one inquiry to delete.");
            return;
        }

        // Confirm before deleting
        if (confirm("Are you sure you want to delete the selected inquiries?")) {
            form.action = "multi_delete_inquiry.php"; // Set the form action
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

            let confirmDownload = confirm("Are you sure you want to generate a PDF for the inquiries?");
            if (confirmDownload) {
                window.location.href = "inquiry_generate_pdf.php"; // Redirect to PHP script for PDF generation
            }
        });
    </script>

</body>

</html>