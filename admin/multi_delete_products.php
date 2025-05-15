<?php
include('./config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['selected_ids'])) {
        $selected_ids = $_POST['selected_ids'];

        // Convert array to comma-separated values for SQL query
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $sql = "DELETE FROM products WHERE product_id IN ($placeholders)";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param(str_repeat('s', count($selected_ids)), ...$selected_ids);
            if ($stmt->execute()) {
                echo "<script>alert('Selected products have been deleted successfully.'); window.location.href='products.php';</script>";
            } else {
                echo "<script>alert('Error occurred while deleting products.'); window.location.href='products.php';</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('No products selected for deletion.'); window.location.href='products.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='products.php';</script>";
}

$mysqli->close();
?>
