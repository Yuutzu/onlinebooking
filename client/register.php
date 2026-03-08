<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include_once('../admin/inc/password_helper.php');
include_once('../admin/inc/FileUploadHandler.php');
require('../admin/inc/alert.php');
require_once('../admin/inc/mailer_helper.php');



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

    // Check if email already exists
    $email_check = "SELECT id FROM clients WHERE client_email = ?";
    $stmt_check = $mysqli->prepare($email_check);
    $stmt_check->bind_param('s', $client_email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['upload_error'] = 'Email already registered. Please use a different email address.';
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }

    // Secure file upload handler
    if (!isset($_FILES['client_id_picture']) || $_FILES['client_id_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['upload_error'] = 'ID picture is required.';
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }

    $uploadHandler = new FileUploadHandler('../admin/dist/img/');
    $uploadResult = $uploadHandler->upload($_FILES['client_id_picture']);

    if (!$uploadResult['success']) {
        $_SESSION['upload_error'] = $uploadResult['message'];
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }

    $client_id_picture = $uploadResult['filename'];

    $client_id_number = $_POST['client_id_number'];
    $password = $temp_pass;
    $client_status = "Pending";

    // Hash the temporary password before storing
    $hashed_password = hashPassword($password);

    try {
        $mail = getMailer();
        $mail->addAddress($client_email, $client_name);
        $mail->Subject = 'Luxe Haven Team - Temporary Account Password';
        $mail->isHTML(true);
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

        if (!$mail->send()) {
            error_log("Email sending failed for $client_email: " . $mail->ErrorInfo);
        }
    } catch (Exception $e) {
        error_log("Email exception for $client_email: " . $e->getMessage());
    }

    $insertClientQuery = "INSERT INTO clients (client_id, client_name, client_presented_id, client_id_picture, client_picture, client_id_number, client_phone, client_email, client_password, client_status) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmt2 = $mysqli->prepare($insertClientQuery);
    //bind paramaters - use same uploaded image for both ID and profile picture
    $rc = $stmt2->bind_param('ssssssssss', $client_id, $client_name, $client_presented_id, $client_id_picture, $client_id_picture, $client_id_number, $client_phone, $client_email, $hashed_password, $client_status);

    if ($stmt2->execute()) {
        echo "<script>
            alert('Registered Successfully. Please check your email for your temporary password.');
            window.location.href = 'login.php';
        </script>";
        exit();
    } else {
        alert('error', 'Please try again: ' . $stmt2->error);
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
                                    <?php
                                    // Display error messages if any
                                    if (isset($_SESSION['upload_error'])) {
                                        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
                                        echo htmlspecialchars($_SESSION['upload_error']);
                                        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                                        echo "</div>";
                                        unset($_SESSION['upload_error']);
                                    }

                                    // Get form data if available
                                    $form_data = $_SESSION['form_data'] ?? [];
                                    unset($_SESSION['form_data']);
                                    ?>
                                    <form id="register_form" method="POST" enctype="multipart/form-data" onsubmit="return validateFormBeforeSubmit(event)">
                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Full Name</label>
                                            <input type="text" name="client_name"
                                                class="form-control someText shadow-none"
                                                value="<?= htmlspecialchars($form_data['client_name'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Contact No.</label>
                                            <input type="number" name="client_number"
                                                class="form-control someText shadow-none"
                                                value="<?= htmlspecialchars($form_data['client_number'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Email Address</label>
                                            <input type="email" id="client_email" name="client_email"
                                                class="form-control someText shadow-none"
                                                value="<?= htmlspecialchars($form_data['client_email'] ?? '') ?>"
                                                required>
                                            <small id="email_error" class="text-danger d-none"></small>
                                            <small id="email_success" class="text-success d-none">✓ Email available</small>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Identification Card</label>
                                            <select name="client_presented_id" required
                                                class="form-control shadow-none someText">
                                                <option>Select ID</option>
                                                <option value="National ID" <?= ($form_data['client_presented_id'] ?? '') === 'National ID' ? 'selected' : '' ?>>National ID</option>
                                                <option value="Social Security ID" <?= ($form_data['client_presented_id'] ?? '') === 'Social Security ID' ? 'selected' : '' ?>>Social Security
                                                    ID</option>
                                                <option value="Passport" <?= ($form_data['client_presented_id'] ?? '') === 'Passport' ? 'selected' : '' ?>>Passport</option>
                                                <option value="Driver's License" <?= ($form_data['client_presented_id'] ?? '') === "Driver's License" ? 'selected' : '' ?>>Driver's License
                                                </option>
                                                <option value="PRC License" <?= ($form_data['client_presented_id'] ?? '') === 'PRC License' ? 'selected' : '' ?>>PRC License</option>
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Upload ID</label>
                                            <input type="file" name="client_id_picture"
                                                class="form-control shadow-none someText">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Uploaded ID No.</label>
                                            <input type="text" name="client_id_number"
                                                class="form-control someText shadow-none"
                                                value="<?= htmlspecialchars($form_data['client_id_number'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <button type="submit" name="register"
                                                class="btn btn-primary btnAddCategory someText">Register</button>
                                        </div>
                                    </form>

                                    <!-- Login Button as Text -->
                                    <div class="d-flex justify-content-center mt-4">
                                        <a href="login.php"
                                            style="font-size: 1rem; color: #4a1c1d; text-decoration: none;">Already have
                                            an account? Log in</a>
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

    <script>
        // Real-time email validation
        const emailInput = document.getElementById('client_email');

        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                checkEmailAvailability(this.value);
            });

            emailInput.addEventListener('input', function() {
                // Clear validation messages while typing
                document.getElementById('email_error').classList.add('d-none');
                document.getElementById('email_success').classList.add('d-none');
            });
        }

        function checkEmailAvailability(email) {
            if (!email || !isValidEmail(email)) {
                return;
            }

            fetch('check_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                const errorEl = document.getElementById('email_error');
                const successEl = document.getElementById('email_success');

                if (data.exists) {
                    errorEl.textContent = '✗ Email already registered. Please use a different email.';
                    errorEl.classList.remove('d-none');
                    successEl.classList.add('d-none');
                    emailInput.classList.add('is-invalid');
                } else {
                    successEl.classList.remove('d-none');
                    errorEl.classList.add('d-none');
                    emailInput.classList.remove('is-invalid');
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validateFormBeforeSubmit(event) {
            const emailInput = document.getElementById('client_email');
            const emailError = document.getElementById('email_error');

            // Check if email error message is visible
            if (!emailError.classList.contains('d-none')) {
                event.preventDefault();
                alert('Please use a different email address.');
                return false;
            }

            // Check if email is empty
            if (!emailInput.value.trim()) {
                event.preventDefault();
                alert('Please enter an email address.');
                return false;
            }

            return true;
        }
    </script>
</body>

</html>