<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_email'])) {
        $client_email = $_POST['client_email'];

        // Check if email exists in the database
        $stmt = $mysqli->prepare("SELECT id, client_name FROM clients WHERE client_email = ?");
        $stmt->bind_param('s', $client_email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($client_id, $client_name);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();

            // Generate OTP (6-digit code)
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $client_email;
            $_SESSION['client_id'] = $client_id;

            // Send OTP via email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'luxehavenhotelph@gmail.com';
                $mail->Password = 'lvlx qagp ojak ymsu';  // Use app password if 2FA is enabled
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('luxehavenhotelph@gmail.com', 'Luxe Haven Hotel PH Team');
                $mail->addAddress($client_email, $client_name);

                $mail->isHTML(true);
                $mail->Subject = 'Luxe Haven Hotel - Password Reset OTP';
                $mail->Body = "
                    Dear $client_name, <br><br>
                    Your One-Time Password (OTP) for password reset is: <b>$otp</b><br><br>
                    Please enter this OTP on the password reset page.<br><br>
                    If you did not request this, please ignore this email.<br><br>
                    Sincerely,<br>
                    Luxe Haven Hotel Team<br>
                    ***<i>This is an auto-generated email. DO NOT REPLY.</i>***
                ";

                $mail->send();
                alert('success', 'An OTP has been sent to your email. Please check your inbox.');

                $_SESSION['step'] = 'verify_otp';
            } catch (Exception $e) {
                alert('error', 'Email could not be sent.');
            }
        } else {
            alert('error', 'No account found with that email address.');
        }
    }

    if (isset($_POST['verify_otp'])) {
        $entered_otp = $_POST['otp'];

        if ($_SESSION['otp'] == $entered_otp) {
            alert('success', 'OTP Verified! You can now enter a new password.');
            $_SESSION['step'] = 'reset_password';
        } else {
            alert('error', 'Invalid OTP. Please try again.');
        }
    }

    if (isset($_POST['update_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $client_id = $_SESSION['client_id'];

        if ($new_password === $confirm_password) {
            // Update password (not hashed as per your request)
            $stmt = $mysqli->prepare("UPDATE clients SET client_password = ? WHERE id = ?");
            $stmt->bind_param('si', $new_password, $client_id);
            if ($stmt->execute()) {
                echo "<script>
                    alert('Your password has been updated. You can now log in.');
                    window.location.href = 'login.php';
                </script>";
                session_destroy(); // Clear session data
                exit();
            }
             else {
                alert('error', 'Error updating password.');
            }
        } else {
            alert('error', 'Passwords do not match.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                                    <h5 class="titleFont mb-0">Forgot Password</h5>
                                    <p class="someText">
                                        <?php 
                                        if (!isset($_SESSION['step'])) echo 'Please enter your email address to receive an OTP.';
                                        elseif ($_SESSION['step'] == 'verify_otp') echo 'Enter the OTP sent to your email.';
                                        elseif ($_SESSION['step'] == 'reset_password') echo 'Enter your new password.';
                                        ?>
                                    </p>
                                </div>

                                <div class="container">
                                    <form method="POST">
                                        <?php if (!isset($_SESSION['step'])) { ?>
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Email Address</label>
                                                <input type="email" name="client_email" class="form-control someText shadow-none" required>
                                            </div>
                                            <div class="mb-2 d-grid mt-3">
                                                <button type="submit" name="verify_email" class="btn btn-primary btnAddCategory someText">Send OTP</button>
                                            </div>
                                        <?php } elseif ($_SESSION['step'] == 'verify_otp') { ?>
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Enter OTP</label>
                                                <input type="text" name="otp" class="form-control someText shadow-none" required>
                                            </div>
                                            <div class="mb-2 d-grid mt-3">
                                                <button type="submit" name="verify_otp" class="btn btn-primary btnAddCategory someText">Verify OTP</button>
                                            </div>
                                        <?php } elseif ($_SESSION['step'] == 'reset_password') { ?>
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">New Password</label>
                                                <input type="password" name="new_password" class="form-control someText shadow-none" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Confirm Password</label>
                                                <input type="password" name="confirm_password" class="form-control someText shadow-none" required>
                                            </div>
                                            <div class="mb-2 d-grid mt-3">
                                                <button type="submit" name="update_password" class="btn btn-primary btnAddCategory someText">Update Password</button>
                                            </div>
                                        <?php } ?>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="imageContainer">
                                    <img src="./dist/img/meeting.jpg" class="registerImage" style="height: 450px;">
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
