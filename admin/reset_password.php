<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid and has not expired
    $checkTokenQuery = "SELECT id, reset_token_expiry FROM clients WHERE reset_token = ?";
    $stmt = $mysqli->prepare($checkTokenQuery);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($client_id, $reset_token_expiry);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && $reset_token_expiry > time()) {
        // Token is valid and not expired
        if (isset($_POST['reset_password'])) {
            $new_password = md5($_POST['new_password']);
            $confirm_password = md5($_POST['confirm_password']);

            if ($new_password == $confirm_password) {
                // Update the new password in the database
                $updatePasswordQuery = "UPDATE clients SET client_password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
                $stmt2 = $mysqli->prepare($updatePasswordQuery);
                $stmt2->bind_param('si', $new_password, $client_id);
                $stmt2->execute();

                if ($stmt2) {
                    alert('success', 'Your password has been reset successfully. You can now login with your new password.');
                    header('Location: login.php');
                } else {
                    alert('error', 'Error resetting password. Please try again.');
                }
            } else {
                alert('error', 'Passwords do not match.');
            }
        }
    } else {
        alert('error', 'Invalid or expired token.');
    }
} else {
    header('Location: login.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

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
                                    <h5 class="titleFont mb-0">Reset Password</h5>
                                    <p class="someText">Please enter your new password below.</p>
                                </div>

                                <div class="container">
                                    <form method="POST" enctype="multipart/form-data">

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">New Password</label>
                                            <input type="password" name="new_password" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label someText m-0">Confirm Password</label>
                                            <input type="password" name="confirm_password" class="form-control someText shadow-none" required>
                                        </div>

                                        <div class="mb-2 d-grid mt-3">
                                            <button type="submit" name="reset_password" class="btn btn-primary btnAddCategory someText">Reset Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-
