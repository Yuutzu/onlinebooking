<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_GET['deleteClient'])) {
    $id = $_GET['deleteClient'];
    $adn = "DELETE FROM clients WHERE id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
        alert("success", "Client Deleted Successfully!");
    } else {
        alert("error", "Please Try Again");
    }
}

if (isset($_POST['delete_selected'])) {
    // Decode selected client IDs from the form input
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $query = "DELETE FROM clients WHERE id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                alert("success", "Selected Clients Deleted Successfully!");
            } else {
                alert("error", "Please try again.");
            }
        } else {
            alert("error", "Error in query execution.");
        }
    } else {
        alert("error", "No Selected Clients!");
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Clients</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



    <script>
        function confirmDelete(url) {
            if (confirm("Are you sure you want to delete this client?")) {
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
                    <h5 class="titleFont mb-1">Clients</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Clients </a></li>
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
                        <button type="submit" class="btn btn-danger someText mt-3 mb-3" name="delete_selected">Delete Selected</button>
                    </div>

                </div>

<!-- Table -->
<div class="mt-5">
    <form action="multi_delete_clients.php" method="POST">

        <table class="table table-striped table-hover table-responsive">
            <!-- Table Header -->
            <thead>
                <th scope="col"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col">No</th>
                <th scope="col">Client ID</th>
                <th scope="col">Client Name</th>
                <th scope="col">Presented ID</th>
                <th scope="col">ID Picture</th> <!-- New Column -->
                <th scope="col">Phone</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Picture</th>
                <th scope="col">Operations</th>
            </thead>

            <!-- Table Body -->
            <tbody id="results">
                <?php
                $ret = "SELECT id, client_id, client_name, client_presented_id, client_id_picture, client_phone, client_email, role, client_status, client_picture FROM clients WHERE role = 'User'";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                $cnt = 1;

                while ($row = $res->fetch_object()) {
                    // Define status color
                    $statusColor = "";
                    if ($row->client_status == "Activated") {
                        $statusColor = "badge bg-success"; // Green
                    } elseif ($row->client_status == "Pending") {
                        $statusColor = "badge bg-warning text-dark"; // Yellow
                    } elseif ($row->client_status == "Blocked") {
                        $statusColor = "badge bg-danger"; // Red
                    }
                ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="deleteCheckbox" style="vertical-align:middle;" name="selected_ids[]" value="<?php echo $row->id; ?>">
                        </td>
                        <td scope="row" class="align-middle" style="vertical-align:middle;"> <?php echo $cnt; ?> </td>
                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->client_id; ?> </td>
                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->client_name; ?> </td>
                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->client_presented_id; ?> </td>

                        <!-- New Column: Client ID Picture -->
                        <td class="align-middle" style="vertical-align:middle;">
                            <a href="./dist/img/<?php echo $row->client_id_picture; ?>" target="_blank" style="text-decoration: underline; color: blue;">
                                View
                            </a>
                        </td>

                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->client_phone; ?> </td>
                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->client_email; ?> </td>
                        <td class="align-middle" style="vertical-align:middle;"> <?php echo $row->role; ?> </td>
                        
                        <!-- Status with color -->
                        <td class="align-middle" style="vertical-align:middle;">
                            <span class="<?php echo $statusColor; ?>" style="padding: 5px; border-radius: 5px;">
                                <?php echo $row->client_status; ?>
                            </span>
                        </td>

                        <td class="align-middle" style="vertical-align:middle;">
                            <img src="./dist/img/<?php echo $row->client_picture ?>" style="width: 100px; border-radius: 5px;">
                        </td>
                        <td class="d-flex align-middle" style="vertical-align:middle;">
                            <a class="btn btn-success me-1 btn-sm someText open-modal" style="padding: 5px;" href="clients.php?id=<?php echo $row->id ?>">
                                <i class="bi bi-pencil-square iicon2"></i>
                            </a>
                            <a class='btn btn-danger btn-sm someText' style='padding: 5px;' href='#' onClick='confirmDelete("clients.php?deleteClient=<?php echo $row->id; ?>")'>
                                <i class='bi bi-trash iicon2'></i>
                            </a>
                        </td>
                    </tr>
                <?php $cnt++; } ?>
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



<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteButton = document.querySelector("button[name='delete_selected']");
    const form = document.querySelector("form");
    
    deleteButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission

        // Get all selected checkboxes
        const checkboxes = document.querySelectorAll(".deleteCheckbox:checked");

        if (checkboxes.length === 0) {
            alert("Please select at least one client to delete.");
            return;
        }

        // Confirm before deleting
        if (confirm("Are you sure you want to delete the selected clients?")) {
            form.action = "multi_delete_clients.php"; // Set the form action
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

    <!-- Importing AJAX for Search -->
    <?php require('./ajax/clients_ajax.php'); ?>

    <script>
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.deleteCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>



<!-- ✅ Update Client Modal -->
<div class="modal fade" id="updateClientModal" tabindex="-1" aria-labelledby="updateClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateClientModalLabel">Update Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_client.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="client_id" id="client_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name" id="client_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Presented ID</label>
                        <input type="text" class="form-control" name="client_presented_id" id="client_presented_id" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="client_phone" id="client_phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="client_email" id="client_email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="client_status" id="client_status">
                            <option value="Pending">Pending</option>
                            <option value="Activated">Activated</option>
                            <option value="Blocked">Blocked</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="client_picture">
                        <input type="hidden" name="existing_image" id="existing_image">
                        <img id="client_image_preview" src="" style="width: 100px; border-radius: 5px; margin-top: 5px;">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ JavaScript to Auto-Open Modal if 'id' Exists -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const clientId = urlParams.get('id');

    if (clientId) {
        fetch(`get_client.php?id=${clientId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('client_id').value = data.id;
                document.getElementById('client_name').value = data.client_name;
                document.getElementById('client_presented_id').value = data.client_presented_id;
                document.getElementById('client_phone').value = data.client_phone;
                document.getElementById('client_email').value = data.client_email;
                document.getElementById('client_status').value = data.client_status;
                document.getElementById('existing_image').value = data.client_picture;
                document.getElementById('client_image_preview').src = "./dist/img/" + data.client_picture;
                
                // Open modal
                var updateModal = new bootstrap.Modal(document.getElementById('updateClientModal'));
                updateModal.show();
            });
    }
});
</script>

</body>

</html>