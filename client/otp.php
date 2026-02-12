<?php
session_start();

$client_id = $_SESSION['client_id'];
$client_email = $_SESSION['client_email'];
$client_name = $_SESSION['client_name'];

include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');
require_once('../admin/inc/mailer_helper.php');


if (isset($_POST['Activate'])) {

    $client_otp = $_POST['client_otp'];

    if (isset($_SESSION['otp']) && isset($_SESSION['otp_expiry'])) {
        $otp = $_SESSION['otp'];
        $expiry = $_SESSION['otp_expiry'];

        $Activated = "Activated";

        if (time() < $expiry) {

            if ($client_otp == $otp) {
                $updateQuery = "UPDATE clients SET client_status = ? WHERE id = ?";
                $stmt = $mysqli->prepare($updateQuery);
                // Bind the parameters to the statement
                $rc = $stmt->bind_param('ss', $Activated, $client_id);

                // Execute the statement
                $stmt->execute();

                // Success message and redirection
                echo "<script>
                        alert('Account Successfully Activated!');
                        window.location.href = 'index.php';  // Redirect to client page
                      </script>";
                exit;
            } else {
                alert("error", "OTP didn't match! Please try again.");
            }
        } else {
            alert("Error", "OTP Expired! Please request a new one.");
            // Optionally unset the session variables
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiry']);
        }
    } else {
        echo "No OTP found in the session.";
    }
}

if (isset($_POST["request"])) {
    // Generate OTP
    $otp = rand(100000, 999999);
    // Store the OTP in the session
    $_SESSION['otp'] = $otp;
    // Optionally, store the OTP expiry time (e.g., 5 minutes from now)
    $_SESSION['otp_expiry'] = time() + (5 * 60); // Current time + 5 minutes

    try {
        $mail = getMailer();
        $mail->addAddress($client_email, $client_name);
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
                <h2>Dear Mr./Ms./Mrs. $client_name,</h2>

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
        echo $e;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation</title>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>
</head>

<body style="background-color:#f0eeeb;">
    <div class="container-fluid">
        <div class="row" id="client-content">
            <div class="col-lg-8 m-auto d-flex align-items-center justify-content-center">
                <div class="card card-register" style="width:20rem;">
                    <div class="card-body p-0">

                        <div class="row">

                            <div class="col-lg-12 p-2 mt-3">
                                <div class="d-flex justify-content-center">
                                    <img src="./dist/img/logo2.png" style="width: 140px;">
                                </div>

                                <div class="container">
                                    <form method="POST" enctype="multipart/form-data">

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">One-Time Password</label>
                                            <input type="number" name="client_otp"
                                                class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <button type="submit" name="Activate"
                                                class="btn btn-primary btnAddCategory someText">Activate
                                                Account</button>
                                        </div>
                                    </form>

                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-2 d-grid mt-0">
                                            <button type="submit" name="request" class="btn someText mt-1">Request New
                                                OTP</button>
                                        </div>
                                    </form>

                                    <!-- Login Button (No border, styled as text) -->
                                    <div class="d-flex justify-content-center mt-4">
                                        <a href="login.php"
                                            style="font-size: 15px; color: #4a1c1d; text-decoration: none; padding: 10px 20px;">Log
                                            in</a>
                                    </div>
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