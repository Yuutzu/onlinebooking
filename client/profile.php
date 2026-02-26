<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include_once('../admin/inc/FileUploadHandler.php');
require_once('../admin/inc/CSRFToken.php');
require('../admin/inc/alert.php');

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

$client_id = $_SESSION['client_id'];

// Fetch client details
$ret = "SELECT * FROM clients WHERE id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    $uploadHandler = new FileUploadHandler('../admin/dist/img/');

    // Handle Profile Picture Update
    if (isset($_FILES['client_picture']) && $_FILES['client_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['client_picture']);
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            exit;
        }
        $client_picture = $result['filename'];
    } else {
        $client_picture = $row->client_picture;
    }

    // Handle Password Update
    if (isset($_POST['client_password']) && !empty($_POST['client_password'])) {
        $client_password = password_hash($_POST['client_password'], PASSWORD_BCRYPT);
    } else {
        $client_password = $row->client_password;
    }

    // Update Profile Information
    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];

    $update = "UPDATE clients SET 
        client_name = ?, 
        client_phone = ?, 
        client_email = ?, 
        client_password = ?, 
        client_picture = ? 
        WHERE id = ?";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param('sssssi', $client_name, $client_phone, $client_email, $client_password, $client_picture, $client_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> | Profile</title>

    <!-- Favicon -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png"
            href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>

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

    <!-- Sidebar & Navigation -->
    <?php require('./inc/nav.php'); ?>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-12"> <!-- Changed from col-lg-10 ms-auto to col-lg-12 -->

                <!-- Profile Card -->
                <div class="card shadow-sm p-4">
                    <div class="row">
                        <!-- Profile Picture -->
                        <div class="col-md-4 text-center">
                            <img src="../admin/dist/img/<?php echo $row->client_picture ?: 'avatar.jpg'; ?>"
                                class="rounded-circle img-fluid"
                                style="width: 150px; height: 150px; object-fit: cover;">
                        </div>

                        <!-- Profile Details -->
                        <div class="col-md-8">
                            <h4 class="mb-3"><?php echo $row->client_name; ?></h4>
                            <p><strong>Client ID:</strong> <?php echo $row->client_id; ?></p>
                            <p><strong>Presented ID:</strong> <?php echo $row->client_presented_id; ?></p>
                            <p><strong>Phone:</strong> <?php echo $row->client_phone; ?></p>
                            <p><strong>Email:</strong> <?php echo $row->client_email; ?></p>
                            <!-- Status Badge -->
                            <p>
                                <strong>Status:</strong>
                                <span class="badge 
                                <?php echo ($row->client_status == 'Activated') ? 'bg-success' :
                                    (($row->client_status == 'Pending') ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                    <?php echo $row->client_status; ?>
                                </span>
                            </p>

                            <p><strong>Role:</strong> <?php echo $row->role; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile & Update Password in Columns -->
                <div class="row mt-4">
                    <!-- Edit Profile Card -->
                    <div class="col-md-7"> <!-- Adjusted width to better use space -->
                        <div class="card shadow-sm p-4">
                            <h5 class="mb-3">Edit Profile</h5>
                            <form action="profile_edit.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="client_id" value="<?php echo $row->id; ?>">

                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Admin Name</label>
                                        <input type="text" class="form-control" name="client_name"
                                            value="<?php echo $row->client_name; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Presented ID</label>
                                        <input type="text" class="form-control" name="client_presented_id"
                                            value="<?php echo $row->client_presented_id; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">ID Number</label>
                                        <input type="text" class="form-control" name="client_id_number"
                                            value="<?php echo $row->client_id_number; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="client_phone"
                                            value="<?php echo $row->client_phone; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="client_email"
                                            value="<?php echo $row->client_email; ?>" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Profile Picture</label>
                                        <input type="file" class="form-control" name="client_picture">
                                        <input type="hidden" name="existing_image"
                                            value="<?php echo $row->client_picture; ?>">
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">ID Image</label>
                                        <input type="file" class="form-control" name="client_id_picture">
                                        <input type="hidden" name="existing_id_image"
                                            value="<?php echo $row->client_id_picture; ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-4"
                                    onclick="confirmUpdate(event, 'Are you sure you want to update your profile?')">Update
                                    Profile</button>
                            </form>
                        </div>
                    </div>

                    <!-- Update Password Card -->
                    <div class="col-md-5"> <!-- Adjusted width to align properly -->
                        <div class="card shadow-sm p-4 mb-3">
                            <h5 class="mb-3">Update Password</h5>
                            <form action="update_password.php" method="POST">
                                <input type="hidden" name="client_id" value="<?php echo $row->id; ?>">

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

                                <button type="submit" class="btn btn-primary mt-4"
                                    onclick="confirmUpdate(event, 'Are you sure you want to update your password?')">Update
                                    Password</button>
                            </form>
                        </div>

                        <!-- Two-Factor Authentication Card -->
                        <div class="card shadow-sm p-4 mt-3">
                            <h5 class="mb-3">🔐 Account Security</h5>
                            <p class="text-muted" style="font-size: 0.9rem;">
                                Secure your account with email-based two-factor authentication
                            </p>
                            <a href="email_2fa_settings.php" class="btn btn-outline-primary">
                                <i class="fas fa-shield-alt"></i> Manage Two-Factor Authentication
                            </a>
                        </div>
                    </div>
                </div>

                <br>

            </div>
        </div>
    </div>


</body>

</html>