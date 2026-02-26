<?php
session_start();
include_once('../admin/config/config.php');
include_once('../admin/inc/email_2fa_helper.php');
require_once('../admin/inc/alert.php');

// Check if user is in 2FA verification state
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['2fa_user_id'];

// Get user info
$query = "SELECT client_name, client_email, two_fa_code, two_fa_expiry FROM clients WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_object();
$stmt->close();

if (!$user) {
    header("Location: login.php");
    exit();
}

// Handle verification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $inputCode = trim($_POST['verification_code']);

    if (verifyEmail2FACode($inputCode, $user->two_fa_code, $user->two_fa_expiry)) {
        // Code is valid - complete login
        clear2FACode($mysqli, $user_id);

        // Set session variables
        $_SESSION['client_id'] = $user_id;
        $_SESSION['client_name'] = $user->client_name;
        $_SESSION['client_email'] = $user->client_email;

        // Clear 2FA session variable
        unset($_SESSION['2fa_user_id']);

        alert('success', 'Login successful!');
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid or expired verification code. Please try again.";
    }
}

// Handle resend code
if (isset($_POST['resend_code'])) {
    $newCode = generateEmailCode();

    if (store2FACode($mysqli, $user_id, $newCode) && sendEmail2FACode($user->client_email, $user->client_name, $newCode)) {
        $success = "A new verification code has been sent to your email.";
    } else {
        $error = "Failed to send verification code. Please try again.";
    }
}

// Handle send test code
if (isset($_POST['send_test_code'])) {
    $newCode = generateEmailCode();

    if (store2FACode($mysqli, $user_id, $newCode) && sendEmail2FACode($user->client_email, $user->client_name, $newCode)) {
        $success = "Test verification code sent successfully to " . htmlspecialchars($user->client_email) . ". Check your email!";
    } else {
        $error = "Failed to send test code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>

    <?php require('./inc/links.php'); ?>
</head>

<body style="background-color:#f0eeeb;">
    <div class="container-fluid">
        <div class="row" id="client-content">
            <div class="col-lg-6 m-auto d-flex align-items-center justify-content-center">
                <div class="card card-register" style="width:40rem;">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-center mb-3">
                            <img src="./dist/img/logo2.png" style="width: 120px;">
                        </div>

                        <div class="text-center mb-4">
                            <h5 class="titleFont mb-2">Email Verification</h5>
                            <p class="someText">
                                We've sent a 6-digit code to
                                <strong><?php echo htmlspecialchars($user->client_email); ?></strong>
                            </p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger someText">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success someText">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label someText m-0">Verification Code</label>
                                <input type="text" name="verification_code"
                                    class="form-control someText shadow-none text-center"
                                    placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required autofocus
                                    autocomplete="off" style="font-size: 20px; letter-spacing: 5px; font-weight: 600;">
                            </div>

                            <div class="mb-2 d-grid">
                                <button type="submit" name="verify_code"
                                    class="btn btn-primary btnAddCategory someText">
                                    Verify & Login
                                </button>
                            </div>
                        </form>

                        <form method="POST" class="mt-2">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" name="resend_code"
                                        class="btn btn-outline-secondary someText w-100">
                                        Resend Code
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" name="send_test_code"
                                        class="btn btn-outline-info someText w-100">
                                        Test Send
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-3 p-3" style="background-color: #f8f9fa; border-radius: 5px;">
                            <p class="someText mb-1"><strong>Tips:</strong></p>
                            <p class="someText mb-1">• Check your spam/junk folder</p>
                            <p class="someText mb-1">• Code expires in 10 minutes</p>
                            <p class="someText mb-0">• Request a new code if expired</p>
                        </div>

                        <div class="text-center mt-3">
                            <a href="login.php" style="font-size: 13px; color: #4a1c1d; text-decoration: none;">
                                ← Back to Login
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Only allow numbers
        document.querySelector('input[name="verification_code"]').addEventListener('keypress', function (e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>