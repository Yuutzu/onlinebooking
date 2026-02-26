<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');
require_once('./inc/CSRFToken.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    // Get form values
    $product_category = $_POST['product_category'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_status = $_POST['product_status'];

    // Generate a 6-digit random number for product_id
    $product_id = mt_rand(100000, 999999); // Generates a random 6-digit number

    // Handle file upload
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('Please select an image.'); window.location.href='products.php';</script>";
        exit;
    }

    $uploadHandler = new FileUploadHandler('dist/img/');
    $uploadResult = $uploadHandler->upload($_FILES['product_image']);

    if (!$uploadResult['success']) {
        echo "<script>alert('" . addslashes($uploadResult['message']) . "'); window.location.href='products.php';</script>";
        exit;
    }

    $image_name = $uploadResult['filename'];

    // File uploaded successfully, now insert data into database
    $sql = "INSERT INTO products (product_id, product_category, product_name, product_description, product_price, product_image, product_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('issssss', $product_id, $product_category, $product_name, $product_description, $product_price, $image_name, $product_status);
        if ($stmt->execute()) {
            echo "<script>alert('New product added successfully!'); window.location.href='products.php';</script>";
        } else {
            echo "<script>alert('Error occurred while adding product.'); window.location.href='products.php';</script>";
        }
        $stmt->close();
    }
}
?>