<?php
require('../admin/config/config.php');
require_once('./inc/password_helper.php');

// Rate limiting: Store failed attempts in session
if (!isset($_SESSION)) {
    session_start();
}
$rate_limit_key = 'admin_password_attempts_' . hash('sha256', $_SERVER['REMOTE_ADDR']);
$attempts = isset($_SESSION[$rate_limit_key]) ? $_SESSION[$rate_limit_key] : 0;
$last_attempt_time = isset($_SESSION[$rate_limit_key . '_time']) ? $_SESSION[$rate_limit_key . '_time'] : 0;

// Reset attempts if 15 minutes have passed
if (time() - $last_attempt_time > 900) {
    $attempts = 0;
}

if (isset($_POST['admin_password'])) {
    // Check rate limiting
    if ($attempts >= 5) {
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please try again in 15 minutes.']);
        exit();
    }

    $admin_password = $_POST['admin_password'];

    // Query the clients table to find the admin with id = 0
    $query = "SELECT client_password FROM clients WHERE id = 0 LIMIT 1";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verify password using secure hashing
        if (verifyPassword($admin_password, $admin['client_password'])) {
            // Reset attempts on success
            unset($_SESSION[$rate_limit_key]);
            unset($_SESSION[$rate_limit_key . '_time']);
            echo json_encode(['status' => 'success']);
        } else {
            // Increment failed attempts
            $_SESSION[$rate_limit_key] = $attempts + 1;
            $_SESSION[$rate_limit_key . '_time'] = time();
            echo json_encode(['status' => 'error', 'message' => 'Incorrect admin password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin record not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No password provided.']);
}
?>