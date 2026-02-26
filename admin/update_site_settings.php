<?php
require('../admin/config/config.php');
require_once('../admin/inc/FileUploadHandler.php');
require_once('../admin/inc/CSRFToken.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    CSRFToken::verifyOrDie();

    // Get and validate input data (use prepared statements)
    $site_name = $_POST['site_name'] ?? '';
    $site_shortname = $_POST['site_shortname'] ?? '';
    $site_welcome_text = $_POST['site_welcome_text'] ?? '';
    $site_about_text1 = $_POST['site_about_text1'] ?? '';
    $site_about_text2 = $_POST['site_about_text2'] ?? '';
    $site_about_text3 = $_POST['site_about_text3'] ?? '';
    $site_about_title1 = $_POST['site_about_title1'] ?? '';
    $site_about_title2 = $_POST['site_about_title2'] ?? '';
    $site_about_title3 = $_POST['site_about_title3'] ?? '';
    $site_email = $_POST['site_email'] ?? '';
    $site_contact = $_POST['site_contact'] ?? '';
    $site_bg_color = $_POST['site_bg_color'] ?? '';
    $site_primary_color = $_POST['site_primary_color'] ?? '';
    $site_hover_color = $_POST['site_hover_color'] ?? '';
    $site_iframe_address = $_POST['site_iframe_address'] ?? '';

    // Handle image uploads
    function uploadImage($file, $directory)
    {
        if (!empty($file['name'])) {
            $uploadHandler = new FileUploadHandler($directory);
            $result = $uploadHandler->upload($file);
            if ($result['success']) {
                return $result['filename'];
            }
        }
        return null;
    }

    $site_favicon = uploadImage($_FILES['site_favicon'], './dist/img/logos/');
    $site_logo = uploadImage($_FILES['site_logo'], './dist/img/logos/');
    $site_about_image1 = uploadImage($_FILES['site_about_image1'], './dist/img/about/');
    $site_about_image2 = uploadImage($_FILES['site_about_image2'], './dist/img/about/');
    $site_about_image3 = uploadImage($_FILES['site_about_image3'], './dist/img/about/');
    $carousel1 = uploadImage($_FILES['carousel1'], './dist/img/carousels/');
    $carousel2 = uploadImage($_FILES['carousel2'], './dist/img/carousels/');
    $carousel3 = uploadImage($_FILES['carousel3'], './dist/img/carousels/');

    // Use prepared statement to prevent SQL injection
    $query = "UPDATE site_settings SET 
        site_name = ?,
        site_shortname = ?,
        site_welcome_text = ?,
        site_about_text1 = ?,
        site_about_text2 = ?,
        site_about_text3 = ?,
        site_about_title1 = ?,
        site_about_title2 = ?,
        site_about_title3 = ?,
        site_email = ?,
        site_contact = ?,
        site_bg_color = ?,
        site_primary_color = ?,
        site_hover_color = ?,
        site_iframe_address = ?";

    // Add image fields only if they were uploaded
    $types = "sssssssssssssss"; // 15 string parameters
    $params = [$site_name, $site_shortname, $site_welcome_text, $site_about_text1, $site_about_text2, $site_about_text3, $site_about_title1, $site_about_title2, $site_about_title3, $site_email, $site_contact, $site_bg_color, $site_primary_color, $site_hover_color, $site_iframe_address];

    // Add image parameters conditionally
    if ($site_favicon) {
        $query .= ", site_favicon = ?";
        $types .= "s";
        $params[] = $site_favicon;
    }
    if ($site_logo) {
        $query .= ", site_logo = ?";
        $types .= "s";
        $params[] = $site_logo;
    }
    if ($site_about_image1) {
        $query .= ", site_about_image1 = ?";
        $types .= "s";
        $params[] = $site_about_image1;
    }
    if ($site_about_image2) {
        $query .= ", site_about_image2 = ?";
        $types .= "s";
        $params[] = $site_about_image2;
    }
    if ($site_about_image3) {
        $query .= ", site_about_image3 = ?";
        $types .= "s";
        $params[] = $site_about_image3;
    }
    if ($carousel1) {
        $query .= ", carousel1 = ?";
        $types .= "s";
        $params[] = $carousel1;
    }
    if ($carousel2) {
        $query .= ", carousel2 = ?";
        $types .= "s";
        $params[] = $carousel2;
    }
    if ($carousel3) {
        $query .= ", carousel3 = ?";
        $types .= "s";
        $params[] = $carousel3;
    }

    // Complete query with WHERE clause
    $query .= " WHERE id = 0";

    // Prepare and execute statement
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $mysqli->error . "');</script>";
    } else {
        // Bind parameters dynamically
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo "<script>alert('Site settings updated successfully.'); window.location.href='admin_settings.php';</script>";
        } else {
            echo "<script>alert('Error updating site settings: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
} else {
    echo "<script>alert('Invalid request.');</script>";
}
?>