<?php
require_once './config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reservation_id"])) {
    $reservation_id = $_POST["reservation_id"];

    // Select only necessary columns, EXCLUDING `client_gcash_ref_image`
    $query = "SELECT reservation_id, room_id, client_name, client_email, client_contact, client_address, 
                     client_id_type, client_id_image, check_in_date, check_out_date, payment_method, 
                     client_gcash_name, client_gcash_number, client_gcash_ref, total_price, amount_paid, 
                     balance, payment_remarks, reservation_type, reservation_status 
              FROM walkin_reservation 
              WHERE reservation_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Format `client_id_image` with the correct path
        if (!empty($row["client_id_image"])) {
            $row["client_id_image"] = "./dist/img/" . $row["client_id_image"];
        } else {
            $row["client_id_image"] = "./dist/img/default.png"; // Default image if none exists
        }

        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Reservation not found."]);
    }

    $stmt->close();
    $mysqli->close();
}
?>
