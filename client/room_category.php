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

$id = isset($_GET['category_name']) ? trim($_GET['category_name']) : '';
$client_id = $_SESSION['client_id'];

// Validate category name
if (empty($id)) {
    header('Location: index.php');
    exit();
}

// Pagination setup
$limit = 6;  // Number of records per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;  // Current page number
$offset = ($page - 1) * $limit;  // Calculate offset

// Get the total number of rooms for the category to calculate the number of pages using prepared statement
$totalQuery = "SELECT COUNT(*) as total FROM rooms WHERE room_status = 'Available' AND room_category = ?";
$totalStmt = $mysqli->prepare($totalQuery);
$totalStmt->bind_param('s', $id);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRows = $totalResult->fetch_object()->total;
$totalPages = ceil($totalRows / $limit);  // Calculate total pages
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> | Room Category</title>

    <!-- Dynamically load favicon if available -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png"
            href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>
</head>

<body>
    <!-- Navigation -->
    <?php require('./inc/nav.php'); ?>

    <div class="container-fluid">
        <!-- Our Rooms -->
        <div class="row" style="background-color:#f5f5f5;">
            <div class="container py-5" style="padding: 20px;">
                <div class="col-lg-8 m-auto text-center py-5">
                    <div class="container mt-5 py-5">
                        <div class="mb-5">
                            <h5 class="bigTitle"><?php echo htmlspecialchars($id); ?></h5>
                        </div>
                        <div class="col-lg-12 d-flex justify-content-center" style="flex-wrap:wrap;">
                            <?php
                            // Query for rooms with LIMIT and OFFSET for pagination using prepared statement
                            $ret = "SELECT * FROM rooms WHERE room_status = 'Available' AND room_category = ? ORDER BY RAND() LIMIT ? OFFSET ?";
                            $stmt = $mysqli->prepare($ret);
                            $stmt->bind_param('sii', $id, $limit, $offset);
                            $stmt->execute();
                            $res = $stmt->get_result();

                            while ($row1 = $res->fetch_object()) {
                                ?>
                                <div class="card mb-4 me-3" style="width: 18rem;">
                                    <img src="../admin/dist/img/<?php echo htmlspecialchars($row1->room_picture); ?>"
                                        class="card-img-top" style="height: 200px; object-fit:cover;">
                                    <div class="card-body">
                                        <p class="miniTitle"><?php echo htmlspecialchars($row1->room_category); ?></p>
                                        <h5 class="cardRoomTitle"><?php echo htmlspecialchars($row1->room_name); ?></h5>
                                        <p class="cardRoomDescription">
                                            <?php echo htmlspecialchars($row1->room_description); ?></p>

                                        <div class="container d-flex justify-content-center">
                                            <p class="cardRoomDescription"><b>ADULTS: </b>
                                                <?php echo htmlspecialchars($row1->room_adult); ?></p>
                                            &nbsp; &nbsp;
                                            <p class="cardRoomDescription"><b>CHILD: </b>
                                                <?php echo htmlspecialchars($row1->room_child); ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-3 mt-0">
                                        <a href="room_details.php?room_id=<?php echo htmlspecialchars($row1->room_id); ?>"
                                            class="btn btn-primary btnAddCategory someText">Check More Details</a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="d-flex justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                                <a href="?category_name=<?php echo urlencode($id); ?>&page=<?php echo $page - 1; ?>"
                                    class="btn btn-primary btnAddCategory someText">Previous</a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?category_name=<?php echo urlencode($id); ?>&page=<?php echo $page + 1; ?>"
                                    class="btn btn-primary btnAddCategory someText ms-2">Next</a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>