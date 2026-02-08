<?php
require_once './config/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_ids"])) {
    $selected_ids = $_POST["selected_ids"];
    
    if (!empty($selected_ids)) {
        // Convert array to comma-separated string for SQL query
        $ids = implode(",", array_map('intval', $selected_ids));

        // Fetch client pictures before deleting records
        $query = "SELECT client_picture FROM clients WHERE id IN ($ids)";
        $stmt = $mysqli->prepare($query);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            $imagePath = "./dist/img/" . $row["client_picture"];
            if (file_exists($imagePath) && !empty($row["client_picture"])) {
                unlink($imagePath); // Delete image file
            }
        }

        // Prepare delete query
        $query = "DELETE FROM clients WHERE id IN ($ids)";
        $stmt = $mysqli->prepare($query);

        if ($stmt->execute()) {
            echo "<script>
                alert('Selected clients have been deleted successfully.');
                window.location.href = 'clients.php';
            </script>";
        } else {
            echo "<script>
                alert('Error deleting clients.');
                window.location.href = 'clients.php';
            </script>";
        }
    }
} else {
    echo "<script>
        alert('No clients selected.');
        window.location.href = 'clients.php';
    </script>";
}
?>
