<?php
session_start();
require('../admin/config/config.php');

// Fetch site settings from the database
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_name']; ?> | Site Settings</title>

    <!-- Dynamically load favicon -->
    <?php if (!empty($settings['site_favicon'])) : ?>
        <link rel="icon" type="image/png" href="./dist/img/logos/<?php echo $settings['site_favicon']; ?>">
    <?php endif; ?>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>

    <style>
        .nav-tabs {
            gap: 10px; /* Adds space between tab buttons */
            display: flex; /* Ensures gap works properly */
        }
        .nav-tabs .nav-link {
            border: none;
            color: #333;
            padding: 10px 15px;
            margin-right: 5px; /* Adds spacing between tabs */
        }
        .nav-tabs .nav-link.active {
            background-color: #4a1c1d;
            color: white;
            border-radius: 5px;
        }
        .tab-content {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .site-logo {
            width: 150px;
            height: auto;
        }
    </style>
</head>

<body>
<?php
// HEADER
require('../admin/inc/side_header.php');
?>

<div class="col-lg-10 ms-auto">
  <?php require('./inc/nav.php'); ?>

  <div class="container mt-4">
    <form id="settingsForm" action="update_site_settings.php" method="POST" enctype="multipart/form-data">
      <ul class="nav nav-tabs" id="settingsTabs">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#general">General Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#siteImages">Site Images</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#info">Information Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#theme">Theme Settings</a>
        </li>
      </ul>

      <div class="tab-content mt-3">
        <!-- General Settings -->
        <div id="general" class="tab-pane fade show active">
          <h5>General Settings</h5>
          <div class="mb-3">
            <label>Site Name</label>
            <input type="text" name="site_name" class="form-control" value="<?php echo $settings['site_name']; ?>" required>
          </div>
          <div class="mb-3">
            <label>Site Short Name</label>
            <input type="text" name="site_shortname" class="form-control" value="<?php echo $settings['site_shortname']; ?>" required>
          </div>
          <div class="mb-3 d-flex align-items-center">
            <img id="faviconPreview" src="./dist/img/logos/<?php echo $settings['site_favicon']; ?>" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
            <div class="flex-grow-1">
              <label>Site Favicon</label>
              <input type="file" name="site_favicon" class="form-control" onchange="previewImage(this, 'faviconPreview')">
            </div>
          </div>
          <div class="mb-3 d-flex align-items-center">
            <img id="logoPreview" src="./dist/img/logos/<?php echo $settings['site_logo']; ?>" class="img-thumbnail me-3" style="width: 100px; height: 50px; object-fit: cover;">
            <div class="flex-grow-1">
              <label>Site Logo</label>
              <input type="file" name="site_logo" class="form-control" onchange="previewImage(this, 'logoPreview')">
            </div>
          </div>
        </div>

        <!-- Site Images -->
        <div id="siteImages" class="tab-pane fade">
          <h5>Site Images</h5>
          <?php
          $carousel_images = ['carousel1', 'carousel2', 'carousel3'];
          $about_images = ['site_about_image1', 'site_about_image2', 'site_about_image3'];
          foreach ($carousel_images as $index => $image) {
            echo '<div class="mb-3 d-flex align-items-center">
                    <img id="' . $image . 'Preview" src="./dist/img/carousels/' . $settings[$image] . '" class="img-thumbnail me-3" style="width: 150px; height: 100px; object-fit: cover;">
                    <div class="flex-grow-1">
                      <label>Carousel ' . ($index + 1) . '</label>
                      <input type="file" name="' . $image . '" class="form-control" onchange="previewImage(this, \'' . $image . 'Preview\')">
                    </div>
                  </div>';
          }
          foreach ($about_images as $index => $image) {
            echo '<div class="mb-3 d-flex align-items-center">
                    <img id="' . $image . 'Preview" src="./dist/img/about/' . $settings[$image] . '" class="img-thumbnail me-3" style="width: 150px; height: 100px; object-fit: cover;">
                    <div class="flex-grow-1">
                      <label>About Us Image ' . ($index + 1) . '</label>
                      <input type="file" name="' . $image . '" class="form-control" onchange="previewImage(this, \'' . $image . 'Preview\')">
                    </div>
                  </div>';
          }
          ?>
        </div>

        <!-- Information Settings -->
        <div id="info" class="tab-pane fade">
          <h5>Information Settings</h5>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="site_email" class="form-control" value="<?php echo $settings['site_email']; ?>">
          </div>
          <div class="mb-3">
            <label>Contact</label>
            <input type="text" name="site_contact" class="form-control" value="<?php echo $settings['site_contact']; ?>">
          </div>
          <div class="mb-3">
            <label>Iframe Address</label>
            <input type="text" name="site_iframe_address" class="form-control">
            <small class="text-muted">Please leave blank if you don't want to change the address.</small>
          </div>

          <div class="mb-3">
            <label>Site Welcome Text</label>
            <input type="text" name="site_welcome_text" class="form-control" value="<?php echo $settings['site_welcome_text']; ?>">
          </div>
          <?php for ($i = 1; $i <= 3; $i++) { ?>
            <div class="mb-3">
              <label>About Us Title <?php echo $i; ?></label>
              <input type="text" name="site_about_title<?php echo $i; ?>" class="form-control" value="<?php echo $settings['site_about_title' . $i]; ?>">
            </div>
            <div class="mb-3">
              <label>About Us Text <?php echo $i; ?></label>
              <input type="text" name="site_about_text<?php echo $i; ?>" class="form-control" value="<?php echo $settings['site_about_text' . $i]; ?>">
            </div>
          <?php } ?>
        </div>

        <!-- Theme Settings -->
        <div id="theme" class="tab-pane fade">
          <h5>Theme Settings</h5>
          <div class="mb-3">
            <label>Site Background Color</label>
            <input type="color" name="site_bg_color" class="form-control form-control-color" value="<?php echo $settings['site_bg_color']; ?>">
          </div>
          <div class="mb-3">
            <label>Site Primary Color</label>
            <input type="color" name="site_primary_color" class="form-control form-control-color" value="<?php echo $settings['site_primary_color']; ?>">
          </div>
          <div class="mb-3">
            <label>Site Hover Color</label>
            <input type="color" name="site_hover_color" class="form-control form-control-color" value="<?php echo $settings['site_hover_color']; ?>">
          </div>
        </div>
      </div>

      <div class="mt-4 text-end">
        <button type="button" class="btn btn-primary" onclick="showPasswordModal()">Save Changes</button>
      </div>
    </form>
  </div>
</div>



    <!-- Admin Password Modal -->
    <div class="modal fade" id="adminPasswordModal" tabindex="-1" aria-labelledby="adminPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="passwordForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Admin Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="adminPassword">Admin Password</label>
                        <input type="password" class="form-control" id="adminPassword" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- JavaScript for Modal Handling -->
<script>
    function showPasswordModal() {
        const modalElement = document.getElementById('adminPasswordModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        // Clear the input when the modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            document.getElementById('adminPassword').value = '';
        });
    }

    document.getElementById('passwordForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const adminPassword = document.getElementById('adminPassword').value;

        // AJAX request for password verification
        fetch('verify_admin_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'admin_password=' + encodeURIComponent(adminPassword)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (confirm("Are you sure you want to save changes?")) {
                    document.getElementById('settingsForm').submit();
                }
            } else {
                alert(data.message || 'Incorrect Admin Password.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again later.');
        });
    });
</script>



<script>
  function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

</body>



</html>
