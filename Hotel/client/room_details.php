<?php
error_reporting(E_ALL & ~E_WARNING);
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
require('../admin/inc/alert.php');

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

$room_id = $_GET['room_id'];
$client_id = $_SESSION['client_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($settings['site_name']); ?> | Room Details</title>
  <?php if (!empty($settings['site_favicon'])): ?>
      <link rel="icon" type="image/png" href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
  <?php endif; ?>
  <!-- Import Links -->
  <?php require('./inc/links.php'); ?>
</head>

<body>
  <!-- Navigation -->
  <?php require('./inc/nav.php'); ?>

  <?php
  $ret = "SELECT * FROM rooms WHERE room_id = '$room_id'";
  $stmt = $mysqli->prepare($ret);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row1 = $res->fetch_object()) {
  ?>
    <div class="container-fluid">
      <div class="row">
        <div class="container mt-5 mb-5 m-auto">
          <div class="col-lg-12 m-auto d-flex justify-content-center mt-5">
            <div class="col-4 mt-5">
              <div class="roomContainer">
                <img src="../admin/dist/img/<?php echo htmlspecialchars($row1->room_picture); ?>" style="object-fit: cover; width: 100%; height: 100%;">
              </div>
            </div>
            <div class="col-4 p-4 mt-5">
              <p class="miniTitle"><?php echo htmlspecialchars($row1->room_category); ?></p>
              <h5 class="bigTitle mb-0" style="font-size: 30px;"><?php echo htmlspecialchars($row1->room_name); ?></h5>
              <p class="contentPara"><b><?php echo htmlspecialchars($row1->room_status); ?></b></p>
              <hr class="mb-3">
              <p class="contentPara">
                <b>DESCRIPTION:</b> <br>
                <?php echo htmlspecialchars($row1->room_description); ?>
              </p>
              <p class="contentPara mb-0">
                <b>ADULT:</b> &nbsp;&nbsp; <?php echo htmlspecialchars($row1->room_adult); ?>
              </p>
              <p class="contentPara mb-5">
                <b>CHILD:</b> &nbsp;&nbsp; <?php echo htmlspecialchars($row1->room_child); ?>
              </p>
              <div class="mt-3">
                <p class="contentPara mb-0">
                  <b>PRICE:</b> &nbsp;&nbsp;
                </p>
                <h5 class="newBigTitle mb-4">₱ <?php echo htmlspecialchars($row1->room_price); ?></h5>&nbsp;&nbsp;
              </div>
              <form method="POST">
                <div class="col-4 mt-2 d-grid">
                  <?php if (isset($_SESSION['client_id'])) { ?>
                    <a href="room_book.php?room_id=<?php echo htmlspecialchars($row1->room_id); ?>" class="btn btn-primary btnAddCategory someText">Book Now</a>
                  <?php } else { ?>
                    <a href="login.php" class="btn btn-primary btnAddCategory someText">Book Now</a>
                  <?php } ?>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php } ?>
      </div>

      <!-- Our Rooms -->
      <div class="row" style="background-color:#f5f5f5;">
        <div class="container py-5" style="padding: 20px;">
          <div class="col-lg-8 m-auto text-center py-5">
            <p class="miniTitle">LOOKING FOR A PLACE TO STAY?</p>
            <h5 class="bigTitle mb-5">OTHER ROOMS YOU MAY LIKE</h5>
            <div class="container">
              <div class="col-lg-12 d-flex justify-content-around" style="flex-wrap:wrap;">
                <?php
                $ret = "SELECT * FROM rooms WHERE room_status = 'Available' ORDER BY RAND() LIMIT 6";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row1 = $res->fetch_object()) {
                ?>
                  <div class="card mb-4" style="width: 18rem;">
                    <img src="../admin/dist/img/<?php echo htmlspecialchars($row1->room_picture); ?>" class="card-img-top" style="height: 200px; object-fit:cover;">
                    <div class="card-body">
                      <p class="miniTitle"><?php echo htmlspecialchars($row1->room_category); ?></p>
                      <h5 class="cardRoomTitle"><?php echo htmlspecialchars($row1->room_name); ?></h5>
                      <p class="cardRoomDescription"><?php echo htmlspecialchars($row1->room_description); ?></p>
                      <div class="container d-flex justify-content-center">
                        <p class="cardRoomDescription"><b>ADULTS: </b> <?php echo htmlspecialchars($row1->room_adult); ?></p>
                        &nbsp; &nbsp;
                        <p class="cardRoomDescription"><b>CHILD: </b> <?php echo htmlspecialchars($row1->room_child); ?></p>
                      </div>
                    </div>
                    <div class="mb-3 mt-0">
                      <a href="room_details.php?room_id=<?php echo htmlspecialchars($row1->room_id); ?>" class="btn btn-primary btnAddCategory someText">Check More Details</a>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
