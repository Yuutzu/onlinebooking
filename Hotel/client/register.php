<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '..\PHPMailer\PHPMailer\src\Exception.php';
require '..\PHPMailer\PHPMailer\src\PHPMailer.php';
require '..\PHPMailer\PHPMailer\src\SMTP.php';



if (isset($_POST['register'])) {
    // Generate Temporary Password
    $length = 10;
    $temp_pass = substr(str_shuffle('0123A4567B89ABC'), 1, $length);

    // Generate Unique Client ID
    $id_length = 4;
    $current_year = date("Y");
    $random_id = substr(str_shuffle('0123A4567B89ABC'), 1, $length);

    $client_id = "LUX-$current_year-$random_id";


    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_number'];
    $client_email = $_POST['client_email'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_id_picture  = $_FILES["client_id_picture"]["name"];
    move_uploaded_file($_FILES["client_id_picture"]["tmp_name"], "dist/img/" . $_FILES["client_id_picture"]["name"]);
    $client_id_number = $_POST['client_id_number'];
    $password = $temp_pass;
    $client_status = "Pending";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luxehavenhotelph@gmail.com';
        $mail->Password = 'lvlx qagp ojak ymsu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('luxehavenhotelph@gmail.com', 'Luxe Haven Hotel PH Team');
        $mail->addAddress($client_email, $client_name);

        $mail->isHTML(true);
        $mail->Subject = 'Luxe Haven Team - Temporary Account Password';
        $mail->Body = "
           <html>
        <head>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    color: #333333;
                    background-color: #f4f4f4;
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
                .password {
                    font-weight: bold;
                    font-size: 18px;
                    color: #d9534f;
                    background-color: #f8f8f8;
                    padding: 8px;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Dear Mr./Ms./Mrs. $client_name,</h2>

                <p>Thank you for choosing <b>Luxe Haven Hotel</b> for your stay.</p>

                <p>As requested, we have generated a temporary password for your account:</p>

                <p><span class='password'>Temporary Password: $temp_pass</span></p>

                <p>For your security, we recommend updating your password immediately after logging in.</p>

                <p>If you encounter any issues or have any questions, please feel free to contact our support team at <a href='mailto:luxehavenhotelph@gmail.com' style='color: #4a1c1d;'>luxehavenhotelph@gmail.com</a>.</p>

                <p>We look forward to serving you and ensuring a comfortable stay.</p>

                <br>

                <p>Sincerely yours,</p>
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

    $insertClientQuery = "INSERT INTO clients (client_id, client_name, client_presented_id, client_id_picture, client_id_number, client_phone, client_email, client_password, client_status) VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt2 = $mysqli->prepare($insertClientQuery);
    //bind paramaters
    $rc = $stmt2->bind_param('sssssssss', $client_id, $client_name, $client_presented_id, $client_id_picture, $client_id_number, $client_phone, $client_email, $password, $client_status);
    $stmt2->execute();

    if ($stmt2) {
        echo "<script>
            alert('Registered Successfully. Please check your email for your temporary password.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
    else {
        alert('error', 'Please try again');
    }
}





?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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

                            <div class="col-lg-6 p-2 mt-3">
                                <div class="d-flex justify-content-center">
                                    <img src="./dist/img/logo2.png" style="width: 140px;">
                                </div>

                                <div class="container">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Full Name</label>
                                            <input type="text" name="client_name" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Contact No.</label>
                                            <input type="number" name="client_number" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Email Address</label>
                                            <input type="email" name="client_email" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Identification Card</label>
                                            <select name="client_presented_id" required class="form-control shadow-none someText">
                                                <option>Select ID</option>
                                                <option value="National ID">National ID</option>
                                                <option value="Social Security ID">Social Security ID</option>
                                                <option value="Passport">Passport</option>
                                                <option value="Driver's License">Driver's License</option>
                                                <option value="PRC License">PRC License</option>
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Upload ID</label>
                                            <input type="file" name="client_id_picture" class="form-control shadow-none someText">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Uploaded ID No.</label>
                                            <input type="text" name="client_id_number" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <button type="submit" name="register" class="btn btn-primary btnAddCategory someText">Register</button>
                                        </div>
                                    </form>

                                    <!-- Login Button as Text -->
                                    <div class="d-flex justify-content-center mt-4">
                                        <a href="login.php" style="font-size: 1rem; color: #4a1c1d; text-decoration: none;">Already have an account? Log in</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="imageContainer">
                                    <img src="./dist/img/register.jpg" class="registerImage">
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

