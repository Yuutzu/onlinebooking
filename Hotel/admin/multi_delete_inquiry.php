<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_ids"])) {
    $selected_ids = $_POST["selected_ids"];
    
    if (!empty($selected_ids)) {
        // Convert array to comma-separated string for SQL query
        $ids = implode(",", array_map('intval', $selected_ids));

        // Prepare delete query
        $query = "DELETE FROM inquiry WHERE inquiry_id IN ($ids)";
        $stmt = $mysqli->prepare($query);

        if ($stmt->execute()) {
            echo "<script>
                alert('Selected inquiries have been deleted successfully.');
                window.location.href = 'inquiry.php';
            </script>";
        } else {
            echo "<script>
                alert('Error deleting inquiries.');
                window.location.href = 'inquiry.php';
            </script>";
        }
    }
} else {
    echo "<script>
        alert('No inquiries selected.');
        window.location.href = 'inquiry.php';
    </script>";
}
?>
