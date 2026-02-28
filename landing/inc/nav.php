<?php

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

if (isset($_SESSION['client_id'])) {
    $client_id = $_SESSION['client_id'];

    // Fetch client details securely
    $ret = "SELECT * FROM clients WHERE client_id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $client_data = $res->fetch_object();
}
?>

<div class="stick">
    <div class="row topContainer p-2">
        <div class="col-lg-12 d-flex align-items-center justify-content-between">
            <div style="color: #fff; font-size: 14px;">
                <i class="bi bi-telephone-fill me-2 iconnav"></i>
                <?php echo htmlspecialchars($settings['site_contact']); ?> | Book Now and Call Us!
            </div>

            <div>
                <?php if (isset($client_data)): ?>
                    <!-- Show client info if logged in -->
                    <div class="d-flex align-items-center">
                        <img src="../../admin/dist/img/<?php echo $client_data->client_picture ?: "avatar.jpg"; ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                        <span class="text-light someText"><?php echo htmlspecialchars($client_data->client_name); ?></span>
                        <a id="logoutBtn" class="btn btn-danger btn-sm ms-3" href="javascript:void(0);">Logout</a>
                    </div>
                <?php else: ?>
                    <!-- Show login/register buttons if not logged in -->
                    <a class="btn btn-primary btn-sm someText clientLoginButton" href="login.php">Login</a>
                    <a class="btn btn-primary btn-sm someText clientRegisterButton" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg bg-body-tertiary mainNav">
        <div>
            <img src="../../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_logo']); ?>" style="width: 130px;" alt="Site Logo">
        </div>

        <div class="collapse navbar-collapse d-flex justify-content-end" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Rooms
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        $ret = "SELECT * FROM room_category";
                        $stmt = $mysqli->prepare($ret);
                        $stmt->execute();
                        $res = $stmt->get_result();

                        while ($row1 = $res->fetch_object()) {
                            echo '<li><a class="dropdown-item someText" href="./client/room_category.php?category_name=' . htmlspecialchars($row1->category_name) . '">' . htmlspecialchars($row1->category_name) . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php#aboutus">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php#contactus">Contact</a>
                </li>

                <?php if (isset($client_data)): ?>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="profile.php" href="./client/profile.php">Profile</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="./client/booking_calendar.php?#">Booking</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</div>

<!-- Include SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Logout button click event
    document.getElementById('logoutBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Log out',
            text: "Are you sure you want to log out?",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: 'No',
            confirmButtonText: 'Yes',
            customClass: {
                confirmButton: 'btn btn-danger', // Red button class for "Yes"
                cancelButton: 'btn btn-secondary' // Optional: You can add a custom class for "No"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = './client/logout.php';  // Redirect to logout page
            }
        });
    });
</script>
