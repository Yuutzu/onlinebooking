<?php
include('./config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_ids'])) {
    $selected_ids = $_POST['selected_ids'];

    // Convert selected IDs to a comma-separated string for SQL query
    $ids_string = implode(',', array_map('intval', $selected_ids));

    // Prepare DELETE query
    $deleteQuery = "DELETE FROM rooms WHERE room_id IN ($ids_string)";
    $stmt = $mysqli->prepare($deleteQuery);

    if ($stmt->execute()) {
        echo "<script>
                alert('Selected rooms have been deleted successfully.');
                window.location.href = 'rooms.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting rooms. Please try again.');
                window.location.href = 'rooms.php';
              </script>";
    }

    $stmt->close();
} else {
    echo "<script>
            alert('No rooms were selected.');
            window.location.href = 'rooms.php';
          </script>";
}
?>
