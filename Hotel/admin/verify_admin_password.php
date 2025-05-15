<?php
require('../admin/config/config.php');

// Check if the password is sent through POST
if (isset($_POST['admin_password'])) {
    $admin_password = $_POST['admin_password'];
    
    // Query the clients table to find the admin with id = 0
    $query = "SELECT client_password FROM clients WHERE id = 0 LIMIT 1";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Compare the provided password with the stored plain text password
        if ($admin_password === $admin['client_password']) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect admin password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin record not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No password provided.']);
}
?>