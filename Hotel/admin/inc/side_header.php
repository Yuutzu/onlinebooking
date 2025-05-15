<?php
// Include the database configuration file
require('../admin/config/config.php');

// Fetch the site logo from the database
$logoQuery = "SELECT site_logo FROM site_settings WHERE id = 0";
$result = $mysqli->query($logoQuery);
$logoPath = './dist/img/logos/default_logo.png'; // Default logo path

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['site_logo'])) {
        $logoPath = './dist/img/logos/' . $row['site_logo']; // Dynamically fetch logo
    }
}
?>

<!-- DASHBOARD CONTROLLER MENUS -->
<div class="col-lg-2" id="dashboard-menu">
    <div class="container d-flex align-items-center justify-content-center mt-0" style="width: 100%;">
        <img src="<?php echo $logoPath; ?>" alt="Site Logo" style="width: 180px;">
    </div>
    
    <nav class="navbar navbar-expand-lg mt-0">
        <div class="container-fluid flex-lg-column align-items-stretch text-white mt-5">
            <h5 class="mt-2 optionTitle">Management Console</h5>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminDropdown" aria-controls="adminDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
            </button>

            <div class="collapse navbar-collapse flex-column align-items-stretch mt-0" id="adminDropdown">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item main-item prefFont">
                        <div class="d-flex">
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="bi bi-speedometer me-2 iicon"></i>
                                <span>Dashboard</span>
                            </a>
                        </div>
                    </li>

                    <!-- HOTEL -->
                    <li class="nav-item prefFont">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#collapseHotel" role="button" aria-expanded="false">
                            <i class="bi bi-building me-2 iicon"></i>
                            Hotel
                        </a>
                        <div class="collapse" id="collapseHotel">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="category.php">Category</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="rooms.php">Rooms</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="products.php">Products</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="services.php">Services</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- RESERVATION -->
                    <li class="nav-item prefFont">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#collapseReservation" role="button" aria-expanded="false">
                            <i class="bi bi-calendar me-2 iicon"></i>
                            Reservation
                        </a>
                        <div class="collapse" id="collapseReservation">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="online_reservation.php">Online Reservation</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="walkin_reservation.php">Walk-in Reservation</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- PAYMENTS -->
                    <li class="nav-item prefFont">
                        <a class="nav-link text-white" href="payments.php">
                            <i class="bi bi-credit-card me-2 iicon"></i>
                            Payments
                        </a>
                    </li>

                    <!-- CUSTOMER SUPPORT -->
                    <li class="nav-item prefFont">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#collapseSupport" role="button" aria-expanded="false">
                            <i class="bi bi-envelope me-2 iicon"></i>
                            Customer Support
                        </a>
                        <div class="collapse" id="collapseSupport">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="requests.php">Requests</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="inquiry.php">Inquiry</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- ACCOUNTS -->
                    <li class="nav-item prefFont">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#collapseAccounts" role="button" aria-expanded="false">
                            <i class="bi bi-people me-2 iicon"></i>
                            Accounts
                        </a>
                        <div class="collapse" id="collapseAccounts">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="admin_accounts.php">Admin</a></li>
                                <li class="nav-item sub-item prefFont"><a class="nav-link text-white" href="clients.php">Clients</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <h5 class="mt-4 optionTitle">System Management</h5>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item prefFont mb-0">
                    <a class="nav-link text-white" href="admin_settings.php">
                        <i class="bi bi-sliders me-2 iicon"></i>
                        System Settings
                    </a>
                </li>

                <li class="nav-item prefFont">
                    <a class="nav-link text-white" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2 iicon"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('logoutBtn').addEventListener('click', function (event) {
        event.preventDefault(); // Prevent default behavior

        Swal.fire({
            title: "Logout Confirmation",
            text: "Are you sure you want to log out?",
            icon: "warning",
            width: "450px",  // Decreased width for a smaller modal
            padding: "1rem", // Reduce padding for a compact look
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                title: "swal-title",  // Custom class for styling
                popup: "swal-popup"  // Custom class for modal size
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../admin/admin_logout.php"; // Redirect to logout page
            }
        });
    });
</script>