<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_ids"])) {
    $selected_ids = $_POST["selected_ids"];  // Get the selected request IDs from the form
    
    if (!empty($selected_ids)) {
        // Convert array to comma-separated string for SQL query
        $ids = implode(",", array_map('intval', $selected_ids));

        // Prepare delete query to delete from 'requests' table based on selected request IDs
        $query = "DELETE FROM requests WHERE request_id IN ($ids)";
        $stmt = $mysqli->prepare($query);

        if ($stmt->execute()) {
            // If the deletion is successful
            echo "<script>
                alert('Selected requests have been deleted successfully.');
                window.location.href = 'requests.php';  // Redirect to requests page
            </script>";
        } else {
            // If there is an error deleting the requests
            echo "<script>
                alert('Error deleting selected requests.');
                window.location.href = 'requests.php';  // Redirect to requests page
            </script>";
        }
    }
} else {
    // If no requests were selected
    echo "<script>
        alert('No requests selected.');
        window.location.href = 'requests.php';  // Redirect to requests page
    </script>";
}
?>
