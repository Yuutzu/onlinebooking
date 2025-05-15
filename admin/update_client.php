<?php
include('./config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id'];
    $client_name = $_POST['client_name'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];
    $client_status = $_POST['client_status'];

    $client_picture = $_POST['existing_image'];
    if (!empty($_FILES['client_picture']['name'])) {
        $client_picture = $_FILES['client_picture']['name'];
        move_uploaded_file($_FILES['client_picture']['tmp_name'], "./dist/img/" . $client_picture);
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
