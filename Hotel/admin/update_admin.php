<?php
include('./config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id']; // Admin ID (Primary Key)
    $client_name = $_POST['client_name'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];
    $client_id_number = $_POST['client_id_number']; // Corrected variable name for ID Number

    // Handle profile picture update
    $client_picture = $_POST['existing_image']; // Keep existing image if no new one is uploaded
    if (!empty($_FILES['client_picture']['name'])) {
        $client_picture = basename($_FILES['client_picture']['name']);
        move_uploaded_file($_FILES['client_picture']['tmp_name'], "./dist/img/" . $client_picture);
    }

    // Handle ID image update
    $client_id_picture = $_POST['existing_id_image']; // Keep existing ID image if no new one is uploaded
    if (!empty($_FILES['client_id_picture']['name'])) {
        $client_id_picture = basename($_FILES['client_id_picture']['name']);
        move_uploaded_file($_FILES['client_id_picture']['tmp_name'], "./dist/img/" . $client_id_picture);
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
