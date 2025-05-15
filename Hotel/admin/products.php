<?php

session_start();
include('./config/config.php');
include('./config/checklogin.php');
require('./inc/alert.php');


// Check if there's a status in the URL query string (success or error)
$status = isset($_GET['status']) ? $_GET['status'] : '';




if (isset($_GET['deleteProduct'])) {
    $id = $_GET['deleteProduct'];
    $adn = "DELETE FROM products WHERE product_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
        alert("success", "Product Deleted Successfully!");
    } else {
        alert("error", "Please Try Again");
    }
}


// Get the product ID from the URL parameter
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Fetch the product details
if ($product_id > 0) {
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product_data = $product_result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <!-- Include jQuery from a CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



    <script>
        function confirmDelete(url) {
            if (confirm("Are you sure you want to delete this product?")) {
                window.location.href = url; // Redirect if confirmed
            }
        }
    </script>

</head>

<body>





    <!-- HEADER -->
    <?php require('../admin/inc/side_header.php'); ?>

    <!-- Navigation -->
    <div class="col-lg-10 ms-auto">
        <?php require('./inc/nav.php'); ?>
    </div>


    <!-- Main Container -->
    <div class="container-fluid" id="main-content">

        <div class="row">
            <div class="col-lg-10 ms-auto">

                <!-- breadcrumbs -->
                <div class="mb-3 mt-4 ">
                    <h5 class="titleFont mb-1">Products</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb ">
                            <li class="breadcrumb-item linkFont"><a href="#" class="text-decoration-none" style="color: #333333;">Admin Dashboard</a></li>
                            <li class="breadcrumb-item linkFont active"><a href="#" class="text-decoration-none" style="color: #333333;">Products</a></li>
                        </ol>
                    </nav>
                </div>

<!-- Add and Search -->
<div class="mb-3 mt-5 d-flex align-items-end justify-content-between">
    <div style="width: 50%;">
        <label class="form-label someText m-0">Search: &nbsp;</label>
        <input type="text" name="search" id="search" class="form-control shadow-none w-50 someText">
    </div>

    <div>
        <a class="btn btn-primary someText btnAddCategory" data-bs-toggle="modal" data-bs-target="#addProductModal" href="services_add.php">+ Add</a>
        <button type="button" class="btn btn-danger someText mt-3 mb-3" id="deleteSelectedBtn">Delete Selected</button>
    </div>
</div>

<!-- Table -->
<div class="mt-5">
    <form action="multi_delete_products.php" method="POST" id="deleteForm">
        <table class="table table-striped table-hover table-responsive">
            <thead>
                <th scope="col" class="col-1"><input type="checkbox" id="selectAllCheckbox"></th>
                <th scope="col" class="col-1">No</th>
                <th scope="col" class="col-1">ID</th>
                <th scope="col" class="col-2">Category</th>
                <th scope="col" class="col-2">Name</th>
                <th scope="col" class="col-4">Description</th>
                <th scope="col" class="col-2">Price</th>
                <th scope="col" class="col-2">Status</th>
                <th scope="col">Image</th>
                <th scope="col">Operations</th>
            </thead>
            <tbody id="results">
                <!-- Results will be populated by AJAX -->
            </tbody>
        </table>
    </form>

    <!-- Pagination Placeholder -->
    <ul class="pagination" id="pagination">
        <!-- Pagination links will be injected here -->
    </ul>
</div>


<!-- Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product_category" class="form-label">Category</label>
                <select class="form-control" id="product_category" name="product_category" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Food">Food</option>
                    <option value="Beverages">Beverages</option>
                    <option value="Others">Others</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="mb-3">
                <label for="product_description" class="form-label">Description</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Price</label>
                <input type="number" class="form-control" id="product_price" name="product_price" required>
            </div>
            <div class="mb-3">
                <label for="product_status" class="form-label">Status</label>
                <select class="form-control" id="product_status" name="product_status" required>
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                    <option value="Out of stock">Out of stock</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="product_image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="product_image" name="product_image" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Modal for Updating Product -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Update Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for updating product -->
                <form action="update_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product_data['product_id']; ?>" readonly>

                    <div class="mb-3">
                        <label for="product_category" class="form-label">Category</label>
                        <select class="form-select" id="product_category" name="product_category" required>
                            <option value="Food" <?php echo ($product_data['product_category'] == 'Food') ? 'selected' : ''; ?>>Food</option>
                            <option value="Beverages" <?php echo ($product_data['product_category'] == 'Beverages') ? 'selected' : ''; ?>>Beverages</option>
                            <option value="Others" <?php echo ($product_data['product_category'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_data['product_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="product_description" class="form-label">Description</label>
                        <textarea class="form-control" id="product_description" name="product_description" required><?php echo htmlspecialchars($product_data['product_description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="product_price" class="form-label">Price</label>
                        <input type="text" class="form-control" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product_data['product_price']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="product_status" class="form-label">Status</label>
                        <select class="form-select" id="product_status" name="product_status" required>
                            <option value="Available" <?php echo ($product_data['product_status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                            <option value="Unavailable" <?php echo ($product_data['product_status'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                            <option value="Out of stock" <?php echo ($product_data['product_status'] == 'Out of stock') ? 'selected' : ''; ?>>Out of stock</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="product_image" name="product_image" onchange="previewImage(event)">
                        <small>Current Image: <img id="current_image" src="./dist/img/<?php echo htmlspecialchars($product_data['product_image']); ?>" style="width: 100px; height: auto;"></small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript function to update the image preview when a new image is selected
function previewImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const imagePreview = document.getElementById('current_image');
        imagePreview.src = e.target.result; // Set the new image as preview
    };
    
    reader.readAsDataURL(file); // Convert the selected image to base64 format and preview
}
</script>


<script>
    // Check if a product ID is provided in the URL and open the modal
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('product_id');
        if (productId) {
            // Show the modal automatically
            var myModal = new bootstrap.Modal(document.getElementById('productModal'), {
                keyboard: false
            });
            myModal.show();
        }
    };
</script>

<!-- JavaScript -->
<script>
    document.getElementById('selectAllCheckbox').addEventListener('change', function () {
        document.querySelectorAll('.deleteCheckbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('deleteSelectedBtn').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('.deleteCheckbox:checked');
        if (checkboxes.length === 0) {
            alert("Please select at least one products to delete.");
        } else {
            if (confirm("Are you sure you want to delete the selected products? This action cannot be undone.")) {
                document.getElementById('deleteForm').submit();
            }
        }
    });
</script>

            </div>
        </div>
    </div>

    <!-- Importing AJAX for Search -->
    <?php require('./ajax/products_ajax.php'); ?>

    <script>
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.deleteCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>


    <?php if ($status == 'success'): ?>
        <script>
            alert('Product updated successfully!');
        </script>
    <?php elseif ($status == 'error'): ?>
        <script>
            alert('Sorry, there was an error updating the product.');
        </script>
    <?php endif; ?>

</body>



</html>