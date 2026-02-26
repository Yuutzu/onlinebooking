<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    $client_id = $_POST['client_id']; // Admin ID (Primary Key)
    $client_name = $_POST['client_name'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];
    $client_id_number = $_POST['client_id_number']; // Corrected variable name for ID Number

    $uploadHandler = new FileUploadHandler('./dist/img/');

    // Handle profile picture update
    $client_picture = $_POST['existing_image'];
    if (isset($_FILES['client_picture']) && $_FILES['client_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['client_picture']);
        if (!$result['success']) {
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
            exit;
        }
        $client_picture = $result['filename'];
    }

    // Handle ID image update
    $client_id_picture = $_POST['existing_id_image'];
    if (isset($_FILES['client_id_picture']) && $_FILES['client_id_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['client_id_picture']);
        if (!$result['success']) {
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
            exit;
        }
        $client_id_picture = $result['filename'];
    }

    // Update database
    $query = "UPDATE clients SET client_name = ?, client_presented_id = ?, client_phone = ?, client_email = ?, client_id_number = ?, client_picture = ?, client_id_picture = ?, failed_attempts = '0' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssssi", $client_name, $client_presented_id, $client_phone, $client_email, $client_id_number, $client_picture, $client_id_picture, $client_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Admin information updated successfully!');
                setTimeout(function() {
                    window.location.href = 'admin_accounts.php';
                }, 1500);
              </script>";
    } else {
        echo "<script>alert('Error updating admin information. Please try again.');</script>";
    }
}
?>