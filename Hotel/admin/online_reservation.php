<?php
session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_GET['deleteReservation'])) {
    $id = $_GET['deleteReservation'];

    // Fetch the associated room_id before deleting the reservation
    $room_query = "SELECT room_id FROM reservations WHERE reservation_id = ?";
    $stmt_room = $mysqli->prepare($room_query);
    $stmt_room->bind_param('s', $id);
    $stmt_room->execute();
    $result = $stmt_room->get_result();
    $room = $result->fetch_assoc();
    $stmt_room->close();

    if ($room) {
        $room_id = $room['room_id'];

        // Delete the reservation
        $delete_query = "DELETE FROM reservations WHERE reservation_id = ?";
        $stmt = $mysqli->prepare($delete_query);
        $stmt->bind_param('s', $id);

        if ($stmt->execute()) {
            $stmt->close();

            // Update the room status to "Available"
            $update_room_query = "UPDATE rooms SET room_status = 'Available' WHERE room_id = ?";
            $stmt_update = $mysqli->prepare($update_room_query);
            $stmt_update->bind_param('s', $room_id);
            $stmt_update->execute();
            $stmt_update->close();

            alert("success", "Reservation Deleted Successfully! Room is now Available.");
        } else {
            alert("error", "Error deleting reservation. Please try again.");
        }
    } else {
        alert("error", "Reservation not found or Room ID is missing.");
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Online Reservations</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



    <script>
        function confirmDelete(url) {
            if (confirm("Are you sure you want to delete this reservation?")) {
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
                    <h5 class="titleFont mb-1">Online Reservation</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Online Reservation</a></li>
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
                <!--    <a class="btn btn-primary someText btnAddCategory" href="#" id="viewReservation">View</a>    -->
                        <a class="btn btn-primary someText btnAddCategory" href="#" id="extendReservation">Extend</a>
                        <a class="btn btn-primary someText btnAddCategory" href="#" id="downloadReservation">Download</a>
                        <button type="submit" class="btn btn-danger someText mt-3 mb-3" name="delete_selected" onclick="confirmDeleteSelected(event)">
                            Delete Selected
                        </button>
                    </div>


                </div>

<!-- Table -->
<div class="mt-5">
    <form action="online_reservations.php" method="POST">
        <table class="table table-striped table-hover table-responsive">
            <thead>
                <th scope="col" class="col-1"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col" class="col-1">Reservation ID</th>
                <th scope="col" class="col-1">Client ID</th>
                <th scope="col" class="col-1">Room ID</th>
                <th scope="col" class="col-2">Check-in</th>
                <th scope="col" class="col-2">Check-out</th>
                <th scope="col" class="col-2">Total Price</th>
                <th scope="col" class="col-2">Type</th>
                <th scope="col" class="col-2">Status</th>
                <th scope="col">Operations</th>
            </thead>
            <tbody id="results">
                <?php
                require_once './config/config.php';
                $query = "SELECT reservation_id, client_id, room_id, check_in, check_out, total_price, type, reservation_status FROM reservations WHERE type='Online'";
                $stmt = $mysqli->prepare($query);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_object()) {
                ?>
                    <tr>
                        <td><input type="checkbox" class="deleteCheckbox" name="selected_ids[]" value="<?php echo $row->reservation_id; ?>"></td>
                        <td class="align-middle"><?php echo $row->reservation_id; ?></td>
                        <td class="align-middle"><?php echo $row->client_id; ?></td>
                        <td class="align-middle"><?php echo $row->room_id; ?></td>
                        <td class="align-middle"><?php echo $row->check_in; ?></td>
                        <td class="align-middle"><?php echo $row->check_out; ?></td>
                        <td class="align-middle">₱<?php echo number_format($row->total_price, 2); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($row->type); ?></td>
                        <td class="align-middle">
                            <span class="badge bg-<?php echo ($row->reservation_status == 'confirmed') ? 'success' : ($row->reservation_status == 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($row->reservation_status); ?>
                            </span>
                        </td>
                        <td class="d-flex align-middle">
                            <button type="button" class="btn btn-success btn-sm me-1 open-modal" data-bs-toggle="modal" data-bs-target="#updateModal" 
                                data-id="<?php echo $row->reservation_id; ?>" data-status="<?php echo $row->reservation_status; ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a class="btn btn-danger btn-sm someText" style="padding: 5px;" href="#" onClick="confirmDelete('online_reservation.php?deleteReservation=<?php echo $row->reservation_id; ?>')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </form>

</div>


<!-- View Reservation Modal -->
<div class="modal fade" id="viewReservationModal" tabindex="-1" aria-labelledby="viewReservationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Large Modal for Better Display -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Categories for Better Readability -->

                <!-- Client Details -->
                <h5 class="text-primary">Client Information</h5>
                <p><strong>Name:</strong> <span id="client_name"></span></p>
                <p><strong>Email:</strong> <span id="client_email"></span></p>
                <p><strong>Contact:</strong> <span id="client_contact"></span></p>
                <p><strong>Address:</strong> <span id="client_address"></span></p>

                <hr>

                <!-- Reservation Details -->
                <h5 class="text-primary">Reservation Details</h5>
                <p><strong>Check-in Date:</strong> <span id="check_in_date"></span></p>
                <p><strong>Check-out Date:</strong> <span id="check_out_date"></span></p>
                <p><strong>Reservation Type:</strong> <span id="reservation_type"></span></p>
                <p><strong>Status:</strong> <span id="reservation_status"></span></p>

                <hr>

                <!-- Payment Details -->
                <h5 class="text-primary">Payment Information</h5>
                <p><strong>Payment Method:</strong> <span id="payment_method"></span></p>
                <p><strong>GCash Name:</strong> <span id="client_gcash_name"></span></p>
                <p><strong>GCash Number:</strong> <span id="client_gcash_number"></span></p>
                <p><strong>GCash Ref Number:</strong> <span id="client_gcash_ref"></span></p>
                <p><strong>GCash Screenshot:</strong> <img id="gcash_screenshot" src="" alt="GCash Screenshot" width="100"></p>

                <hr>

                <!-- Billing Details -->
                <h5 class="text-primary">Billing Summary</h5>
                <p><strong>Total Price:</strong> <span id="total_price"></span></p>
                <p><strong>Amount Paid:</strong> <span id="amount_paid"></span></p>
                <p><strong>Balance:</strong> <span id="balance"></span></p>
                <p><strong>Payment Remarks:</strong> <span id="payment_remarks"></span></p>
            </div>
        </div>
    </div>
</div>

<script> 
$(document).ready(function () {
    // Enable/disable the View button based on checkbox selection
    $(".deleteCheckbox").change(function () {
        let selectedCount = $(".deleteCheckbox:checked").length;
        let viewButton = $("#viewReservation");

        if (selectedCount === 1) {
            viewButton.prop("disabled", false);
        } else {
            viewButton.prop("disabled", true);
        }
    });

    // When clicking the View button
    $("#viewReservation").click(function (e) {
        e.preventDefault();
        let selectedRows = $(".deleteCheckbox:checked");

        if (selectedRows.length === 0) {
            alert("Please select a reservation to view.");
        } else if (selectedRows.length > 1) {
            alert("You can only view one reservation at a time.");
        } else {
            // Get the selected reservation ID
            let reservationId = selectedRows.val();

            // Fetch reservation details via AJAX
            $.ajax({
                url: "fetch_reservation1.php",
                type: "POST",
                data: { reservation_id: reservationId },
                dataType: "json",
                success: function (data) {
                    // Client Information
                    $("#client_name").text(data.client_name);
                    $("#client_email").text(data.client_email);
                    $("#client_contact").text(data.client_contact);
                    $("#client_address").text(data.client_address);

                    // Reservation Details
                    $("#room_id").text(data.room_id);
                    $("#check_in_date").text(data.check_in);
                    $("#check_out_date").text(data.check_out);
                    $("#reservation_type").text(data.type); // Updated field name
                    $("#reservation_status").text(data.reservation_status);

                    // Payment Information
                    $("#payment_method").text(data.payment_method);
                    $("#total_price").text("₱" + parseFloat(data.total_price).toFixed(2));
                    $("#amount_paid").text("₱" + parseFloat(data.amount_paid).toFixed(2));  // Adjust logic if needed for amount paid
                    $("#balance").text("₱" + parseFloat(data.balance).toFixed(2));  // Same here for balance calculation
                    $("#payment_remarks").text(data.payment_remarks);  // If this field is in use

                    // GCash Information
                    $("#client_gcash_name").text(data.gcash_name);
                    $("#client_gcash_number").text(data.gcash_number);
                    $("#client_gcash_ref").text(data.gcash_ref);

                    // GCash Screenshot (if you want to show the image)
                    if (data.gcash_screenshot) {
                        $("#gcash_screenshot").attr("src", "./dist/img/" + data.gcash_screenshot);
                    } else {
                        $("#gcash_screenshot").attr("src", "./dist/img/default.png"); // Placeholder image
                    }

                    // Show the modal
                    $("#viewReservationModal").modal("show");
                },
                error: function () {
                    alert("Failed to fetch reservation details.");
                }
            });
        }
    });
});
</script>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Reservation Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateForm" action="update_online_reservation.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="reservationId" name="reservation_id">
                    <label for="reservation_status" class="form-label">Status</label>
                    <select class="form-control" id="reservation_status" name="reservation_status">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".open-modal").forEach(button => {
            button.addEventListener("click", function () {
                const reservationId = this.getAttribute("data-id");
                const reservationStatus = this.getAttribute("data-status");
                document.getElementById("reservationId").value = reservationId;
                document.getElementById("reservation_status").value = reservationStatus;
            });
        });

        document.getElementById("updateForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent default submission

            if (confirm("Are you sure you want to update this reservation?")) {
                this.submit(); // Submit if user confirms
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

        // Get all checkboxes
        const checkboxes = document.querySelectorAll(".deleteCheckbox:checked");

        if (checkboxes.length === 0) {
            alert("Please select at least one reservation to delete.");
            return;
        }

        // Confirm before deleting
        if (confirm("Are you sure you want to delete the selected reservations?")) {
            form.action = "multi_delete_online_reservation.php"; // Set the form action
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


<!-- Extend Reservation Modal -->
<div class="modal fade" id="customExtendReservationModal" tabindex="-1" aria-labelledby="customExtendReservationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customExtendReservationModalLabel">Extend Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customExtendReservationForm">
                    <div class="mb-3">
                        <label for="customCheckInDate" class="form-label">Check-in Date</label>
                        <input type="text" class="form-control" id="customCheckInDate" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="customCheckOutDate" class="form-label">Check-out Date</label>
                        <input type="date" class="form-control" id="customCheckOutDate" required>
                    </div>

                    <!-- Hidden fields to store current check-out date, total price, reservation ID, and room ID -->
                    <input type="hidden" id="customHiddenCheckOutDate">
                    <input type="hidden" id="customHiddenTotalPrice">
                    <input type="hidden" id="customHiddenReservationId">
                    <input type="hidden" id="customHiddenRoomId"> <!-- This is the new hidden field for room_id -->
                    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Extend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let selectedReservation = null;

        // Event listener for the "Extend" button
        document.getElementById('extendReservation').addEventListener('click', function (e) {
            // Get all checkboxes that are selected
            const selectedCheckboxes = document.querySelectorAll('.deleteCheckbox:checked');
            
            // Ensure exactly one row is selected
            if (selectedCheckboxes.length === 0) {
                alert('Please select a reservation to extend.');
                return;
            }

            if (selectedCheckboxes.length !== 1) {
                alert('Please select a reservation one at a time.');
                return;
            }

            // Get the selected row's data
            const selectedRow = selectedCheckboxes[0].closest('tr');
            const reservationId = selectedRow.querySelector('input[type="checkbox"]').value;
            const status = selectedRow.querySelector('td:nth-child(9)').innerText.trim().toLowerCase();
            const checkInDate = selectedRow.querySelector('td:nth-child(5)').innerText;
            const checkOutDate = selectedRow.querySelector('td:nth-child(6)').innerText;
            const totalPrice = parseFloat(selectedRow.querySelector('td:nth-child(7)').innerText.replace('₱', '').replace(',', ''));
            const roomId = selectedRow.querySelector('td:nth-child(4)').innerText.trim(); 

            // Check if the status is "confirmed"
            if (status !== 'confirmed') {
                alert('Only confirmed reservations can be extended.');
                return;
            }

            // Set the selected reservation data
            document.getElementById('customCheckInDate').value = checkInDate;
            document.getElementById('customCheckOutDate').value = checkOutDate;  // Set current check-out date
            document.getElementById('customHiddenCheckOutDate').value = checkOutDate;  // Store the current check-out date in a hidden field
            document.getElementById('customHiddenTotalPrice').value = totalPrice;  // Store total price in a hidden field
            document.getElementById('customHiddenReservationId').value = reservationId;  // Store reservation ID in a hidden field
            document.getElementById('customHiddenRoomId').value = roomId;  // Store room_id in the hidden field

            // Show the modal
            new bootstrap.Modal(document.getElementById('customExtendReservationModal')).show();
        });

        // Handle form submission for extending the reservation
        document.getElementById('customExtendReservationForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Get the new check-out date from the modal
            const newCheckOutDate = document.getElementById('customCheckOutDate').value;
            const reservationId = document.getElementById('customHiddenReservationId').value;
            const roomId = document.getElementById('customHiddenRoomId').value;  // Get the room_id from the hidden field

            // Get the original check-out date from the hidden field
            const originalCheckOutDate = document.getElementById('customHiddenCheckOutDate').value;

            // Get the reservation ID from the hidden field
            const currentTotalPrice = parseFloat(document.getElementById('customHiddenTotalPrice').value);

            // Convert dates to Date objects
            const checkInDate = new Date(document.getElementById('customCheckInDate').value);
            const originalCheckOut = new Date(originalCheckOutDate);
            const newCheckOut = new Date(newCheckOutDate);

            // Calculate the number of days between the check-in date and new check-out date
            const dateDifference = Math.floor((newCheckOut - checkInDate) / (1000 * 3600 * 24));

            // If the date difference is 0 or negative, we cannot extend the reservation
            if (dateDifference <= 0) {
                alert("The new check-out date must be later than the check-in date.");
                return;
            }

            // Send request to get room price and calculate new total price
            fetch('get_room_price.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ room_id: roomId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Get room price from the response
                    const roomPrice = parseFloat(data.room_price);  // Make sure to parse it as a number

                    // Calculate the new total price
                    const newTotalPrice = roomPrice * dateDifference;

                    // Confirm the extension with the details
                    const confirmation = confirm(`
                        Are you sure you want to extend this reservation?

                        Check-in Date: ${document.getElementById('customCheckInDate').value}
                        Current Check-out Date: ${originalCheckOutDate}
                        New Check-out Date: ${newCheckOutDate}
                        Current Total Price: ₱${currentTotalPrice.toFixed(2)}
                        Room Price: ₱${roomPrice.toFixed(2)} per night
                        New Total Price: ₱${newTotalPrice.toFixed(2)}
                    `);

                    if (confirmation) {
                        // Proceed with the extension (e.g., update the reservation in the backend)
                        const formData = new FormData();
                        formData.append('reservation_id', reservationId);
                        formData.append('new_check_out_date', newCheckOutDate);
                        formData.append('new_total_price', newTotalPrice);

                        // AJAX request to update the reservation in the database
                        fetch('extend_online_reservation.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Reservation extended successfully.');
                                location.reload();  // Optionally reload the page to reflect changes
                            } else {
                                alert('There was an error updating the reservation.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while processing your request.');
                        });
                    } else {
                        alert('Extension canceled.');
                    }
                } else {
                    alert('Unable to fetch room price.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching room price.');
            });
        });
    });
</script>





            </div>
        </div>
    </div>

    <!-- Importing AJAX for Search -->
    <?php require('./ajax/online_reservation_ajax.php'); ?>

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

            let confirmDownload = confirm("Are you sure you want to generate a PDF for the reservations?");
            if (confirmDownload) {
                window.location.href = "online_reservation_generate_pdf.php"; // Redirect to PHP script for PDF generation
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    const checkOutInput = document.getElementById("customCheckOutDate");
    const hiddenCheckOutInput = document.getElementById("customHiddenCheckOutDate");

    // Function to disable past dates based on the fetched check-out date
    function disablePastFetchedCheckOutDate() {
        let fetchedCheckOutDate = hiddenCheckOutInput.value; // Fetched check-out date from the database
        if (fetchedCheckOutDate) {
            checkOutInput.setAttribute("min", fetchedCheckOutDate);
        }
    }

    // Call the function when the modal is opened
    document.getElementById("customExtendReservationModal").addEventListener("show.bs.modal", function () {
        disablePastFetchedCheckOutDate();
    });
});

    </script>



</body>

</html>