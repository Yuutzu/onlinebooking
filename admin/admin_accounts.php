<?php
session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');

// Fetch Admin Details
$ret = "SELECT id, client_id, client_name, client_presented_id, client_id_picture, client_id_number, client_phone, client_email, role, client_status, client_picture FROM clients WHERE role = 'Admin'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
$admin = $res->fetch_object();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Admin Profile</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        function confirmUpdate(event, message) {
            event.preventDefault();
            if (confirm(message)) {
                event.target.closest("form").submit();
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
                
                <!-- Breadcrumbs -->
                <div class="mb-3 mt-4">
                    <h5 class="titleFont mb-1">Admin Profile</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item linkFont">
                                <a href="#" class="text-decoration-none" style="color: #333;">Admin Dashboard</a>
                            </li>
                            <li class="breadcrumb-item linkFont active" aria-current="page">
                                Admin Profile
                            </li>
                        </ol>
                    </nav>
                </div>

                <!-- Profile Card -->
                <div class="card shadow-sm p-4">
                    <div class="row">
                        <!-- Profile Picture -->
                        <div class="col-md-4 text-center">
                            <img src="./dist/img/<?php echo $admin->client_picture; ?>" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>

                        <!-- Profile Details -->
                        <div class="col-md-8">
                            <h4 class="mb-3"><?php echo $admin->client_name; ?></h4>

                            <p><strong>Admin ID:</strong> <?php echo $admin->client_id; ?></p>
                            <p><strong>Presented ID:</strong> <?php echo $admin->client_presented_id; ?></p>
                            <p><strong>Phone:</strong> <?php echo $admin->client_phone; ?></p>
                            <p><strong>Email:</strong> <?php echo $admin->client_email; ?></p>

                            <!-- Status Badge -->
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge 
                                    <?php echo ($admin->client_status == 'Activated') ? 'bg-success' : 
                                              (($admin->client_status == 'Pending') ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                    <?php echo $admin->client_status; ?>
                                </span>
                            </p>

                            <p><strong>Role:</strong> <?php echo $admin->role; ?></p>
                        </div>

                    </div>
                </div>

                <!-- Edit Profile & Update Password in Columns -->
                <div class="row mt-4">
                    <!-- Edit Profile Card -->
                    <div class="col-md-6">
                        <div class="card shadow-sm p-4">
                            <h5 class="mb-3">Edit Profile</h5>
                            <form action="update_admin.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="client_id" value="<?php echo $admin->id; ?>">

                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Admin Name</label>
                                        <input type="text" class="form-control" name="client_name" value="<?php echo $admin->client_name; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Presented ID</label>
                                        <input type="text" class="form-control" name="client_presented_id" value="<?php echo $admin->client_presented_id; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">ID Number</label>
                                        <input type="text" class="form-control" name="client_id_number" value="<?php echo $admin->client_id_number; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="client_phone" value="<?php echo $admin->client_phone; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="client_email" value="<?php echo $admin->client_email; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Profile Picture</label>
                                        <input type="file" class="form-control" name="client_picture">
                                        <input type="hidden" name="existing_image" value="<?php echo $admin->client_picture; ?>">
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">ID Image</label>
                                        <input type="file" class="form-control" name="client_id_picture">
                                        <input type="hidden" name="existing_id_image" value="<?php echo $admin->client_id_picture; ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-4" onclick="confirmUpdate(event, 'Are you sure you want to update your profile?')">Update Profile</button>
                            </form>
                        </div>
                    </div>

                    <!-- Update Password Card -->
                    <div class="col-md-6">
                        <div class="card shadow-sm p-4">
                            <h5 class="mb-3">Update Password</h5>
                            <form action="update_password.php" method="POST">
                                <input type="hidden" name="client_id" value="<?php echo $admin->id; ?>">

                                <div class="col-md-12">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>

                                <button type="submit" class="btn btn-danger mt-4" onclick="confirmUpdate(event, 'Are you sure you want to update your password?')">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
