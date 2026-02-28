<?php
require_once('./admin/inc/SessionManager.php');
include_once('./admin/config/config.php');
require_once('./admin/inc/alert.php');
require_once('./admin/inc/password_helper.php');
require_once('./admin/inc/FileUploadHandler.php');
require_once('./admin/inc/mailer_helper.php');

$reg_error = '';
$reg_success = '';

// Check if user is already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: ./client/index.php');
    exit();
}

if (isset($_POST['register_btn'])) {
    $client_name = trim($_POST['client_name'] ?? '');
    $client_email = trim($_POST['client_email'] ?? '');
    $client_contact = trim($_POST['client_contact'] ?? '');
    $client_dob = trim($_POST['client_dob'] ?? '');
    $client_address = trim($_POST['client_address'] ?? '');
    
    $email_regex = "/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";

    // Validate inputs
    if (empty($client_name) || empty($client_email) || empty($client_contact) || empty($client_dob) || empty($client_address)) {
        $reg_error = "All fields are required";
    } elseif (!preg_match($email_regex, $client_email)) {
        $reg_error = "Invalid email format";
    } elseif (strlen($client_name) < 3) {
        $reg_error = "Name must be at least 3 characters";
    } else {
        // Check if email already exists
        $check_query = "SELECT client_id FROM clients WHERE client_email = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $client_email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $reg_error = "Email already registered";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // Handle file upload
            $uploadHandler = new FileUploadHandler('../dist/img/');
            $upload_result = $uploadHandler->upload();

            if ($upload_result['success']) {
                $client_picture = $upload_result['filename'];
                
                // Generate temporary password
                $temp_password = bin2hex(random_bytes(6)); // 12 character password
                $hashed_password = hashPassword($temp_password);

                // Insert client into database with prepared statement
                $insert_query = "INSERT INTO clients (client_name, client_email, client_contact, client_dob, client_address, client_password, client_picture, client_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
                $insert_stmt = $mysqli->prepare($insert_query);
                $insert_stmt->bind_param("sssssss", $client_name, $client_email, $client_contact, $client_dob, $client_address, $hashed_password, $client_picture);

                if ($insert_stmt->execute()) {
                    // Send email with temporary password
                    $subject = "Welcome to " . $settings['site_name'] . " - Your Temporary Password";
                    $body = "Dear " . htmlspecialchars($client_name) . ",\n\n";
                    $body .= "Thank you for registering with us!\n\n";
                    $body .= "Your temporary password is: " . htmlspecialchars($temp_password) . "\n";
                    $body .= "Please log in and change your password immediately.\n\n";
                    $body .= "Best regards,\n" . $settings['site_name'];

                    if (sendEmail($client_email, $subject, $body)) {
                        $reg_success = "Registration successful! A temporary password has been sent to your email.";
                    } else {
                        $reg_error = "Registration successful but email could not be sent. Please contact support.";
                    }
                } else {
                    $reg_error = "Registration failed. Please try again.";
                }
                $insert_stmt->close();
            } else {
                $reg_error = $upload_result['message'];
            }
        }
    }
}

// Fetch settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Management System</title>
    <?php require('./client/inc/links.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .register-row {
            display: flex;
            min-height: auto;
        }

        .register-image {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 40px;
        }

        .register-image img {
            max-width: 100%;
            height: auto;
        }

        .register-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-form h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .register-form p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .register-btn {
            background: #667eea;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .register-btn:hover {
            background: #5568d3;
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        @media (max-width: 768px) {
            .register-row {
                flex-direction: column;
            }

            .register-image {
                display: none;
            }

            .register-form {
                padding: 30px;
            }

            .form-row {
                flex-direction: column;
            }

            .form-row .form-group {
                flex: 1;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-row">
            <div class="register-image">
                <div>
                    <h1 style="font-size: 48px; margin-bottom: 20px;">Join Us</h1>
                    <p>Create your account and start booking amazing rooms</p>
                </div>
            </div>

            <div class="register-form">
                <?php if ($reg_error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($reg_error); ?></div>
                <?php endif; ?>

                <?php if ($reg_success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($reg_success); ?></div>
                <?php endif; ?>

                <h2>Create Account</h2>
                <p>Fill in your details to register</p>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client_name">Full Name</label>
                            <input type="text" id="client_name" name="client_name" placeholder="Enter your full name"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="client_email">Email Address</label>
                            <input type="email" id="client_email" name="client_email" placeholder="Enter your email"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="client_contact">Contact Number</label>
                            <input type="tel" id="client_contact" name="client_contact"
                                placeholder="Enter your contact number" required>
                        </div>

                        <div class="form-group">
                            <label for="client_dob">Date of Birth</label>
                            <input type="date" id="client_dob" name="client_dob" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="client_address">Address</label>
                        <textarea id="client_address" name="client_address" placeholder="Enter your address" rows="3"
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="id_picture">ID Picture (for verification)</label>
                        <input type="file" id="id_picture" name="file" accept="image/*" required>
                        <small style="color: #666;">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    </div>

                    <button type="submit" name="register_btn" class="register-btn">Create Account</button>

                    <div class="login-link">
                        Already have an account? <a href="login.php">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
