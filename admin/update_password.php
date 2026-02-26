<?php
session_start();
include('./config/config.php');
include('./config/checklogin.php');
include_once('./inc/password_helper.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if any field is empty
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo "<script>
            alert('All fields are required!');
            window.location.href='admin_accounts.php';
        </script>";
        exit();
    }

    // Fetch the stored password hash
    $stmt = $mysqli->prepare("SELECT client_password FROM clients WHERE id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    // Verify current password using secure hashing
    if (!verifyPassword($current_password, $stored_password)) {
        echo "<script>
            alert('Current password is incorrect!');
            window.location.href='admin_accounts.php';
        </script>";
        exit();
    }

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "<script>
            alert('New password and Confirm password do not match!');
            window.location.href='admin_accounts.php';
        </script>";
        exit();
    }

    // Hash the new password before storage
    $hashed_password = hashPassword($new_password);

    // Update password in the database with hashed value
    $update_stmt = $mysqli->prepare("UPDATE clients SET client_password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $client_id);

    if ($update_stmt->execute()) {
        echo "<script>
            alert('Password updated successfully!');
            window.location.href='admin_accounts.php';
        </script>";
    } else {
        echo "<script>
            alert('Something went wrong. Please try again!');
            window.location.href='admin_accounts.php';
        </script>";
    }
    $update_stmt->close();
    exit();
}
?>