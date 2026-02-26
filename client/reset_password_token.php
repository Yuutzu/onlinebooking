<?php
session_start();
include('../admin/config/config.php');
include_once('../admin/inc/password_helper.php');
require_once('../admin/inc/alert.php');

$token = isset($_GET['token']) ? $_GET['token'] : '';
$user_id = isset($_GET['user']) ? (int) $_GET['user'] : 0;
$token_valid = false;
$error_message = '';

// Verify the token
if ($token && $user_id > 0) {
    // Get user info to verify token exists
    $query = "SELECT id, client_name, client_email, password_reset_expiry, password_reset_used FROM clients WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_object();
    $stmt->close();

    if ($user && $user->password_reset_used == 0 && $user->password_reset_expiry && strtotime($user->password_reset_expiry) > time()) {
        // Token exists and is not expired and hasn't been used
        if (verifyPasswordResetToken($mysqli, $user_id, $token)) {
            $token_valid = true;
        } else {
            $error_message = 'Invalid reset token. Please request a new password reset.';
        }
    } else {
        if ($user && $user->password_reset_used == 1) {
            $error_message = 'This password reset link has already been used. Please request a new one.';
        } else {
            $error_message = 'This password reset link has expired. Please request a new one.';
        }
    }
} else {
    $error_message = 'Invalid reset link. Please request a new password reset from the forgot password page.';
}

// Handle password reset form submission
if ($token_valid && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = 'All fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Complete the password reset
        if (completePasswordReset($mysqli, $user_id, $new_password, $token)) {
            alert('success', 'Your password has been reset successfully. Please log in with your new password.');
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>";
            exit();
        } else {
            $error_message = 'Error resetting password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
                                    <?php if ($token_valid): ?>
                                        <p class="someText">Enter your new password below.</p>
                                    <?php else: ?>
                                        <p class="someText">Password Reset</p>
                                    <?php endif; ?>
                                </div>

                                <div class="container">
                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger someText mb-3">
                                            <?php echo htmlspecialchars($error_message); ?>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <a href="forgot_password.php"
                                                class="btn btn-outline-secondary btnAddCategory someText">
                                                Request New Reset Link
                                            </a>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="login.php"
                                                style="font-size: 13px; color: #4a1c1d; text-decoration: none;">
                                                ← Back to Login
                                            </a>
                                        </div>
                                    <?php elseif ($token_valid): ?>
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label someText m-0">New Password</label>
                                                <input type="password" name="new_password"
                                                    class="form-control someText shadow-none"
                                                    placeholder="Minimum 6 characters" required>
                                                <small class="form-text text-muted">Password must be at least 6 characters
                                                    long.</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label someText m-0">Confirm Password</label>
                                                <input type="password" name="confirm_password"
                                                    class="form-control someText shadow-none"
                                                    placeholder="Re-enter your password" required>
                                            </div>

                                            <div class="mb-2 d-grid mt-4">
                                                <button type="submit" name="reset_password"
                                                    class="btn btn-primary btnAddCategory someText">
                                                    Reset Password
                                                </button>
                                            </div>
                                        </form>

                                        <div class="text-center mt-3">
                                            <a href="login.php"
                                                style="font-size: 13px; color: #4a1c1d; text-decoration: none;">
                                                ← Back to Login
                                            </a>
                                        </div>

                                        <div class="mt-4 p-3" style="background-color: #f8f9fa; border-radius: 5px;">
                                            <p class="someText mb-1"><strong>Security Tips:</strong></p>
                                            <p class="someText mb-1">• Use a strong, unique password</p>
                                            <p class="someText mb-1">• Don't share your password with anyone</p>
                                            <p class="someText mb-0">• This link will expire in 1 hour</p>
                                        </div>
                                    <?php endif; ?>
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