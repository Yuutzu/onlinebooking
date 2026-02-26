<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include_once('../admin/inc/password_helper.php');
require_once('../admin/inc/alert.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if any field is empty
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        alert('error', 'All fields are required!');
        echo "<script>window.location.href='profile.php';</script>";
        exit();
    }

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        alert('error', 'New password and confirm password do not match!');
        echo "<script>window.location.href='profile.php';</script>";
        exit();
    }

    // Use the secure password update function
    $result = updatePassword($mysqli, $client_id, $current_password, $new_password);

    if ($result['success']) {
        alert('success', $result['message']);
        echo "<script>window.location.href='profile.php';</script>";
    } else {
        alert('error', $result['message']);
        echo "<script>window.location.href='profile.php';</script>";
    }
    exit();
}
?>