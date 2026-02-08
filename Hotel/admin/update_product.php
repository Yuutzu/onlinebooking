<?php
include('./config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $product_id = $_POST['product_id'];
    $product_category = $_POST['product_category'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_status = $_POST['product_status'];

    // Check if a new image is uploaded
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        // Process the new image
        $product_image = $_FILES['product_image']['name'];
        $target_directory = "./dist/img/";
        $target_file = $target_directory . basename($product_image);

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            // If image upload is successful, use the new image name
            // echo "Image uploaded successfully!"; // You can remove this echo.
        } else {
            // If upload fails, display an error message
            // echo "Sorry, there was an error uploading your image."; // Remove this echo too.
            $status = 'error';
            header("Location: products.php?status=$status");
            exit;
        }

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
