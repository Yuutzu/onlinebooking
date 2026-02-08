<?php
include('../admin/config/config.php');

// Fetching products categorized as 'Food', 'Beverages', and 'Others' that are available
$categories = ['Food', 'Beverages', 'Others'];
$response = [];

foreach ($categories as $category) {
    // Modify the query to only fetch products with 'Available' status
    $query = "SELECT * FROM products WHERE product_category = '$category' AND product_status = 'Available'";
    $result = $mysqli->query($query);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    // Add the products to the response array using the lowercase category as the key
    $response[strtolower($category)] = $products;
}

// Fetching room services that are available
$serviceQuery = "SELECT * FROM room_services WHERE service_status = 'Available'";
$serviceResult = $mysqli->query($serviceQuery);
$services = [];
while ($service = $serviceResult->fetch_assoc()) {
    $services[] = $service;
}

// Add the services to the response array
$response['services'] = $services;

// Return the response as JSON
echo json_encode($response);
?>
