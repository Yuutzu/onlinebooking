<?php
include('./config/config.php');

if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];

    $stmt = $mysqli->prepare("SELECT client_id, client_name, client_presented_id, client_phone, client_email, client_status, client_picture FROM clients WHERE client_id = ?");
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "data" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "Client not found"]);
    }

    $stmt->close();
    $mysqli->close();
}
?>
