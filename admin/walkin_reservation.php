<?php
session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

if (isset($_GET['deleteReservation'])) {
    $id = $_GET['deleteReservation'];

    // Fetch the associated room_id before deleting the reservation
    $room_query = "SELECT room_id FROM walkin_reservation WHERE reservation_id = ?";
    $stmt_room = $mysqli->prepare($room_query);
    $stmt_room->bind_param('s', $id);
    $stmt_room->execute();
    $result = $stmt_room->get_result();
    $room = $result->fetch_assoc();
    $stmt_room->close();

    // Proceed with deletion if room_id is found
    if ($room) {
        $room_id = $room['room_id'];

        // Delete the reservation
        $delete_query = "DELETE FROM walkin_reservation WHERE reservation_id = ?";
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

            alert("success", "Walk-in reservation deleted successfully! Room is now Available.");
        } else {
            alert("error", "Error deleting the walk-in reservation. Please try again.");
        }
    } else {
        alert("error", "Walk-in reservation not found or Room ID is missing.");
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Walk-in Reservations</title>

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
                    <h5 class="titleFont mb-1">Walk-in Reservations</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Walk-in Reservation</a></li>
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
                        <!-- Buttons -->
                        <a class="btn btn-primary someText btnAddCategory" href="#" data-bs-toggle="modal" data-bs-target="#bookRoomModal" onclick="fetchCategories()">Book</a>
                <!--    <a class="btn btn-primary someText btnAddCategory" href="#" id="viewReservation">View</a>   -->
                        <a class="btn btn-primary someText btnAddCategory" href="#" id="customExtendReservation">Extend</a>
                        <a class="btn btn-primary someText btnAddCategory" href="#" id="downloadReservation">Download</a>
                        <button type="submit" class="btn btn-danger someText mt-3 mb-3" name="delete_selected">Delete Selected</button>
                    </div>


                </div>

<!-- Walk-in Reservations Table -->
<div class="mt-5">
    <form action="multi_delete_walkin_reservation.php" method="POST">
        <table class="table table-striped table-hover table-responsive">
            <thead>
                <th scope="col" class="col-1"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col" class="col-1">Reservation ID</th>
                <th scope="col" class="col-2">Client Name</th>
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

                $query = "SELECT reservation_id, client_name, room_id, check_in_date, check_out_date, total_price, reservation_type, reservation_status 
                          FROM walkin_reservation";
                $stmt = $mysqli->prepare($query);
                $stmt->execute();
                $res = $stmt->get_result();

                while ($row = $res->fetch_object()) {
                ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="deleteCheckbox" name="selected_ids[]" value="<?php echo $row->reservation_id; ?>">
                        </td>
                        <td class="align-middle"><?php echo $row->reservation_id; ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($row->client_name); ?></td>
                        <td class="align-middle"><?php echo $row->room_id; ?></td>
                        <td class="align-middle"><?php echo $row->check_in_date; ?></td>
                        <td class="align-middle"><?php echo $row->check_out_date; ?></td>
                        <td class="align-middle">₱<?php echo number_format($row->total_price, 2); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($row->reservation_type); ?></td>
                        <td class="align-middle">
                            <span class="badge bg-<?php echo ($row->reservation_status == 'confirmed') ? 'success' : ($row->reservation_status == 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($row->reservation_status); ?>
                            </span>
                        </td>
                        <td class="d-flex align-middle">
                            <!-- Update Button -->
                            <button type="button" class="btn btn-success btn-sm me-1 open-modal" data-bs-toggle="modal" data-bs-target="#updateModal" 
                                data-id="<?php echo $row->reservation_id; ?>" data-status="<?php echo $row->reservation_status; ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- Delete Button -->
                            <a class="btn btn-danger btn-sm" href="#" onClick="confirmDelete('walkin_reservation.php?deleteReservation=<?php echo $row->reservation_id; ?>')">
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


<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Reservation Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateForm" action="update_walkin_reservation.php" method="POST">
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
            event.preventDefault(); // Prevent default form submission

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

        // Get all selected checkboxes
        const checkboxes = document.querySelectorAll(".deleteCheckbox:checked");

        if (checkboxes.length === 0) {
            alert("Please select at least one reservation to delete.");
            return;
        }

        // Confirm before deleting
        if (confirm("Are you sure you want to delete the selected reservations?")) {
            form.action = "multi_delete_walkin_reservation.php"; // Set the form action
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
    <?php require('./ajax/walkin_reservation_ajax.php'); ?>

    <script>
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.deleteCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>


<!-- Booking Modal -->
<div class="modal fade" id="bookRoomModal" tabindex="-1" aria-labelledby="bookRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookRoomModalLabel">Book Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Room Selection -->
                <div class="mb-3 text-center">
                    <img id="roomImage" src="./dist/img/standard-02.jpg" class="img-fluid" style="max-width: 300px;" alt="Room Image">
                </div>
                <div class="mb-3">
                    <label for="roomCategory" class="form-label">Room Category</label>
                    <select id="roomCategory" class="form-select">
                        <option value="">Select a Category</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="room" class="form-label">Room</label>
                    <select id="room" class="form-select" disabled>
                        <option value="">Select a Room</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="roomId" class="form-label">Room ID</label>
                    <input type="text" id="roomId" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="roomDescription" class="form-label">Room Description</label>
                    <input type="text" id="roomDescription" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="roomPrice" class="form-label">Room Price</label>
                    <input type="text" id="roomPrice" class="form-control" readonly>
                </div>
                <hr>
                <!-- Reservation Details -->
                <h5>Reservation Details</h5>
                <div class="mb-3">
                    <label for="checkInDate" class="form-label">Check-in Date</label>
                    <input type="date" id="checkInDate" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="checkOutDate" class="form-label">Check-out Date</label>
                    <input type="date" id="checkOutDate" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="totalPrice" class="form-label">Total Price</label>
                    <input type="text" id="totalPrice" class="form-control" readonly>
                </div>
                <hr>
                <!-- Customer Details -->
                <h5>Customer Details</h5>
                <div class="mb-3">
                    <label for="customerName" class="form-label">Name</label>
                    <input type="text" id="customerName" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="customerEmail" class="form-label">Email</label>
                    <input type="email" id="customerEmail" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="customerContact" class="form-label">Contact</label>
                    <input type="number" id="customerContact" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="customerAddress" class="form-label">Address</label>
                    <textarea id="customerAddress" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="idType" class="form-label">ID Type</label>
                    <select id="idType" class="form-select">
                        <option value="">Select ID Type</option>
                        <option value="Driver's License">Driver's License</option>
                        <option value="National ID">National ID</option>
                        <option value="SSS ID">SSS ID</option>
                        <option value="Passport">Passport</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="validId" class="form-label">Valid ID</label>
                    <input type="file" id="validId" class="form-control">
                </div>
                <hr>
                <!-- Payment Method -->
                <h5>Payment Method</h5>
                <div class="mb-3">
                    <label for="paymentMethod" class="form-label">Select Payment Method</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="Cash">Cash</option>
                        <option value="Gcash">Gcash</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="confirmBooking" class="btn btn-primary">Confirm Booking</button>
            </div>
        </div>
    </div>
</div>


<!-- Gcash Payment Modal -->
<div class="modal fade" id="gcashPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gcash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Sender's Gcash Name</label>
                    <input type="text" id="gcashName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sender's Gcash Number</label>
                    <input type="number" id="gcashNumber" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sender's Gcash Ref</label>
                    <input type="text" id="gcashRef" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gcash Ref Image</label>
                    <input type="file" id="gcashImage" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Price</label>
                    <input type="text" id="gcashTotalPrice" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" id="gcashAmountPaid" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Balance</label>
                    <input type="text" id="gcashBalance" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Remarks</label>
                    <input type="text" id="gcashRemarks" class="form-control" readonly>
                </div>
                <button class="btn btn-primary w-100" onclick="submitBooking('gcash')">Submit Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- Cash Payment Modal -->
<div class="modal fade" id="cashPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Total Price</label>
                    <input type="text" id="cashTotalPrice" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" id="cashAmountPaid" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Change</label>
                    <input type="text" id="cashChange" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Balance</label>
                    <input type="text" id="cashBalance" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Remarks</label>
                    <input type="text" id="cashRemarks" class="form-control" readonly>
                </div>
                <button class="btn btn-primary w-100" onclick="submitBooking('cash')">Submit Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Disable past dates for check-in
        let today = new Date().toISOString().split("T")[0];
        document.getElementById("checkInDate").setAttribute("min", today);

        // Event listener for check-in date selection
        document.getElementById("checkInDate").addEventListener("change", function() {
            let checkIn = new Date(this.value);
            let checkOutInput = document.getElementById("checkOutDate");

            if (this.value) {
                checkOutInput.removeAttribute("readonly");
                checkOutInput.setAttribute("min", this.value);
                checkOutInput.value = "";
            } else {
                checkOutInput.setAttribute("readonly", true);
                checkOutInput.value = "";
            }

            calculateTotalPrice();
        });

        // Event listener for check-out date selection
        document.getElementById("checkOutDate").addEventListener("change", function() {
            calculateTotalPrice();
        });

        function calculateTotalPrice() {
            let checkInDate = document.getElementById("checkInDate").value;
            let checkOutDate = document.getElementById("checkOutDate").value;
            let roomPrice = parseFloat(document.getElementById("roomPrice").value) || 0;

            if (checkInDate && checkOutDate && roomPrice) {
                let checkIn = new Date(checkInDate);
                let checkOut = new Date(checkOutDate);
                let timeDiff = checkOut.getTime() - checkIn.getTime();
                let days = Math.ceil(timeDiff / (1000 * 3600 * 24));

                if (days > 0) {
                    let totalPrice = days * roomPrice;
                    document.getElementById("totalPrice").value = totalPrice.toFixed(2);
                } else {
                    document.getElementById("totalPrice").value = "";
                }
            } else {
                document.getElementById("totalPrice").value = "";
            }
        }
    });
</script>




</body>


<script>
    function fetchCategories() {
        // Clear previous options
        document.getElementById("roomCategory").innerHTML = '<option value="">Select a Category</option>';
        document.getElementById("room").innerHTML = '<option value="">Select a Room</option>';
        document.getElementById("room").disabled = true;
        document.getElementById("roomId").value = ""; // Clear Room ID field
        document.getElementById("roomDescription").value = ""; // Clear Room ID field
        document.getElementById("roomPrice").value = ""; // Clear Room ID field

        // Fetch Room Categories
        fetch('fetch_room_categories.php')
            .then(response => response.json())
            .then(data => {
                let categoryDropdown = document.getElementById("roomCategory");
                data.forEach(category => {
                    let option = document.createElement("option");
                    option.value = category.category_id;
                    option.textContent = category.category_name;
                    categoryDropdown.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching categories:', error));
    }

    document.getElementById("roomCategory").addEventListener("change", function() {
        let categoryName = this.options[this.selectedIndex].text; // Get category name
        let roomDropdown = document.getElementById("room");

        if (categoryName) {
            roomDropdown.innerHTML = '<option value="">Loading rooms...</option>';
            roomDropdown.disabled = true;

            fetch(`fetch_rooms.php?category_name=${encodeURIComponent(categoryName)}`)
                .then(response => response.json())
                .then(data => {
                    roomDropdown.innerHTML = '<option value="">Select a Room</option>';
                    data.forEach(room => {
                        let option = document.createElement("option");
                        option.value = room.room_id;
                        option.textContent = room.room_name;
                        option.dataset.description = room.room_description;
                        option.dataset.price = room.room_price;
                        option.dataset.image = room.room_picture; // Now contains the full path
                        roomDropdown.appendChild(option);
                    });
                    roomDropdown.disabled = false;
                })
                .catch(error => console.error('Error fetching rooms:', error));
        } else {
            roomDropdown.innerHTML = '<option value="">Select a Room</option>';
            roomDropdown.disabled = true;
            document.getElementById("roomId").value = ""; // Clear Room ID field
            document.getElementById("roomDescription").value = ""; // Clear Room ID field
            document.getElementById("roomPrice").value = ""; // Clear Room ID field
        }
    });

    document.getElementById("room").addEventListener("change", function() {
        let selectedRoomId = this.value;

        if (selectedRoomId) {
            document.getElementById("roomId").value = selectedRoomId; // Populate Room ID field

            fetch(`fetch_room_details.php?room_id=${selectedRoomId}`)
                .then(response => response.json())
                .then(room => {
                    document.getElementById("roomDescription").value = room.room_description;
                    document.getElementById("roomPrice").value = room.room_price;

                    // Ensure the correct image path is displayed
                    let roomImageElement = document.getElementById("roomImage");
                    if (room.room_picture) {
                        roomImageElement.src = room.room_picture;
                    }
                })
                .catch(error => console.error('Error fetching room details:', error));
        } else {
            document.getElementById("roomId").value = ""; // Clear Room ID field
            document.getElementById("roomDescription").value = ""; // Clear Room ID field
            document.getElementById("roomPrice").value = ""; // Clear Room ID field
        }
    });
</script>



<script>
function validateBookingFields() {
    // Check if any of the required room-related fields are empty
    let roomFields = ["roomCategory", "room", "roomId", "roomDescription", "roomPrice"];
    let isRoomInvalid = roomFields.some(field => !document.getElementById(field).value.trim());

    if (isRoomInvalid) {
        alert("Please select a category and room first.");
        return false;
    }

    // Validate Check-in and Check-out dates
    let checkInDate = document.getElementById("checkInDate").value;
    let checkOutDate = document.getElementById("checkOutDate").value;

    if (!checkInDate || !checkOutDate) {
        alert("Please select both Check-in and Check-out dates.");
        return false;
    }

    let checkIn = new Date(checkInDate);
    let checkOut = new Date(checkOutDate);

    if (checkOut < checkIn) {
        alert("Check-out date cannot be earlier than the Check-in date.");
        return false;
    }

    if (checkOut.getTime() === checkIn.getTime()) {
        alert("Check-in and Check-out dates cannot be the same.");
        return false;
    }

    // General fields that must not be empty
    let fields = {
        "customerName": "Customer Name",
        "customerEmail": "Customer Email",
        "customerContact": "Customer Contact",
        "customerAddress": "Customer Address",
        "idType": "ID Type",
        "validId": "Valid ID",
        "paymentMethod": "Payment Method"
    };

    for (let field in fields) {
        let input = document.getElementById(field);
        if (!input.value.trim()) {
            alert(`Please fill out the ${fields[field]}.`);
            input.focus();
            return false;
        }
    }

    // Validate email format (must be a Gmail address)
    let email = document.getElementById("customerEmail").value.trim();
    if (!/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email)) {
        alert("Please enter a valid Gmail address (example@gmail.com).");
        document.getElementById("customerEmail").focus();
        return false;
    }

    // Validate contact number (11 digits, numeric, starts with 09)
    let contact = document.getElementById("customerContact").value.trim();
    if (!/^(09)\d{9}$/.test(contact)) {
        alert("Please enter a valid 11-digit contact number starting with 09.");
        document.getElementById("customerContact").focus();
        return false;
    }

    // Validate file upload (Valid ID must be uploaded)
    let validId = document.getElementById("validId").files.length;
    if (validId === 0) {
        alert("Please upload a valid ID.");
        document.getElementById("validId").focus();
        return false;
    }

    return true; // All validations passed
}


document.addEventListener("DOMContentLoaded", function() {
    function updatePaymentFields(amountPaidInput, balanceField, remarksField) {
        let totalPrice = parseFloat(document.getElementById("totalPrice").value) || 0;
        let amountPaid = parseFloat(amountPaidInput.value) || 0;
        let balance = totalPrice - amountPaid;

        balanceField.value = balance.toFixed(2);
        
        if (amountPaid < totalPrice) {
            remarksField.value = "Downpayment";
        } else if (amountPaid === totalPrice) {
            remarksField.value = "Fully Paid";
        } else {
            remarksField.value = "Overpaid";
        }
    }

    document.getElementById("gcashAmountPaid").addEventListener("input", function() {
        updatePaymentFields(this, document.getElementById("gcashBalance"), document.getElementById("gcashRemarks"));
    });

    document.getElementById("cashAmountPaid").addEventListener("input", function() {
        let amountPaid = parseFloat(this.value) || 0;
        let totalPrice = parseFloat(document.getElementById("totalPrice").value) || 0;
        let change = amountPaid - totalPrice;
        document.getElementById("cashChange").value = change > 0 ? change.toFixed(2) : "0.00";
        updatePaymentFields(this, document.getElementById("cashBalance"), document.getElementById("cashRemarks"));
    });
});




document.getElementById("confirmBooking").addEventListener("click", function () {
    if (!validateBookingFields()) return; // Prevents opening the payment modal if validation fails

    let selectedPayment = document.getElementById("paymentMethod").value;
    let totalPrice = parseFloat(document.getElementById("totalPrice").value) || 0;

    if (selectedPayment === "Gcash") {
        document.getElementById("gcashTotalPrice").value = totalPrice;
        new bootstrap.Modal(document.getElementById("gcashPaymentModal")).show();
    } else if (selectedPayment === "Cash") {
        document.getElementById("cashTotalPrice").value = totalPrice;
        new bootstrap.Modal(document.getElementById("cashPaymentModal")).show();
    }
});


function submitBooking(type) {
    if (!validateBookingFields()) return; // Ensure validation before submission

    let confirmation = confirm("Are you sure you want to submit the booking?");
    if (!confirmation) return;

    let formData = new FormData();
    formData.append("room_id", document.getElementById("roomId")?.value);
    formData.append("client_name", document.getElementById("customerName")?.value);
    formData.append("client_email", document.getElementById("customerEmail")?.value);
    formData.append("client_contact", document.getElementById("customerContact")?.value);
    formData.append("client_address", document.getElementById("customerAddress")?.value);
    formData.append("client_id_type", document.getElementById("idType")?.value);
    formData.append("check_in_date", document.getElementById("checkInDate")?.value);
    formData.append("check_out_date", document.getElementById("checkOutDate")?.value);
    formData.append("payment_method", type);
    formData.append("reservation_type", type);

    // Attach valid ID image file
    let validIdFile = document.getElementById("validId")?.files[0];
    if (validIdFile) {
        formData.append("client_id_image", validIdFile);
    }

    // Determine payment remarks field based on the selected payment method
    let paymentRemarks = "";
    if (type === "gcash") {
        formData.append("client_gcash_name", document.getElementById("gcashName")?.value);
        formData.append("client_gcash_number", document.getElementById("gcashNumber")?.value);
        formData.append("client_gcash_ref", document.getElementById("gcashRef")?.value);
        formData.append("total_price", document.getElementById("gcashTotalPrice")?.value);
        formData.append("amount_paid", document.getElementById("gcashAmountPaid")?.value);
        formData.append("balance", document.getElementById("gcashBalance")?.value);

        // Attach GCash Reference Image
        let gcashRefImage = document.getElementById("gcashRefImage")?.files[0];
        if (gcashRefImage) {
            let fileName = gcashRefImage.name; // Get filename
            formData.append("client_gcash_ref_image", gcashRefImage); // Upload file
            formData.append("gcash_ref_filename", fileName); // Store filename
        }

        // Get GCash payment remarks
        paymentRemarks = document.getElementById("gcashRemarks")?.value || "";
    } else {
        formData.append("total_price", document.getElementById("cashTotalPrice")?.value);
        formData.append("amount_paid", document.getElementById("cashAmountPaid")?.value);
        formData.append("balance", document.getElementById("cashBalance")?.value);

        // Get Cash payment remarks
        paymentRemarks = document.getElementById("cashRemarks")?.value || "";
    }

    // Append payment remarks to formData
    formData.append("payment_remarks", paymentRemarks);

    fetch("walkin_book.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text()) // Read response as text for debugging
    .then(data => {
        console.log("Raw Response:", data); // Debugging line

        try {
            let json = JSON.parse(data); // Try parsing JSON
            if (json.success) {
                alert("Booking successfully submitted!");
                location.reload();
            } else {
                alert("Error: " + json.message);
            }
        } catch (error) {
            alert("Invalid JSON response from server. Check console for details.");
            console.error("Parsing error:", error, "Response:", data);
        }
    })
    .catch(error => {
        console.error("Error submitting booking:", error);
        alert("An error occurred while submitting the booking. Please try again.");
    });
}

</script>


    <script>
        document.getElementById("downloadReservation").addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default link behavior

            let tableRows = document.querySelectorAll("#results tr"); // Get table rows
            if (tableRows.length === 0) {
                alert("Error: No data available to generate a PDF.");
                return;
            }

            let confirmDownload = confirm("Are you sure you want to generate a PDF for the walkin reservations?");
            if (confirmDownload) {
                window.location.href = "walkin_reservation_generate_pdf.php"; // Redirect to PHP script for PDF generation
            }
        });
    </script>


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
                url: "fetch_reservation.php",
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
                    $("#check_in_date").text(data.check_in_date);
                    $("#check_out_date").text(data.check_out_date);
                    $("#reservation_type").text(data.reservation_type);
                    $("#reservation_status").text(data.reservation_status);

                    // Payment Information
                    $("#payment_method").text(data.payment_method);
                    $("#total_price").text("₱" + parseFloat(data.total_price).toFixed(2));
                    $("#amount_paid").text("₱" + parseFloat(data.amount_paid).toFixed(2));
                    $("#balance").text("₱" + parseFloat(data.balance).toFixed(2));
                    $("#payment_remarks").text(data.payment_remarks);

                    // GCash Information
                    $("#client_gcash_name").text(data.client_gcash_name);
                    $("#client_gcash_number").text(data.client_gcash_number);
                    $("#client_gcash_ref").text(data.client_gcash_ref);

                    // Display Client ID Image
                    if (data.client_id_image) {
                        $("#client_id_image").attr("src", "./dist/img/" + data.client_id_image);
                    } else {
                        $("#client_id_image").attr("src", "./dist/img/default.png"); // Placeholder image if no ID image is found
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
    document.getElementById('customExtendReservation').addEventListener('click', function (e) {
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
                    fetch('extend_reservation.php', {  // Updated the endpoint to 'extend_online_reservation.php'
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


<script>
    document.addEventListener("DOMContentLoaded", function () {
    const checkOutInput = document.getElementById("customCheckOutDate");
    const hiddenCheckOutInput = document.getElementById("customHiddenCheckOutDate");

    // Function to set the min attribute based on the fetched check-out date
    function disablePastDates() {
        let fetchedCheckOutDate = hiddenCheckOutInput.value; // Fetch the stored check-out date
        if (fetchedCheckOutDate) {
            let minDate = new Date(fetchedCheckOutDate);
            minDate.setDate(minDate.getDate() + 1); // Ensure only future dates can be selected
            
            let minDateString = minDate.toISOString().split("T")[0]; // Convert to YYYY-MM-DD format
            checkOutInput.setAttribute("min", minDateString);
        }
    }

    // Call function when modal is opened (assuming data is populated before opening)
    document.getElementById("customExtendReservationModal").addEventListener("show.bs.modal", disablePastDates);
});

</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
    const checkInInput = document.getElementById("customCheckInDate");
    const checkOutInput = document.getElementById("customCheckOutDate");

    // Function to get today's date in YYYY-MM-DD format
    function getTodayDate() {
        let today = new Date();
        return today.toISOString().split("T")[0]; // Convert to YYYY-MM-DD format
    }

    // Disable past dates for the check-in date
    function disablePastCheckInDates() {
        let todayDate = getTodayDate();
        checkInInput.setAttribute("min", todayDate);
    }

    // Disable past dates for the check-out date based on the selected check-in date
    function disablePastCheckOutDates() {
        let selectedCheckInDate = checkInInput.value;
        if (selectedCheckInDate) {
            let minCheckOutDate = new Date(selectedCheckInDate);
            minCheckOutDate.setDate(minCheckOutDate.getDate() + 1); // Ensure at least one night stay
            
            let minCheckOutDateString = minCheckOutDate.toISOString().split("T")[0];
            checkOutInput.setAttribute("min", minCheckOutDateString);
        }
    }

    // Set default min values when the page loads
    disablePastCheckInDates();

    // Update check-out date when check-in date is selected
    checkInInput.addEventListener("change", disablePastCheckOutDates);
});

</script>






</html>