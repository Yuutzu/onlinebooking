<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');

$client_id = $_SESSION['client_id'];

// Fetch client details
$ret = "SELECT id, client_name, client_picture, client_id_picture FROM clients WHERE id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Profile Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Profile Picture Debug Info</h4>
            </div>
            <div class="card-body">
                <h5>Database Values:</h5>
                <table class="table">
                    <tr>
                        <td><strong>Client Name:</strong></td>
                        <td><?= htmlspecialchars($row->client_name) ?></td>
                    </tr>
                    <tr>
                        <td><strong>client_picture (from DB):</strong></td>
                        <td><code><?= htmlspecialchars($row->client_picture ?: 'NULL/EMPTY') ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>client_id_picture (from DB):</strong></td>
                        <td><code><?= htmlspecialchars($row->client_id_picture ?: 'NULL/EMPTY') ?></code></td>
                    </tr>
                </table>

                <h5 class="mt-4">File System Check:</h5>
                <p><strong>Image Directory:</strong> <code><?= realpath('../admin/dist/img/') ?></code></p>
                <?php
                $img_dir = '../admin/dist/img/';
                if (is_dir($img_dir)) {
                    echo '<p class="text-success">✅ Image directory exists and is readable</p>';

                    // List uploaded files
                    $files = array_diff(scandir($img_dir), ['.', '..']);
                    if (!empty($files)) {
                        echo '<p><strong>Files in directory:</strong></p>';
                        echo '<ul>';
                        foreach (array_slice($files, -10) as $file) { // Show last 10 files
                            echo '<li>' . htmlspecialchars($file) . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p class="text-warning">⚠️ No files found in directory</p>';
                    }
                } else {
                    echo '<p class="text-danger">❌ Image directory does not exist!</p>';
                }
                ?>

                <h5 class="mt-4">Image Display Test:</h5>
                <?php
                $picture = $row->client_picture ?: 'avatar.jpg';
                $full_path = '../admin/dist/img/' . $picture;
                if (file_exists($full_path)) {
                    echo '<p class="text-success">✅ File exists: <code>' . htmlspecialchars($full_path) . '</code></p>';
                    echo '<img src="' . $full_path . '" style="width: 150px; height: 150px; object-fit: cover;" class="rounded-circle">';
                } else {
                    echo '<p class="text-danger">❌ File NOT found: <code>' . htmlspecialchars($full_path) . '</code></p>';
                }
                ?>

                <h5 class="mt-4">Solutions:</h5>
                <ol>
                    <li>If <strong>client_picture is NULL/EMPTY</strong>: User needs to re-upload picture in profile
                        edit</li>
                    <li>If <strong>no files in directory</strong>: Check file upload permissions on
                        <code><?= $img_dir ?></code></li>
                    <li>If <strong>directory doesn't exist</strong>: Create it manually with:
                        <code>mkdir ../admin/dist/img/</code></li>
                    <li>If <strong>avatar.jpg missing</strong>: Upload a default avatar image to that directory</li>
                </ol>

                <a href="profile.php" class="btn btn-primary mt-3">Back to Profile</a>
            </div>
        </div>
    </div>
</body>

</html>