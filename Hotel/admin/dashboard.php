<?php
include('./config/config.php');

// Fetch total bookings
$totalBookingsQuery = "SELECT COUNT(*) AS total FROM reservations";
$totalBookingsResult = $mysqli->query($totalBookingsQuery);
$totalBookings = $totalBookingsResult->fetch_assoc()['total'];

// Fetch total revenue
$totalRevenueQuery = "SELECT SUM(total_price) AS total FROM reservations WHERE reservation_status = 'confirmed'";
$totalRevenueResult = $mysqli->query($totalRevenueQuery);
$totalRevenue = $totalRevenueResult->fetch_assoc()['total'];

// Fetch available rooms
$availableRoomsQuery = "SELECT COUNT(*) AS total FROM rooms WHERE room_status = 'available'";
$availableRoomsResult = $mysqli->query($availableRoomsQuery);
$availableRooms = $availableRoomsResult->fetch_assoc()['total'];

// Fetch bookings per month
$bookingsPerMonthQuery = "SELECT MONTH(created_at) AS month, COUNT(*) AS count FROM reservations GROUP BY MONTH(created_at)";
$bookingsPerMonthResult = $mysqli->query($bookingsPerMonthQuery);
$bookingsData = [];
while ($row = $bookingsPerMonthResult->fetch_assoc()) {
    $bookingsData[$row['month']] = $row['count'];
}

// Fetch revenue per month
$revenuePerMonthQuery = "SELECT MONTH(created_at) AS month, SUM(total_price) AS total FROM reservations WHERE reservation_status = 'confirmed' GROUP BY MONTH(created_at)";
$revenuePerMonthResult = $mysqli->query($revenuePerMonthQuery);
$revenueData = [];
while ($row = $revenuePerMonthResult->fetch_assoc()) {
    $revenueData[$row['month']] = $row['total'];
}

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$bookingsChartData = [];
$revenueChartData = [];

for ($i = 1; $i <= 12; $i++) {
    $bookingsChartData[] = $bookingsData[$i] ?? 0;
    $revenueChartData[] = $revenueData[$i] ?? 0;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard</title>

    <!-- Important Links -->
    <?php require('./inc/links.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php require('../admin/inc/side_header.php'); ?>
    <div class="col-lg-10 ms-auto">
        <?php require('./inc/nav.php'); ?>
        <div class="container mt-4">
            <h3 class="mb-4">Dashboard Analytics</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow p-3">
                        <h5>Total Bookings</h5>
                        <h2><?php echo $totalBookings; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow p-3">
                        <h5>Total Revenue</h5>
                        <h2>₱<?php echo number_format($totalRevenue, 2); ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow p-3">
                        <h5>Available Rooms</h5>
                        <h2><?php echo $availableRooms; ?></h2>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow p-3">
                        <h5>Bookings Over Time</h5>
                        <canvas id="bookingsChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow p-3">
                        <h5>Revenue Over Time</h5>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const ctx1 = document.getElementById('bookingsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Bookings',
                    data: <?php echo json_encode($bookingsChartData); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            }
        });

        const ctx2 = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode($revenueChartData); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>

</html>
