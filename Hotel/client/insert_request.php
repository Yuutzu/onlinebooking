<?php
// Include the MySQL connection file
include('../admin/config/config.php');

// Check if the request data is coming through POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $request_id = $_POST['request_id'];
    $reservation_id = $_POST['reservation_id'];
    $products = $_POST['products'];
    $services = $_POST['services'];
    $total_price = $_POST['total_price'];
    $status = $_POST['status']; // 'pending' in this case

    // Validate required fields
    if (empty($request_id) || empty($reservation_id) || empty($products) || empty($total_price) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Prepare SQL query to insert data into the 'requests' table
    $query = "INSERT INTO requests (request_id, reservation_id, products, services, total_price, status) 
              VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the parameters to the SQL query
        $stmt->bind_param('isssss', $request_id, $reservation_id, $products, $services, $total_price, $status);

        // Execute the query
        if ($stmt->execute()) {
            // Successfully inserted the request
            echo json_encode(['status' => 'success', 'message' => 'Request submitted successfully']);
        } else {
            // Error while executing the query
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit the request']);
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Error preparing the query
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the query']);
    }

    // Close the database connection
    $mysqli->close();
} else {
    // If the request is not POST, show an error message
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
