<?php
include('./config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form values
    $product_category = $_POST['product_category'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_status = $_POST['product_status'];

    // Generate a 6-digit random number for product_id
    $product_id = mt_rand(100000, 999999); // Generates a random 6-digit number

    // Handle file upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_name = $_FILES['product_image']['name'];
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_path = 'dist/img/' . $image_name;

        // Move uploaded file to the desired directory
        if (move_uploaded_file($image_tmp, $image_path)) {
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
        } else {
            echo "<script>alert('Error uploading product image.'); window.location.href='products.php';</script>";
        }
    } else {
        echo "<script>alert('Please select an image.'); window.location.href='products.php';</script>";
    }
}
?>
