<?php
require_once('./admin/inc/SessionManager.php');
include_once('./admin/config/config.php');
include_once('./admin/config/checklogin.php');
require_once('./admin/inc/alert.php');
require_once('./admin/inc/email_2fa_helper.php');
require_once('./admin/inc/password_helper.php');
require_once('./admin/inc/mailer_helper.php');

$client_created_error_display = '';
$client_created_success_display = '';

// Check if user is already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: ./client/index.php');
    exit();
}

// Check if user is admin
if (isset($_SESSION['admin_id'])) {
    header('Location: ./admin/dashboard.php');
    exit();
}

if (isset($_POST['client_submit'])) {
    // Validate input
    $client_email = trim($_POST['client_email'] ?? '');
    $client_password = trim($_POST['client_password'] ?? '');
    $email_regex = "/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";

    if (empty($client_email) || empty($client_password)) {
        $client_created_error_display = "Email and password are required";
    } elseif (!preg_match($email_regex, $client_email)) {
        $client_created_error_display = "Invalid email format";
    } else {
        // Check if user exists with prepared statement
        $query = "SELECT client_id, client_password, client_email, client_name, client_2fa FROM clients WHERE client_email = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $client_email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            
            // Verify password
            if (verifyPassword($client_password, $row['client_password'])) {
                // Check rate limiting
                $rate_limit_key = "failed_attempts_" . hash('sha256', $client_email);
                $failed_attempts = $_SESSION[$rate_limit_key] ?? 0;
                
                if ($failed_attempts >= 5) {
                    $client_created_error_display = "Too many failed attempts. Please try again later.";
                } else {
                    // Reset failed attempts
                    $_SESSION[$rate_limit_key] = 0;
                    
                    if ($row['client_2fa'] == 1) {
                        // Generate OTP with cryptographically secure random bytes
                        $otp = bin2hex(random_bytes(2));
                        
                        // Store OTP in session temporarily
                        $_SESSION['temp_client_id'] = $row['client_id'];
                        $_SESSION['temp_client_otp'] = $otp;
                        $_SESSION['otp_timestamp'] = time();
                        
                        // Send OTP via email
                        if (sendOTPEmail($row['client_email'], $otp)) {
                            header('Location: otp.php');
                            exit();
                        } else {
                            $client_created_error_display = "Failed to send OTP. Please try again.";
                        }
                    } else {
                        // Create session directly
                        SessionManager::create($row['client_id'], 'client');
                        header('Location: ./client/index.php');
                        exit();
                    }
                }
            } else {
                // Increment failed attempts
                $rate_limit_key = "failed_attempts_" . hash('sha256', $client_email);
                $_SESSION[$rate_limit_key] = ($_SESSION[$rate_limit_key] ?? 0) + 1;
                
                if ($_SESSION[$rate_limit_key] >= 5) {
                    $client_created_error_display = "Too many failed attempts. Account temporarily locked.";
                } else {
                    $client_created_error_display = "Invalid email or password";
                }
            }
        } else {
            // Generic response to prevent email enumeration
            $client_created_error_display = "Invalid email or password";
        }
        $stmt->close();
    }
}

// Admin login
if (isset($_POST['admin_submit'])) {
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');
    $email_regex = "/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";

    if (empty($admin_email) || empty($admin_password)) {
        $client_created_error_display = "Email and password are required";
    } elseif (!preg_match($email_regex, $admin_email)) {
        $client_created_error_display = "Invalid email format";
    } else {
        // Check rate limiting
        $rate_limit_key = "admin_failed_attempts_" . hash('sha256', $admin_email);
        $failed_attempts = $_SESSION[$rate_limit_key] ?? 0;
        
        if ($failed_attempts >= 5) {
            $client_created_error_display = "Too many failed attempts. Please try again later.";
        } else {
            // Check if admin exists with prepared statement
            $query = "SELECT admin_id, admin_password, admin_email, admin_2fa FROM admins WHERE admin_email = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $admin_email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                
                // Verify password
                if (verifyPassword($admin_password, $row['admin_password'])) {
                    // Reset failed attempts
                    $_SESSION[$rate_limit_key] = 0;
                    
                    if ($row['admin_2fa'] == 1) {
                        // Generate OTP
                        $otp = bin2hex(random_bytes(2));
                        
                        $_SESSION['temp_admin_id'] = $row['admin_id'];
                        $_SESSION['temp_admin_otp'] = $otp;
                        $_SESSION['otp_timestamp'] = time();
                        
                        // Send OTP
                        if (sendOTPEmail($row['admin_email'], $otp)) {
                            header('Location: ./admin/otp.php');
                            exit();
                        } else {
                            $client_created_error_display = "Failed to send OTP. Please try again.";
                        }
                    } else {
                        // Create session directly
                        SessionManager::create($row['admin_id'], 'admin');
                        header('Location: ./admin/dashboard.php');
                        exit();
                    }
                } else {
                    // Increment failed attempts
                    $_SESSION[$rate_limit_key] = ($_SESSION[$rate_limit_key] ?? 0) + 1;
                    
                    if ($_SESSION[$rate_limit_key] >= 5) {
                        $client_created_error_display = "Too many failed attempts. Account temporarily locked.";
                    } else {
                        $client_created_error_display = "Invalid email or password";
                    }
                }
            } else {
                // Generic response to prevent email enumeration
                $client_created_error_display = "Invalid email or password";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Management System</title>
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

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .login-row {
            display: flex;
            height: 500px;
        }

        .login-image {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 40px;
        }

        .login-image img {
            max-width: 100%;
            height: auto;
        }

        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-form p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .login-btn {
            background: #667eea;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #5568d3;
        }

        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 13px;
        }

        .login-links a {
            color: #667eea;
            text-decoration: none;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        .tab-buttons {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }

        .tab-btn {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #999;
            transition: all 0.3s;
            position: relative;
        }

        .tab-btn.active {
            color: #667eea;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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

        @media (max-width: 768px) {
            .login-row {
                flex-direction: column;
                height: auto;
            }

            .login-image {
                display: none;
            }

            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-row">
            <div class="login-image">
                <div>
                    <h1 style="font-size: 48px; margin-bottom: 20px;">Welcome</h1>
                    <p>Sign in to access your account and book your stay</p>
                </div>
            </div>

            <div class="login-form">
                <?php if ($client_created_error_display): ?>
                    <div class="error-message"><?php echo htmlspecialchars($client_created_error_display); ?></div>
                <?php endif; ?>

                <?php if ($client_created_success_display): ?>
                    <div class="success-message"><?php echo htmlspecialchars($client_created_success_display); ?></div>
                <?php endif; ?>

                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="switchTab('client', this)">Client Login</button>
                    <button class="tab-btn" onclick="switchTab('admin', this)">Admin Login</button>
                </div>

                <!-- Client Login Tab -->
                <div id="client" class="tab-content active">
                    <h2>Client Login</h2>
                    <p>Login with your email and password</p>

                    <form method="POST">
                        <div class="form-group">
                            <label for="client_email">Email Address</label>
                            <input type="email" id="client_email" name="client_email" placeholder="Enter your email"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="client_password">Password</label>
                            <input type="password" id="client_password" name="client_password"
                                placeholder="Enter your password" required>
                        </div>

                        <button type="submit" name="client_submit" class="login-btn">Login</button>

                        <div class="login-links">
                            <a href="./client/forgot_password.php">Forgot Password?</a>
                            <a href="register.php">Create Account</a>
                        </div>
                    </form>
                </div>

                <!-- Admin Login Tab -->
                <div id="admin" class="tab-content">
                    <h2>Admin Login</h2>
                    <p>Login with your admin credentials</p>

                    <form method="POST">
                        <div class="form-group">
                            <label for="admin_email">Email Address</label>
                            <input type="email" id="admin_email" name="admin_email" placeholder="Enter your email"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="admin_password">Password</label>
                            <input type="password" id="admin_password" name="admin_password"
                                placeholder="Enter your password" required>
                        </div>

                        <button type="submit" name="admin_submit" class="login-btn">Login</button>

                        <div class="login-links">
                            <a href="./admin/forgot_password.php">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName, button) {
            // Hide all tab content
            let tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            let buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            button.classList.add('active');
        }
    </script>
</body>

</html>
