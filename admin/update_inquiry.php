<?php
require('../admin/config/config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inquiry_id = $_POST['inquiry_id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    // Prepare the update statement
    $sql = "UPDATE inquiry SET status = ?, remarks = ? WHERE inquiry_id = ?";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssi", $status, $remarks, $inquiry_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Inquiry updated successfully!", "id" => $inquiry_id, "status" => $status, "remarks" => $remarks]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating inquiry!"]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement!"]);
    }
    
    $mysqli->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request!"]);
}
