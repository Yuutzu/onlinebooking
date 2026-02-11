<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include('../admin/inc/email_2fa_helper.php');
require('../admin/inc/alert.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

$max_attempts = 3; // Number of allowed failed attempts

if (isset($_POST['login'])) {
    $email = $_POST['client_email'];
    $password = $_POST['client_password']; // No hashing

    // Check if the user exists (both Admin and User)
    $query = "SELECT id, client_name, client_email, client_password, client_status, failed_attempts, role, two_fa_enabled FROM clients WHERE client_email = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $result_email, $result_password, $status, $failed_attempts, $role, $two_fa_enabled);
    $stmt->fetch();
    $stmt->close();

    if ($result_email) {
        if ($result_password === $password) { // Direct comparison (no hashing)
            if ($role === "Admin") {
                // Admin Login (No blocking applied)
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_email'] = $result_email;

                echo "<script>
                    window.location.href = '../admin/dashboard.php';
                </script>";
                exit();
            } else {
                // User Login with Blocking System
                if ($status === "Blocked") {
                    alert("error", "Your account is blocked. Please contact administrator.");
                    exit();
                }

                if ($failed_attempts >= $max_attempts) {
                    alert("error", "Your account has been blocked due to multiple failed login attempts. Please contact support.");
                    exit();
                }

                // Reset failed attempts on successful login
                $resetAttemptsQuery = "UPDATE clients SET failed_attempts = 0, last_failed_attempt = NULL WHERE client_email = ?";
                $stmt = $mysqli->prepare($resetAttemptsQuery);
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->close();

                // Check if user has pending OTP verification
                if ($status === "Pending") {
                    $_SESSION['client_id'] = $id;
                    $_SESSION['client_name'] = $name;
                    $_SESSION['client_email'] = $result_email;
                    header("location: otp.php");
                    exit();
                }

                // Check if 2FA is enabled for activated accounts
                if ($status === "Activated" && $two_fa_enabled == 1) {
                    // Generate and send 2FA code
                    $code = generateEmailCode();

                    if (store2FACode($mysqli, $id, $code) && sendEmail2FACode($result_email, $name, $code)) {
                        // Store user ID in session for 2FA verification
                        $_SESSION['2fa_user_id'] = $id;
                        header("location: verify_email_2fa.php");
                        exit();
                    } else {
                        alert("error", "Failed to send verification code. Please try again.");
                        exit();
                    }
                }

                // Normal login - set session variables
                $_SESSION['client_id'] = $id;
                $_SESSION['client_name'] = $name;
                $_SESSION['client_email'] = $result_email;

                if ($status === "Activated") {
                    header("location: index.php");
                    exit();
                }
            }
        } else {
            // Handle failed login attempts for users only (not Admin)
            if ($role !== "Admin") {
                $failed_attempts++;
                $updateFailedAttemptsQuery = "UPDATE clients SET failed_attempts = ?, last_failed_attempt = NOW() WHERE client_email = ?";
                $stmt = $mysqli->prepare($updateFailedAttemptsQuery);
                $stmt->bind_param('is', $failed_attempts, $email);
                $stmt->execute();
                $stmt->close();

                // Block account if it reaches max attempts
                if ($failed_attempts >= $max_attempts) {
                    $blockAccountQuery = "UPDATE clients SET client_status = 'Blocked' WHERE client_email = ?";
                    $stmt = $mysqli->prepare($blockAccountQuery);
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $stmt->close();

                    alert("error", "Too many failed login attempts! Your account has been blocked.");
                } else {
                    alert("error", "Invalid credentials. Attempt $failed_attempts of $max_attempts.");
                }
            } else {
                alert("error", "Invalid admin credentials.");
            }
        }
    } else {
        alert("error", "Email not found.");
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>
</head>

<body style="background-color:#f0eeeb;">
    <div class="container-fluid">
        <div class="row" id="client-content">
            <div class="col-lg-8 m-auto d-flex align-items-center justify-content-center">
                <div class="card card-register" style="width:50rem;">
                    <div class="card-body p-0">

                        <div class="row d-flex">

                            <div class="col-lg-6 p-2 mt-4">
                                <div class="d-flex justify-content-center">
                                    <img src="./dist/img/logo2.png" style="width: 140px;">
                                </div>

                                <div class="container mt-4 mb-4">
                                    <h5 class="titleFont mb-0">Welcome Back</h5>
                                    <p class="someText">Please use your credentials to login.</p>
                                </div>

                                <div class="container">
                                    <form method="POST" enctype="multipart/form-data">

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Email Address</label>
                                            <input type="email" name="client_email"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Password</label>
                                            <input type="password" name="client_password"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <button type="submit" name="login"
                                                class="btn btn-primary btnAddCategory someText">Login</button>
                                        </div>
                                    </form>

                                    <!-- Forgot Password and Register Buttons -->
                                    <div class="d-flex justify-content-between mt-3">
                                        <a href="forgot_password.php"
                                            style="font-size: 1rem; color: #4a1c1d; text-decoration: none; padding-top: 5px;">Forgot
                                            Password?</a>
                                        <a href="register.php"
                                            style="font-size: 1rem; color: #4a1c1d; text-decoration: none; padding-top: 5px;">Register</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="imageContainer">
                                    <img src="./dist/img/login.jpg" class="registerImage" style="height: 450px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>