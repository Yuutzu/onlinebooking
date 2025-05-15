<?php
include('./config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selected_ids']) && !empty($_POST['selected_ids'])) {
        // Get selected category IDs
        $selected_ids = $_POST['selected_ids'];
        
        // Prepare placeholders for SQL statement
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));

        // Delete query
        $query = "DELETE FROM room_category WHERE category_id IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        // Bind parameters dynamically
        $stmt->bind_param(str_repeat('s', count($selected_ids)), ...$selected_ids);

        // Execute and check if deletion was successful
        if ($stmt->execute()) {
            echo "<script>
                alert('Selected categories have been successfully deleted.');
                window.location.href = 'category.php';
            </script>";
        } else {
            echo "<script>
                alert('Error deleting categories. Please try again.');
                window.location.href = 'category.php';
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            alert('No categories selected.');
            window.location.href = 'category.php';
        </script>";
    }
}
?>
