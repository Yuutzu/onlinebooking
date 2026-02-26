<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    // Get form data
    $product_id = $_POST['product_id'];
    $product_category = $_POST['product_category'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_status = $_POST['product_status'];

    // Check if a new image is uploaded
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadHandler = new FileUploadHandler('./dist/img/');
        $result = $uploadHandler->upload($_FILES['product_image']);

        if (!$result['success']) {
            $status = 'error';
            header("Location: products.php?status=$status");
            exit;
        }

        $product_image = $result['filename'];

        // Update the product in the database, including the new image
        $query = "UPDATE products SET product_category = ?, product_name = ?, product_description = ?, product_price = ?, product_status = ?, product_image = ? WHERE product_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssi", $product_category, $product_name, $product_description, $product_price, $product_status, $product_image, $product_id);
    } else {
        // If no new image is uploaded, keep the existing image
        $product_image = $_POST['existing_image']; // The existing image is assumed to be passed in the form

        // Update the product in the database without changing the image
        $query = "UPDATE products SET product_category = ?, product_name = ?, product_description = ?, product_price = ?, product_status = ? WHERE product_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssssi", $product_category, $product_name, $product_description, $product_price, $product_status, $product_id);
    }

    // Execute the update
    if ($stmt->execute()) {
        $status = 'success'; // If successful, set the status to success
    } else {
        $status = 'error'; // If something goes wrong, set the status to error
    }

    // Redirect back to the products page after updating with status
    header("Location: products.php?status=$status");
}
?>