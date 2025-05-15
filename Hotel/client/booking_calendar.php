<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

$client_id = $_SESSION['client_id'];

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

// Fetch reservations of the logged-in user
$reservations = [];
$res_query = "SELECT reservation_id, check_in, check_out, reservation_status FROM reservations WHERE client_id = ?";
$stmt = $mysqli->prepare($res_query);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $reservations[] = $row; // Make sure reservation_id is included
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> | Calendar</title>
    
    <!-- Dynamically load favicon if available -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png" href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


</head>

<body>
    <!-- Navigation -->
    <?php require('./inc/nav.php'); ?>

    <br><br><br><br><br><br><br>

    <style>
        .calendar-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 95vw;
            margin-bottom: 10px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            width: 95vw; /* Ensures full width */
            padding: 10px;
        }

        .calendar-cell {
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            border-radius: 8px;
            transition: 0.2s;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: bold;
        }

        .calendar-legend {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
        }



        .calendar-cell:hover {
            background: #e0e0e0;
        }

        .calendar-cell.empty {
            background: transparent;
            pointer-events: none;
        }

        /* Keep only the check-in date styling */
        .calendar-cell.check-in.confirmed {
            background-color: #218838 !important; /* Dark Green */
        }

        .calendar-cell.check-in.pending {
            background-color: #d39e00 !important; /* Dark Yellow */
        }

        .calendar-cell.check-in.cancelled {
            background-color: #c82333 !important; /* Dark Red */
        }

        /* Legends */
        .legend-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .legend {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-box {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .confirmed-box {
            background-color: #218838;
        }

        .pending-box {
            background-color: #d39e00;
        }

        .cancelled-box {
            background-color: #c82333;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <button class="btn btn-primary btn-lg" id="prevMonth">&#9664;</button>
            <h2 id="currentMonth" class="fw-bold text-primary text-uppercase"></h2>
            <button class="btn btn-primary btn-lg" id="nextMonth">&#9654;</button>
        </div>
        <div class="calendar-grid" id="calendar"></div>
        
        <!-- Reservation Legends -->
        <div class="legend-container">
            <div class="legend"><div class="legend-box confirmed-box"></div> Confirmed</div>
            <div class="legend"><div class="legend-box pending-box"></div> Pending</div>
            <div class="legend"><div class="legend-box cancelled-box"></div> Cancelled</div>
        </div>
    </div>

 <!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Reservation Details</h5>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2" id="modalButtons"></div>
            </div>
        </div>
    </div>
</div>

<!-- Full Reservation Details -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation & Room Details</h5>
            </div>
            <div class="modal-body">
                <img id="roomImage" src="" alt="Room Image" class="img-fluid">
                <h5>Room Details</h5>
                <p><strong>Category:</strong> <span id="roomCategory"></span></p>
                <p><strong>Name:</strong> <span id="roomName"></span></p>
                <p><strong>Number:</strong> <span id="roomNumber"></span></p>
                <p><strong>Description:</strong> <span id="roomDescription"></span></p>
                <p><strong>Price:</strong> <span id="roomPrice"></span></p>
                <br>
                <hr>
                <br>
                <h5>Reservation Details</h5>
                <p><strong>Check-in:</strong> <span id="checkInDate"></span></p>
                <p><strong>Check-out:</strong> <span id="checkOutDate"></span></p>
                <p><strong>Payment:</strong> <span id="paymentMethod"></span></p>
                <p><strong>Total:</strong> <span id="totalPrice"></span></p>
                <p><strong>Type:</strong> <span id="reservationType"></span></p>
                <p><strong>Status:</strong> <span id="reservationStatus"></span></p>
            </div>
        </div>
    </div>
</div>



<!-- Request Modal -->
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamicModalLabel">Request Foods & Services</h5>
            </div>
            <div class="modal-body">
                <form id="dynamicModalForm">
                    <!-- Food Section -->
                    <div class="section" id="foodSectionWrapper">
                        <h5 class="section-title">Food</h5>
                        <div id="foodSection" class="list-group"></div>
                    </div>

                    <!-- Beverages Section -->
                    <div class="section" id="beveragesSectionWrapper">
                        <h5 class="section-title">Beverages</h5>
                        <div id="beveragesSection" class="list-group"></div>
                    </div>

                    <!-- Others Section -->
                    <div class="section" id="othersSectionWrapper">
                        <h5 class="section-title">Others</h5>
                        <div id="othersSection" class="list-group"></div>
                    </div>

                    <!-- Services Section -->
                    <div class="section" id="servicesSectionWrapper">
                        <h5 class="section-title">Services</h5>
                        <div id="servicesSection" class="list-group"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary mt-3" id="submitSelectionBtn">Submit Selection</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = {};  // Object to store product quantities

    function showRequestModal(reservationId) {
        console.log("Requesting data...");

        // Set the reservationId in the modal dynamically
        $('#dynamicModal').data('reservationId', reservationId); // Store reservationId in modal


        // Trigger the AJAX request to fetch the data
        $.ajax({
            url: 'fetch_dynamic_data.php', // URL of the PHP file that fetches the data
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log("Data fetched successfully:", response);

                // Food Section
                if (response.food && response.food.length > 0) {
                    let foodContent = '';
                    response.food.forEach(item => {
                        foodContent += `
                            <a href="#" class="list-group-item list-group-item-action product-card" data-category="food" data-id="${item.product_id}" data-name="${item.product_name}" data-price="${item.product_price}" data-image="${item.product_image}">
                                <div class="d-flex w-100 justify-content-between">
                                    <img src="../admin/dist/img/${item.product_image}" class="img-thumbnail me-3" alt="${item.product_name}" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">${item.product_name}</h5>
                                        <p class="mb-1">${item.product_description}</p>
                                        <small class="text-muted">Price: ₱${item.product_price}</small>
                                        <div class="quantity">
                                            <button class="btn btn-sm btn-secondary decrease-btn">-</button>
                                            <span class="quantity-text">0</span>
                                            <button class="btn btn-sm btn-secondary increase-btn">+</button>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#foodSection').html(foodContent);
                    $('#foodSectionWrapper').show();
                } else {
                    $('#foodSectionWrapper').hide();
                }

                // Beverages Section
                if (response.beverages && response.beverages.length > 0) {
                    let beveragesContent = '';
                    response.beverages.forEach(item => {
                        beveragesContent += `
                            <a href="#" class="list-group-item list-group-item-action product-card" data-category="beverages" data-id="${item.product_id}" data-name="${item.product_name}" data-price="${item.product_price}" data-image="${item.product_image}">
                                <div class="d-flex w-100 justify-content-between">
                                    <img src="../admin/dist/img/${item.product_image}" class="img-thumbnail me-3" alt="${item.product_name}" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">${item.product_name}</h5>
                                        <p class="mb-1">${item.product_description}</p>
                                        <small class="text-muted">Price: ₱${item.product_price}</small>
                                        <div class="quantity">
                                            <button class="btn btn-sm btn-secondary decrease-btn">-</button>
                                            <span class="quantity-text">0</span>
                                            <button class="btn btn-sm btn-secondary increase-btn">+</button>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#beveragesSection').html(beveragesContent);
                    $('#beveragesSectionWrapper').show();
                } else {
                    $('#beveragesSectionWrapper').hide();
                }

                // Others Section
                if (response.others && response.others.length > 0) {
                    let othersContent = '';
                    response.others.forEach(item => {
                        othersContent += `
                            <a href="#" class="list-group-item list-group-item-action product-card" data-category="others" data-id="${item.product_id}" data-name="${item.product_name}" data-price="${item.product_price}" data-image="${item.product_image}">
                                <div class="d-flex w-100 justify-content-between">
                                    <img src="../admin/dist/img/${item.product_image}" class="img-thumbnail me-3" alt="${item.product_name}" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">${item.product_name}</h5>
                                        <p class="mb-1">${item.product_description}</p>
                                        <small class="text-muted">Price: ₱${item.product_price}</small>
                                        <div class="quantity">
                                            <button class="btn btn-sm btn-secondary decrease-btn">-</button>
                                            <span class="quantity-text">0</span>
                                            <button class="btn btn-sm btn-secondary increase-btn">+</button>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#othersSection').html(othersContent);
                    $('#othersSectionWrapper').show();
                } else {
                    $('#othersSectionWrapper').hide();
                }

                // Services Section (Only one can be selected)
                if (response.services && response.services.length > 0) {
                    let servicesContent = '';
                    response.services.forEach(item => {
                        servicesContent += `
                            <a href="#" class="list-group-item list-group-item-action service-card product-card" data-category="services" data-id="${item.id}" data-name="${item.service_name}" data-description="${item.service_description}" data-price="${item.service_price}" data-image="${item.service_picture}">
                                <div class="d-flex w-100 justify-content-between">
                                    <img src="../admin/dist/img/${item.service_picture}" class="img-thumbnail me-3" alt="${item.service_name}" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">${item.service_name}</h5>
                                        <p class="mb-1">${item.service_description}</p>
                                        <small class="text-muted">Price: ₱${item.service_price}</small>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#servicesSection').html(servicesContent);
                    $('#servicesSectionWrapper').show();
                } else {
                    $('#servicesSectionWrapper').hide();
                }

                // Show the modal
                new bootstrap.Modal(document.getElementById('dynamicModal')).show();
            },
            error: function () {
                alert('Failed to load data.');
            }
        });
    }

// Handling product card clicks (quantity increase logic)
$(document).on('click', '.product-card', function (e) {
    const category = $(this).data('category');
    const id = $(this).data('id');
    const name = $(this).data('name');
    const price = $(this).data('price');
    const image = $(this).data('image');

    if (category !== 'services') {
        if (!cart[category]) {
            cart[category] = {};
        }

        if (!cart[category][id]) {
            cart[category][id] = { id, name, price, image, quantity: 0 };
        }

        cart[category][id].quantity++;
        $(this).find('.quantity-text').text(cart[category][id].quantity);  // Update the displayed quantity
    } else {
        // For services, only one can be selected
        if (!cart[category]) {
            cart[category] = {};
        }

        if (!cart[category][id]) {
            cart[category][id] = { id, name, price, image, quantity: 1 };
        }

        // Toggle selection for services (change border color)
        $(this).toggleClass('selected');
    }

    // Prevent the modal from closing when quantity is clicked
    e.stopPropagation();
});

// Handling increase and decrease button actions (DO NOT trigger form submission here)
$(document).on('click', '.increase-btn, .decrease-btn', function (e) {
    e.stopPropagation(); // Prevent the modal from closing when quantity control buttons are clicked

    const $card = $(this).closest('.product-card');
    const category = $card.data('category');
    const id = $card.data('id');

    if (!cart[category]) {
        cart[category] = {};
    }

    if (!cart[category][id]) {
        cart[category][id] = { id, name: $card.data('name'), price: $card.data('price'), image: $card.data('image'), quantity: 0 };
    }

    const quantityElement = $card.find('.quantity-text');

    if ($(this).hasClass('increase-btn')) {
        cart[category][id].quantity++;
    } else if ($(this).hasClass('decrease-btn') && cart[category][id].quantity > 0) {
        cart[category][id].quantity--;
    }

    quantityElement.text(cart[category][id].quantity);  // Update the quantity display
});

// Submit button click logic with validation
$('.btn-primary.mt-3').on('click', function (e) {
    e.preventDefault(); // Prevent the default form submission

    // Retrieve the reservationId dynamically from the modal or context (it should be passed correctly)
    let reservationId = $('#dynamicModal').data('reservationId');  // Assuming you're setting reservationId when opening the modal

    // Check if cart is empty
    if (Object.keys(cart).length === 0 || Object.values(cart).every(category => Object.keys(category).length === 0)) {
        alert('Your cart is empty. Please select items.');
        return;
    }

    // Calculate total price and prepare the summary
    let totalPrice = 0;
    let summary = '';
    let products = [];
    let services = [];

    // Loop through cart items to calculate total and prepare the summary
    Object.keys(cart).forEach(category => {
        Object.keys(cart[category]).forEach(id => {
            const item = cart[category][id];
            if (category !== 'services') {
                products.push(`${item.name} (₱${item.price} x ${item.quantity})`);
                totalPrice += item.price * item.quantity;
            } else {
                services.push(`${item.name} (₱${item.price})`);
                totalPrice += item.price;
            }
        });
    });

    summary += 'Products:\n' + products.join('\n') + '\n\n';
    summary += 'Services:\n' + services.join('\n') + '\n\n\n';
    summary += `Total Price: ₱${totalPrice}`;

    // Show confirmation dialog with summary
    if (confirm(`Please review your requests:\n\n${summary}\n\nProceed with your requests?`)) {
        // Generate a random 6-digit request_id
        const requestId = Math.floor(100000 + Math.random() * 900000);

        // Perform insertion to MySQL (example)
        $.ajax({
            url: 'insert_request.php',
            method: 'POST',
            data: {
                request_id: requestId,
                reservation_id: reservationId,
                products: JSON.stringify(products),
                services: JSON.stringify(services),
                total_price: totalPrice,
                status: 'pending'
            },
            success: function (response) {
                alert('Request submitted successfully!');
                location.reload();
            },
            error: function () {
                alert('Error while submitting request.');
            }
        });
    }
});


</script>

<style>
/* Scoped styling for the dynamic request modal */
#dynamicModal .modal-content {
    border-radius: 10px;
}

#dynamicModal .product-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-radius: 10px;
    cursor: pointer;
    margin-bottom: 15px;
    border: 1px solid #ddd;
}

#dynamicModal .product-card.selected {
    border: 2px solid #888; /* Gray color indicating the service is selected */
}

#dynamicModal .product-card img {
    border-radius: 10px;
    width: 80px;
    height: 80px;
    object-fit: cover;
}

#dynamicModal .section-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    padding: 10px 0;
    border-bottom: 2px solid #888; /* Gray color */
}

#dynamicModal .section {
    margin-bottom: 30px;
}

#dynamicModal .modal-body {
    padding: 30px;
}

#dynamicModal .btn-primary {
    margin-top: 20px;
    width: 100%;
}

#dynamicModal .list-group-item {
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#dynamicModal .list-group-item:hover {
    background-color: #f8f9fa;
}

#dynamicModal .quantity {
    font-size: 14px;
    margin-top: 5px;
}

#dynamicModal .quantity-text {
    font-weight: bold;
}

#dynamicModal .decrease-btn,
#dynamicModal .increase-btn {
    margin: 0 5px;
}

/* Hide section title if no data is available */
#dynamicModal .section:empty .section-title {
    display: none;
}

/* Styling for the special message textarea */
#dynamicModal .form-group textarea {
    resize: vertical;
}

/* Gray color for borders, text and buttons */
#dynamicModal .modal-header .modal-title {
    color: #333; /* Dark gray */
}

#dynamicModal .modal-footer button {
    background-color: #888; /* Gray button color */
    border: none;
}

#dynamicModal .modal-footer button:hover {
    background-color: #777;
}


</style>




<script>
    document.addEventListener("DOMContentLoaded", function () {
        const calendar = document.getElementById("calendar");
        const currentMonth = document.getElementById("currentMonth");
        const prevMonth = document.getElementById("prevMonth");
        const nextMonth = document.getElementById("nextMonth");

        let date = new Date();
        let reservations = <?php echo json_encode($reservations); ?>; // Fetch reservations from PHP

        function formatDateString(dateObj) {
            let year = dateObj.getFullYear();
            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
            let day = String(dateObj.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            calendar.innerHTML = "";
            let firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
            let lastDate = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            currentMonth.textContent = date.toLocaleDateString("en-US", { month: "long", year: "numeric" });

            let reservationDates = {};

            reservations.forEach(res => {
                let checkIn = new Date(res.check_in);
                let checkOut = new Date(res.check_out);
                let status = res.reservation_status;
                let reservationId = res.reservation_id;

                for (let d = new Date(checkIn); d <= checkOut; d.setDate(d.getDate() + 1)) {
                    let formattedDate = formatDateString(d);
                    if (formattedDate === formatDateString(checkIn)) {
                        reservationDates[formattedDate] = { status, type: "check-in", id: reservationId };
                    }
                }
            });

            for (let i = 0; i < firstDay; i++) {
                let emptyCell = document.createElement("div");
                emptyCell.classList.add("calendar-cell", "empty");
                calendar.appendChild(emptyCell);
            }

            for (let i = 1; i <= lastDate; i++) {
                let dayCell = document.createElement("div");
                dayCell.classList.add("calendar-cell");
                dayCell.textContent = i;

                let cellDate = formatDateString(new Date(date.getFullYear(), date.getMonth(), i));

                if (reservationDates[cellDate]) {
                    let { status, type, id: reservation_id } = reservationDates[cellDate];
                    dayCell.classList.add(type, status);
                    dayCell.addEventListener("click", function () {
                        showReservationModal(status, reservation_id);
                    });
                }

                calendar.appendChild(dayCell);
            }
        }

        function showReservationModal(status, reservationId) {
            let modalTitle = document.getElementById("modalTitle");
            let modalButtons = document.getElementById("modalButtons");
            modalTitle.textContent = "Reservation Details";
            modalButtons.innerHTML = "";

            let buttons = [
                { text: "View Reservation", class: "btn-primary", action: () => showFullReservationDetails(reservationId) }
            ];

            if (status === "confirmed") {
                buttons.push(
                    { text: "Extend Reservation", class: "btn-primary", action: () => showExtendModal(reservationId) },
                    { text: "Request Foods & Beverages", class: "btn-primary", action: () => showRequestModal(reservationId) },
                    { text: "Withdraw Reservation", class: "btn-danger", action: () => deleteReservation(reservationId) }
                );
            } else if (status === "pending") {
                buttons.push({ text: "Cancel Reservation", class: "btn-danger", action: () => deleteReservation(reservationId) });
            } else if (status === "cancelled") {
                buttons.push({ text: "Remove Reservation", class: "btn-danger", action: () => deleteReservation(reservationId) });
            }

            buttons.forEach(btn => {
                let button = document.createElement("button");
                button.classList.add("btn", btn.class);
                button.textContent = btn.text;
                if (btn.action) {
                    button.addEventListener("click", btn.action);
                }
                modalButtons.appendChild(button);
            });

            new bootstrap.Modal(document.getElementById("reservationModal")).show();
        }

        function showFullReservationDetails(reservationId) {
            fetch("fetch_reservation_details.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ reservation_id: reservationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    let details = data.data; // Access the correct object

                    // Construct the correct image path
                    let imagePath = details.room_picture 
                        ? `../admin/dist/img/${details.room_picture}`
                        : "./dist/img/default-image.jpg"; // Use default image if empty

                    document.getElementById("roomImage").src = imagePath;
                    document.getElementById("roomCategory").textContent = details.room_category || "N/A";
                    document.getElementById("roomName").textContent = details.room_name || "N/A";
                    document.getElementById("roomNumber").textContent = details.room_number || "N/A";
                    document.getElementById("roomDescription").textContent = details.room_description || "N/A";
                    document.getElementById("roomPrice").textContent = `₱${(details.room_price || 0).toFixed(2)}`;

                    document.getElementById("checkInDate").textContent = details.check_in || "N/A";
                    document.getElementById("checkOutDate").textContent = details.check_out || "N/A";
                    document.getElementById("paymentMethod").textContent = details.payment_method || "N/A";
                    document.getElementById("totalPrice").textContent = `₱${(details.total_price || 0).toFixed(2)}`;
                    document.getElementById("reservationType").textContent = details.type || "N/A";
                    document.getElementById("reservationStatus").textContent = details.reservation_status || "N/A";

                    new bootstrap.Modal(document.getElementById("reservationDetailsModal")).show();
                } else {
                    alert("Error fetching reservation details: " + data.message);
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                alert("Something went wrong.");
            });
        }


        function deleteReservation(reservationId) {
            if (confirm("Are you sure you want to delete this reservation?")) {
                fetch("delete_reservation.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ reservation_id: reservationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Fetch Error:", error);
                    alert("Something went wrong.");
                });
            }
        }



        prevMonth.addEventListener("click", function () {
            date.setMonth(date.getMonth() - 1);
            renderCalendar();
        });

        nextMonth.addEventListener("click", function () {
            date.setMonth(date.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
    });
</script>




<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Make sure the modal exists in the DOM
        const fullReservationModalElement = document.getElementById('fullReservationModal');
        
        if (fullReservationModalElement) {
            const fullReservationModal = new bootstrap.Modal(fullReservationModalElement);

            // Select the close buttons (X and Close buttons)
            const closeButtons = document.querySelectorAll('.btn-close, [data-bs-dismiss="modal"]');
            
            // Attach event listeners to each close button
            closeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    fullReservationModal.hide(); // Hide the modal
                });
            });

            // Optional: Close the modal when clicking outside of it
            fullReservationModalElement.addEventListener('click', function (event) {
                if (event.target === fullReservationModalElement) {
                    fullReservationModal.hide(); // Hide the modal when clicking outside of it
                }
            });
        } else {
            console.error('Modal element not found!');
        }
    });
</script>



<!-- Extend Reservation Modal -->
<div class="modal fade" id="extendReservationModal" tabindex="-1" aria-labelledby="extendReservationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="extendReservationModalLabel">Extend Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Reservation Form -->
                <form id="extendReservationForm">
                    <!-- Reservation ID (Hidden) -->
                    <input type="hidden" id="extendReservationId" name="reservation_id">

                    <!-- Room ID (Hidden) -->
                    <input type="hidden" id="extendRoomId" name="room_id">

                    <!-- Total Price (Hidden) -->
                    <input type="hidden" id="extendTotalPrice" name="total_price">

                    <!-- Check-in Date (Readonly Textbox) -->
                    <div class="mb-3">
                        <label for="extendCheckInDate" class="form-label">Check-in Date</label>
                        <input type="text" id="extendCheckInDate" class="form-control" readonly>
                    </div>

                    <!-- Check-out Date (Date input) -->
                    <div class="mb-3">
                        <label for="extendCheckOutDate" class="form-label">Check-out Date</label>
                        <input type="date" id="extendCheckOutDate" class="form-control" name="check_out_date">
                    </div>

                    <!-- Extend Button -->
                    <div class="d-grid gap-2">
                        <button type="button" id="extendButton" class="btn btn-primary">Extend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Function to show the Extend Reservation Modal and populate data
function showExtendModal(reservationId) {
  // Fetch reservation details based on reservation ID
  fetchReservationDetails(reservationId)
    .then(() => {
      // Show the modal after populating data
      $('#extendReservationModal').modal('show');
    })
    .catch(error => {
      console.error("Error fetching reservation details:", error);
      alert("Failed to load reservation details. Please try again.");
    });
}

// Function to fetch reservation details from the server
function fetchReservationDetails(reservationId) {
  return fetch('fetch_reservation.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ reservation_id: reservationId }),
  })
  .then(response => response.json())
  .then(data => {
    console.log("Fetched reservation data:", data);

    if (data && data.check_in && data.check_out && data.room_id && data.total_price) {
      // Populate modal fields with the fetched data
      document.getElementById('extendReservationId').value = reservationId;  // Hidden field for reservation ID
      document.getElementById('extendRoomId').value = data.room_id;  // Hidden field for room ID
      document.getElementById('extendTotalPrice').value = data.total_price;  // Hidden field for total price
      document.getElementById('extendCheckInDate').value = data.check_in;  // Populate check-in date (readonly)
      document.getElementById('extendCheckOutDate').value = data.check_out;  // Populate check-out date (date input)
    } else {
      console.error("Invalid reservation data received:", data);
      throw new Error("Invalid reservation data");
    }
  })
  .catch(error => {
    console.error('Error fetching reservation details:', error);
    throw error;  // Ensure error propagates up
  });
}

// Handle Extend Button click
document.getElementById('extendButton').addEventListener('click', function () {
  const reservationId = document.getElementById('extendReservationId').value;
  const roomId = document.getElementById('extendRoomId').value; // Getting the value from the hidden input field
  const currentCheckOutDate = document.getElementById('extendCheckOutDate').value;
  const checkInDate = document.getElementById('extendCheckInDate').value; // Check-in date from modal

  // Function to fetch the room price from the get_room_price.php script
  function fetchRoomPrice(roomId) {
    return fetch('get_room_price.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ room_id: roomId })  // Send the room_id from the hidden field
    })
    .then(response => response.json())
    .then(data => {
      // Debugging log: check what data is returned from the backend
      console.log("Response from get_room_price.php:", data);

      if (data.success && data.room_price) {
        return parseFloat(data.room_price); // Parse as float for calculation
      } else {
        throw new Error(data.error || 'Room price not found');
      }
    })
    .catch(error => {
      console.error("Error fetching room price:", error);
      throw error;  // Ensure error propagates up
    });
  }

  // Show Confirmation Summary in an Alert
  fetchRoomPrice(roomId)  // Use the hidden roomId here
    .then(roomPrice => {
      // Debugging log: check the fetched room price
      console.log("Fetched room price:", roomPrice);

      // Parse dates
      const checkIn = new Date(checkInDate);  // Get the Check-in Date
      const currentCheckOut = new Date(currentCheckOutDate); // Current Check-out Date
      const newCheckOut = new Date(currentCheckOutDate); // Use the current check-out as the base for extension

      // Let's say we are adding the number of days to the current check-out date to make it the new check-out date.
      // You could dynamically change this based on user input for the extension (e.g., add 5 days or user input)
      const extensionDays = 0;  // This can be dynamically calculated or input from the user.
      newCheckOut.setDate(currentCheckOut.getDate() + extensionDays);  // Extend by 5 days (for example)

      // Calculate the interval between Check-in Date and New Check-out Date in days
      const intervalInMilliseconds = newCheckOut - checkIn;
      const extendedDays = Math.ceil(intervalInMilliseconds / (1000 * 3600 * 24));

      // Calculate the new total price based on the extended days
      const newTotalPrice = roomPrice * extendedDays;

      // Debugging log: verify the new total price
      console.log("New total price calculation:", roomPrice, extendedDays, newTotalPrice);

      // Prepare confirmation message
      const confirmationMessage = 
        `Are you sure you want to extend your reservation?\n\n` +
        `Check-in Date: ${checkInDate}\n\n` +
        `Current Check-out Date: ${currentCheckOutDate}\n` +
        `New Check-out Date: ${newCheckOut.toLocaleDateString()}\n\n` +
        `Current Total Price: ₱${parseFloat(document.getElementById('extendTotalPrice').value).toFixed(2)}\n` +
        `New Total Price: ₱${newTotalPrice.toFixed(2)}`;

      // Show confirmation alert with summary
      const confirmExtension = confirm(confirmationMessage);

      if (confirmExtension) {
        // Proceed with extending the reservation in the database
        updateReservation(reservationId, newCheckOut.toISOString().split('T')[0], newTotalPrice);
      }
    })
    .catch(error => {
      alert('Error fetching room price');
    });
});

// Function to update the reservation in the backend (for example, saving the new check-out date and total price)
function updateReservation(reservationId, newCheckOutDate, newTotalPrice) {
  const formData = new FormData();
  formData.append('reservation_id', reservationId);
  formData.append('new_check_out_date', newCheckOutDate);
  formData.append('new_total_price', newTotalPrice);

  // Send the request to update the reservation
  fetch('extend_online_reservation.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
        alert('Reservation extended successfully.');
        
        // Refresh the current page
        location.reload();

        // After the page reloads, redirect to 'booking_calendar.php'
        setTimeout(() => {
        window.location.href = 'booking_calendar.php?#';
        }, 100);  // Delay to allow the page to reload before redirecting
    } else {
        alert('There was an error updating the reservation.');
    }
    })

  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while processing your request.');
  });
}


</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const checkOutInput = document.getElementById("extendCheckOutDate");

    // Function to disable past dates for Check-out Date based on fetched Check-out Date
    function disablePastFetchedCheckOutDate() {
        let fetchedCheckOutDate = checkOutInput.value; // Get populated check-out date
        
        if (fetchedCheckOutDate) {
            // Convert fetched date to YYYY-MM-DD format
            let minDate = new Date(fetchedCheckOutDate);
            let formattedMinDate = minDate.toISOString().split('T')[0];

            // Set min attribute to disable past dates
            checkOutInput.setAttribute("min", formattedMinDate);
        }
    }

    // Call function when the modal is opened
    document.getElementById("extendReservationModal").addEventListener("show.bs.modal", function () {
        disablePastFetchedCheckOutDate();
    });
});

</script>








</body>


</html>
