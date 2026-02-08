<?php
require_once './config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reservation_id"])) {
    $reservation_id = $_POST["reservation_id"];

    // Select only necessary columns, now from the `reservations` table
    $query = "SELECT reservation_id, room_id, check_in, check_out, payment_method, 
                     gcash_name, gcash_number, gcash_ref, gcash_screenshot, total_price, 
                     type, reservation_status, client_id
              FROM reservations 
              WHERE reservation_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // If there is a GCash screenshot, append the correct path
        if (!empty($row["gcash_screenshot"])) {
            $row["gcash_screenshot"] = "./dist/img/" . $row["gcash_screenshot"];
        } else {
            $row["gcash_screenshot"] = "./dist/img/default.png"; // Default image if no screenshot
        }

        // Fetch client information from the clients table
        $clientQuery = "SELECT client_name, client_email, client_contact, client_address FROM clients WHERE client_id = ?";
        $clientStmt = $mysqli->prepare($clientQuery);
        $clientStmt->bind_param("i", $row['client_id']);
        $clientStmt->execute();
        $clientResult = $clientStmt->get_result();

        if ($clientData = $clientResult->fetch_assoc()) {
            // Merge the client data with reservation data
            $row = array_merge($row, $clientData);
        }

        // Format the response
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Reservation not found."]);
    }

    $stmt->close();
    $mysqli->close();
}
?>
