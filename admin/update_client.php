<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    $client_id = $_POST['client_id'];
    $client_name = $_POST['client_name'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];
    $client_status = $_POST['client_status'];

    $client_picture = $_POST['existing_image'];
    if (isset($_FILES['client_picture']) && $_FILES['client_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadHandler = new FileUploadHandler('./dist/img/');
        $result = $uploadHandler->upload($_FILES['client_picture']);
        if (!$result['success']) {
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
            exit;
        }
        $client_picture = $result['filename'];
    }

    $query = "UPDATE clients SET client_name = ?, client_presented_id = ?, client_phone = ?, client_email = ?, client_status = ?, client_picture = ?, failed_attempts = '0' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssi", $client_name, $client_presented_id, $client_phone, $client_email, $client_status, $client_picture, $client_id);
    $stmt->execute();

    if ($stmt->execute()) {
        // Show success message and redirect after 2 seconds
        echo "<script>
                alert('Client information updated successfully!');
                setTimeout(function() {
                    window.location.href = 'clients.php';
                }, 1500);
              </script>";
    } else {
        echo "<script>alert('Error updating client information. Please try again.');</script>";
    }
}
?>