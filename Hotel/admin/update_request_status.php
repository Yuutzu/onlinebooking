<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['status'])) {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];

    // Update the request status in the database
    $query = "UPDATE requests SET status = ? WHERE request_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $status, $requestId);

    if ($stmt->execute()) {
        echo "<script>
            alert('Request status updated successfully.');
            window.location.href = 'requests.php';  // Redirect to requests page
        </script>";
    } else {
        echo "<script>
            alert('Error updating request status.');
            window.location.href = 'requests.php';  // Redirect to requests page
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request.');
        window.location.href = 'requests.php';  // Redirect to requests page
    </script>";
}
?>
