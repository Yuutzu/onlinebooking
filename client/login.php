<?php
require_once('../admin/inc/SessionManager.php');
SessionManager::init();

include_once('../admin/config/config.php');
include_once('../admin/config/checklogin.php');
include_once('../admin/inc/email_2fa_helper.php');
include_once('../admin/inc/password_helper.php');
require_once('../admin/inc/alert.php');
require_once('../admin/inc/mailer_helper.php');

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
        // Verify password using secure hashing
        if (verifyPassword($password, $result_password)) {
            // Check if password needs rehashing (for upgraded algorithms)
            if (passwordNeedsRehash($result_password)) {
                $newHash = hashPassword($password);
                $updateQuery = "UPDATE clients SET client_password = ? WHERE id = ?";
                $stmt = $mysqli->prepare($updateQuery);
                $stmt->bind_param('si', $newHash, $id);
                $stmt->execute();
                $stmt->close();
            }

            if ($role === "Admin") {
                // Admin Login (No blocking applied)
                SessionManager::create($id, $name, [
                    'admin_id' => $id,
                    'admin_name' => $name,
                    'admin_email' => $result_email,
                    'role' => 'Admin'
                ]);

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
                    // Create session with OTP data
                    $otp = rand(100000, 999999);
                    SessionManager::create($id, $name, [
                        'client_id' => $id,
                        'client_name' => $name,
                        'client_email' => $result_email,
                        'otp' => $otp,
                        'otp_expiry' => time() + (5 * 60) // Valid for 5 minutes
                    ]);

                    try {
                        $mail = getMailer();
                        $mail->addAddress($result_email, $name);
                        $mail->Subject = 'Luxe Haven Team - One-Time Password (OTP)';
                        $mail->Body = "
                           <html>
                        <head>
                            <style>
                                body {
                                    font-family: 'Arial', sans-serif;
                                    color: #333333;
                                    background-color: #f0eeeb;
                                    margin: 0;
                                    padding: 0;
                                }
                                .container {
                                    width: 100%;
                                    max-width: 600px;
                                    margin: 0 auto;
                                    padding: 20px;
                                    background-color: #ffffff;
                                    border-radius: 8px;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }
                                h1, h2, h3 {
                                    color: #4a1c1d;
                                }
                                p {
                                    font-size: 16px;
                                    line-height: 1.5;
                                    color: #555555;
                                }
                                b {
                                    color: #4a1c1d;
                                }
                                .footer {
                                    font-size: 12px;
                                    color: #888888;
                                    text-align: center;
                                }
                                .footer i {
                                    font-style: italic;
                                }
                                .otp-code {
                                    font-weight: bold;
                                    font-size: 22px;
                                    letter-spacing: 4px;
                                    color: #d9534f;
                                    background-color: #f8f8f8;
                                    padding: 10px 16px;
                                    border-radius: 4px;
                                    display: inline-block;
                                    margin: 8px 0 16px;
                                }
                                .highlight {
                                    color: #4a1c1d;
                                    font-weight: bold;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h2>Dear Mr./Ms./Mrs. $name,</h2>

                                <p>Thank you for registering with <b>Luxe Haven Hotel</b>.</p>

                                <p>Your One-Time Password (OTP) for account activation is:</p>

                                <p class='otp-code'>$otp</p>

                                <p>This code is valid for the next <span class='highlight'>5 minutes</span>. For your security, please do not share this OTP with anyone.</p>

                                <p>If you did not request this code, you may safely ignore this email.</p>

                                <br>

                                <p>Sincerely,</p>
                                <p><b>LUXE HAVEN HOTEL MANAGEMENT</b></p>

                                <br>
                                <div class='footer'>
                                    <p>***<i>This is an auto-generated email. DO NOT REPLY.</i>***</p>
                                </div>
                            </div>
                        </body>
                    </html>
";
                        $mail->send();
                    } catch (Exception $e) {
                        // Log error but don't stop the flow, user can request OTP again
                    }

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

                // Normal login - set session variables with SessionManager
                SessionManager::create($id, $name, [
                    'client_id' => $id,
                    'client_name' => $name,
                    'client_email' => $result_email
                ]);

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