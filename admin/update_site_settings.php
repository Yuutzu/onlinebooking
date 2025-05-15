<?php
require('../admin/config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $site_name = $mysqli->real_escape_string($_POST['site_name']);
    $site_shortname = $mysqli->real_escape_string($_POST['site_shortname']);
    $site_welcome_text = $mysqli->real_escape_string($_POST['site_welcome_text']);
    $site_about_text1 = $mysqli->real_escape_string($_POST['site_about_text1']);
    $site_about_text2 = $mysqli->real_escape_string($_POST['site_about_text2']);
    $site_about_text3 = $mysqli->real_escape_string($_POST['site_about_text3']);
    $site_about_title1 = $mysqli->real_escape_string($_POST['site_about_title1']);
    $site_about_title2 = $mysqli->real_escape_string($_POST['site_about_title2']);
    $site_about_title3 = $mysqli->real_escape_string($_POST['site_about_title3']);
    $site_email = $mysqli->real_escape_string($_POST['site_email']);
    $site_contact = $mysqli->real_escape_string($_POST['site_contact']);
    $site_bg_color = $mysqli->real_escape_string($_POST['site_bg_color']);
    $site_primary_color = $mysqli->real_escape_string($_POST['site_primary_color']);
    $site_hover_color = $mysqli->real_escape_string($_POST['site_hover_color']);

    // Handle image uploads
    function uploadImage($file, $directory) {
        if (!empty($file['name'])) {
            $filename = uniqid() . '_' . basename($file['name']);
            $targetPath = $directory . $filename;

            // Validate file type (only allow JPG, PNG, and GIF)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($file['type'], $allowedTypes)) {
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    return $filename; // Return only the file name
                }
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

    // Start constructing the UPDATE query
    $query = "UPDATE site_settings SET 
        site_name = '$site_name',
        site_shortname = '$site_shortname',
        site_welcome_text = '$site_welcome_text',
        site_about_text1 = '$site_about_text1',
        site_about_text2 = '$site_about_text2',
        site_about_text3 = '$site_about_text3',
        site_about_title1 = '$site_about_title1',
        site_about_title2 = '$site_about_title2',
        site_about_title3 = '$site_about_title3',
        site_email = '$site_email',
        site_contact = '$site_contact',
        site_bg_color = '$site_bg_color',
        site_primary_color = '$site_primary_color',
        site_hover_color = '$site_hover_color'";

    // Check if `site_iframe_address` has a value before adding it to the query
    if (!empty($_POST['site_iframe_address'])) {
        $site_iframe_address = $mysqli->real_escape_string($_POST['site_iframe_address']);
        $query .= ", site_iframe_address = '$site_iframe_address'";
    }

    // Add uploaded image filenames to the update query if they exist
    if ($site_favicon) $query .= ", site_favicon = '$site_favicon'";
    if ($site_logo) $query .= ", site_logo = '$site_logo'";
    if ($site_about_image1) $query .= ", site_about_image1 = '$site_about_image1'";
    if ($site_about_image2) $query .= ", site_about_image2 = '$site_about_image2'";
    if ($site_about_image3) $query .= ", site_about_image3 = '$site_about_image3'";
    if ($carousel1) $query .= ", carousel1 = '$carousel1'";
    if ($carousel2) $query .= ", carousel2 = '$carousel2'";
    if ($carousel3) $query .= ", carousel3 = '$carousel3'";

    // Complete query with WHERE clause
    $query .= " WHERE id = 0";

    if ($mysqli->query($query)) {
        echo "<script>alert('Site settings updated successfully.'); window.location.href='admin_settings.php';</script>";
    } else {
        echo "<script>alert('Error updating site settings: " . $mysqli->error . "');</script>";
    }
} else {
    echo "<script>alert('Invalid request.');</script>";
}
?>
