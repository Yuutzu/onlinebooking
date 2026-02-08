<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

$client_id = $_SESSION['client_id'];

// Fetch client details
$ret = "SELECT * FROM clients WHERE id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Profile Picture Update
    if (isset($_FILES['client_picture']) && $_FILES['client_picture']['name'] != '') {
        $target_dir = "../admin/dist/img/";
        $client_picture = basename($_FILES["client_picture"]["name"]);
        $target_file = $target_dir . $client_picture;
        move_uploaded_file($_FILES["client_picture"]["tmp_name"], $target_file);
    } else {
        $client_picture = $row->client_picture;
    }

    // Handle ID Image Update
    if (isset($_FILES['client_id_picture']) && $_FILES['client_id_picture']['name'] != '') {
        $target_dir = "../admin/dist/img/";
        $client_id_picture = basename($_FILES["client_id_picture"]["name"]);
        $target_file = $target_dir . $client_id_picture;
        move_uploaded_file($_FILES["client_id_picture"]["tmp_name"], $target_file);
    } else {
        $client_id_picture = $row->client_id_picture;
    }

    // Update Profile Details
    $client_name = $_POST['client_name'];
    $client_presented_id = $_POST['client_presented_id'];
    $client_id_number = $_POST['client_id_number'];
    $client_phone = $_POST['client_phone'];
    $client_email = $_POST['client_email'];

    $update = "UPDATE clients SET 
        client_name = ?, 
        client_presented_id = ?, 
        client_id_number = ?, 
        client_phone = ?, 
        client_email = ?, 
        client_picture = ?, 
        client_id_picture = ? 
        WHERE id = ?";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param('sssssssi', $client_name, $client_presented_id, $client_id_number, $client_phone, $client_email, $client_picture, $client_id_picture, $client_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>
