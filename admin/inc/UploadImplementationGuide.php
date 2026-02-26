<?php
/**
 * SECURE FILE UPLOAD IMPLEMENTATION EXAMPLES
 * These are before/after examples for your existing upload handlers
 */

/**
 * ============================================
 * EXAMPLE 1: Client Registration (register.php)
 * ============================================
 */

// BEFORE (VULNERABLE):
/*
if (isset($_POST['register'])) {
    // ... other code ...
    $client_id_picture = $_FILES["client_id_picture"]["name"];
    move_uploaded_file($_FILES["client_id_picture"]["tmp_name"], "dist/img/" . $_FILES["client_id_picture"]["name"]);
    // ... saves file with original unsafe name
}
*/

// AFTER (SECURE):
/*
<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include_once('../admin/inc/password_helper.php');
include_once('../admin/inc/FileUploadHandler.php');
require('../admin/inc/alert.php');
require_once('../admin/inc/mailer_helper.php');

if (isset($_POST['register'])) {
    // Initialize file upload handler
    $uploadHandler = new FileUploadHandler(
        '../dist/img/',  // upload directory
        FileUploadHandler::ALLOWED_IMAGE_MIMES,
        FileUploadHandler::MAX_IMAGE_SIZE // 2MB limit
    );
    
    // Validate and upload ID picture
    if (!isset($_FILES['client_id_picture']) || $_FILES['client_id_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('ID picture is required.'); window.location.href='register.php';</script>";
        exit;
    }
    
    $uploadResult = $uploadHandler->upload($_FILES['client_id_picture']);
    
    if (!$uploadResult['success']) {
        echo "<script>alert('" . addslashes($uploadResult['message']) . "'); window.location.href='register.php';</script>";
        exit;
    }
    
    // Use secure filename
    $client_id_picture = $uploadResult['filename'];
    
    // ... rest of registration code ...
}
?>
*/

/**
 * ============================================
 * EXAMPLE 2: Walk-in Reservation (walkin_book.php)
 * ============================================
 */

// BEFORE (VULNERABLE):
/*
if (!empty($_FILES['client_id_image']['name'])) {
    $client_id_image = time() . "_" . uniqid() . "_" . basename($_FILES["client_id_image"]["name"]);
    $targetPath = $uploadDir . $client_id_image;
    if (!move_uploaded_file($_FILES["client_id_image"]["tmp_name"], $targetPath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload client ID image."]);
        exit;
    }
}
*/

// AFTER (SECURE):
/*
if (!empty($_FILES['client_id_image']['name'])) {
    $uploadHandler = new FileUploadHandler($uploadDir);
    $uploadResult = $uploadHandler->upload($_FILES['client_id_image']);
    
    if (!$uploadResult['success']) {
        echo json_encode(["success" => false, "message" => $uploadResult['message']]);
        exit;
    }
    
    $client_id_image = $uploadResult['filename'];
}
*/

/**
 * ============================================
 * EXAMPLE 3: Product Upload (add_product.php)
 * ============================================
 */

// BEFORE (VULNERABLE):
/*
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $image_name = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $image_path = 'dist/img/' . $image_name;
    
    if (move_uploaded_file($image_tmp, $image_path)) {
        // saves file with original name, no validation
    }
}
*/

// AFTER (SECURE):
/*
if (isset($_FILES['product_image'])) {
    $uploadHandler = new FileUploadHandler('dist/img/');
    $uploadResult = $uploadHandler->upload($_FILES['product_image']);
    
    if (!$uploadResult['success']) {
        echo "<script>alert('" . addslashes($uploadResult['message']) . "'); window.location.href='products.php';</script>";
        exit;
    }
    
    $image_name = $uploadResult['filename'];
    // Now proceed with database insert
} else {
    echo "<script>alert('Please select an image.'); window.location.href='products.php';</script>";
    exit;
}
*/

/**
 * ============================================
 * KEY CHANGES & SECURITY FEATURES
 * ============================================
 */

$securityFeatures = <<<'FEATURES'
1. COMPREHENSIVE MIME TYPE VALIDATION
   - Uses finfo_file() to detect actual file type (can't be spoofed)
   - Compares detected MIME type against whitelist
   - Ensures extension matches actual file content

2. STRICT FILE EXTENSION WHITELIST
   - Only allows: jpg, jpeg, jpe, png, gif, webp, bmp
   - Extensions checked against predefined whitelist
   - Extension validation happens at multiple levels

3. FILE SIZE LIMITS
   - Image files limited to 2MB (configurable)
   - Prevents large file uploads causing storage/DoS issues
   - Checks both PHP upload_max_filesize and custom limit

4. SECURE FILENAME GENERATION
   - Uses random_bytes() for cryptographic randomness
   - Format: timestamp_randomhex.extension
   - Original filename not retained (privacy + security)
   - Prevents directory traversal attacks

5. PROPER ERROR HANDLING
   - Clear error messages for validation failures
   - Returns status and filename for database storage
   - Graceful failure without exposing system details

6. PREVENT SCRIPT EXECUTION
   - Creates .htaccess to block PHP execution in upload dir
   - Blocks common executable extensions (.exe, .sh, .bat, etc)
   - File permissions set to 0644 (read-only)

7. VERIFICATION
   - is_uploaded_file() ensures file came from form upload
   - Checks for UPLOAD_ERR_OK status
   - Validates temporary file exists

FEATURES;

?>