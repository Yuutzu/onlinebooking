<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include_once('../admin/inc/password_helper.php');
require('../admin/inc/alert.php');
require_once('../admin/inc/mailer_helper.php');

// Track password reset attempts (rate limiting)
$reset_attempts_key = 'reset_attempts_' . md5($_SERVER['REMOTE_ADDR']);
$reset_attempts = isset($_SESSION[$reset_attempts_key]) ? $_SESSION[$reset_attempts_key] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_email'])) {
        $client_email = $_POST['client_email'];

        // Rate limiting: Max 3 reset requests per hour
        if ($reset_attempts >= 3) {
            alert('error', 'Too many password reset attempts. Please try again after 1 hour.');
            $_SESSION['step'] = 'rate_limited';
        } else {
            // Check if email exists in the database
            $stmt = $mysqli->prepare("SELECT id, client_name FROM clients WHERE client_email = ?");
            $stmt->bind_param('s', $client_email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($client_id, $client_name);

            if ($stmt->num_rows > 0) {
                $stmt->fetch();

                // Generate secure reset token
                $token = generatePasswordResetToken($mysqli, $client_id);

                if ($token) {
                    // Build reset link
                    $reset_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . "/client/reset_password_token.php?token=" . urlencode($token) . "&user=" . urlencode($client_id);

                    // Send reset link via email
                    try {
                        $mail = getMailer();
                        $mail->addAddress($client_email, $client_name);
                        $mail->Subject = 'Luxe Haven Hotel - Password Reset Link';
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
                                .reset-button {
                                    display: inline-block;
                                    background-color: #4a1c1d;
                                    color: white;
                                    padding: 12px 24px;
                                    border-radius: 5px;
                                    text-decoration: none;
                                    margin: 20px 0;
                                }
                                .reset-button:hover {
                                    background-color: #3a1419;
                                }
                                .warning {
                                    background-color: #fff3cd;
                                    border: 1px solid #ffc107;
                                    padding: 10px;
                                    border-radius: 4px;
                                    margin: 15px 0;
                                }
                                .footer {
                                    font-size: 12px;
                                    color: #888888;
                                    text-align: center;
                                    margin-top: 30px;
                                }
                                .footer i {
                                    font-style: italic;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h2>Dear Mr./Ms./Mrs. $client_name,</h2>

                                <p>We received a request to reset your <b>Luxe Haven Hotel</b> account password.</p>

                                <p><strong>Click the link below to reset your password:</strong></p>

                                <p>
                                    <a href='$reset_url' class='reset-button'>Reset Password</a>
                                </p>

                                <div class='warning'>
                                    <strong>⚠️ Security Note:</strong><br>
                                    This link is valid for <b>1 hour only</b>. After that, you'll need to request a new password reset.<br>
                                    Never share this link with anyone.
                                </div>

                                <p>Or copy and paste this link in your browser:</p>
                                <p style='word-break: break-all; background-color: #f8f8f8; padding: 10px; border-radius: 4px;'>
                                    $reset_url
                                </p>

                                <p>If you didn't request this password reset, please ignore this email and your password will remain unchanged.</p>

                                <p>If you have any questions, contact our support team.</p>

                                <br>

                                <p>Sincerely,</p>
                                <p><b>LUXE HAVEN HOTEL MANAGEMENT</b></p>

                                <div class='footer'>
                                    <p>***<i>This is an auto-generated email. DO NOT REPLY.</i>***</p>
                                </div>
                            </div>
                        </body>
                    </html>
";
                        $mail->send();

                        // Increment reset attempts
                        $_SESSION[$reset_attempts_key] = $reset_attempts + 1;

                        alert('success', 'A password reset link has been sent to your email. Please check your inbox. The link is valid for 1 hour.');
                        $_SESSION['email_verified'] = $client_email;
                        $_SESSION['step'] = 'check_email';
                    } catch (Exception $e) {
                        alert('error', 'Failed to send reset email. Please try again later.');
                        error_log("Password reset email error: " . $e->getMessage());
                    }
                } else {
                    alert('error', 'Error generating reset token. Please try again.');
                }
            } else {
                // Don't reveal if email exists (security)
                alert('success', 'If an account exists with that email, you will receive a password reset link shortly.');
                $_SESSION['step'] = 'check_email';
            }
            $stmt->close();
        }
    }
}

// Clear rate limiting after 1 hour
if (isset($_SESSION['reset_attempts_timestamp'])) {
    if (time() - $_SESSION['reset_attempts_timestamp'] > 3600) {
        unset($_SESSION[$reset_attempts_key]);
        $reset_attempts = 0;
    }
} else {
    $_SESSION['reset_attempts_timestamp'] = time();
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
                                    <h5 class="titleFont mb-0">Reset Your Password</h5>
                                    <p class="someText">
                                        <?php
                                        if (!isset($_SESSION['step'])) {
                                            echo 'Enter your email address to receive a secure password reset link.';
                                        } elseif ($_SESSION['step'] == 'check_email') {
                                            echo 'Check your email for the reset link. The link is valid for 1 hour.';
                                        } elseif ($_SESSION['step'] == 'rate_limited') {
                                            echo 'Too many reset attempts. Please wait 1 hour and try again.';
                                        }
                                        ?>
                                    </p>
                                </div>

                                <div class="container">
                                    <form method="POST">
                                        <?php if (!isset($_SESSION['step'])) { ?>
                                            <div class="mb-2">
                                                <label class="form-label someText m-0">Email Address</label>
                                                <input type="email" name="client_email"
                                                    class="form-control someText shadow-none" required>
                                            </div>
                                            <div class="mb-2 d-grid mt-3">
                                                <button type="submit" name="verify_email"
                                                    class="btn btn-primary btnAddCategory someText">Send Reset Link</button>
                                            </div>
                                        <?php } elseif ($_SESSION['step'] == 'check_email') { ?>
                                            <div class="alert alert-info someText mb-3">
                                                <strong>Check your email!</strong> We've sent a secure password reset link
                                                to your inbox.
                                                <br><br>
                                                <small>Didn't receive the email? Check your spam folder or</small>
                                                <a href="forgot_password.php" class="alert-link"> try again</a>
                                            </div>
                                        <?php } elseif ($_SESSION['step'] == 'rate_limited') { ?>
                                            <div class="alert alert-warning someText mb-3">
                                                <strong>Too many attempts!</strong> For your security, you can only request
                                                3 password resets per hour. Please wait and try again later.
                                            </div>
                                            <div class="mb-2 d-grid mt-3">
                                                <a href="login.php"
                                                    class="btn btn-outline-secondary btnAddCategory someText">Back to
                                                    Login</a>
                                            </div>
                                        <?php } ?>
                                    </form>

                                    <?php if (isset($_SESSION['step']) && $_SESSION['step'] == 'check_email') { ?>
                                        <div class="mt-3 text-center">
                                            <a href="login.php"
                                                style="font-size: 13px; color: #4a1c1d; text-decoration: none;">
                                                ← Back to Login
                                            </a>
                                        </div>
                                    <?php } ?>
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