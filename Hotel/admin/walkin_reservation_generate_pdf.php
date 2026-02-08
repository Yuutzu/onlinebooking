<?php
require_once './config/config.php'; // Database connection
require '../vendor/autoload.php'; // Load dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// Set Dompdf options
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // Use DejaVu Sans to support ₱ sign
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Allow external font loading

$dompdf = new Dompdf($options);

// Fetch Walk-in Reservations data
$query = "SELECT reservation_id, client_name, room_id, check_in_date, check_out_date, total_price, reservation_type, reservation_status 
          FROM walkin_reservation";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('Error: No data available to generate a PDF.'); window.history.back();</script>";
    exit();
}

// Start building the PDF content
$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
$html .= '<style>
    body { font-family: "DejaVu Sans", sans-serif; }
    h2 { text-align: center; color: #007bff; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background-color: #007bff; color: white; }
    .confirmed { background-color: #d4edda; color: #155724; }
    .pending { background-color: #fff3cd; color: #856404; }
    .cancelled { background-color: #f8d7da; color: #721c24; }
</style>';
$html .= '<h2>Walk-in Reservations Report</h2>';
$html .= '<table>
    <tr>
        <th>Reservation ID</th>
        <th>Client Name</th>
        <th>Room ID</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Total Price (₱)</th>
        <th>Type</th>
        <th>Status</th>
    </tr>';

// Add rows dynamically
while ($row = $res->fetch_assoc()) {
    $statusClass = strtolower($row['reservation_status']);
    $html .= "<tr class='$statusClass'>
        <td>{$row['reservation_id']}</td>
        <td>{$row['client_name']}</td>
        <td>{$row['room_id']}</td>
        <td>{$row['check_in_date']}</td>
        <td>{$row['check_out_date']}</td>
        <td>₱" . number_format($row['total_price'], 2) . "</td>
        <td>{$row['reservation_type']}</td>
        <td>{$row['reservation_status']}</td>
    </tr>";
}

$html .= '</table>';

// Load HTML into dompdf
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output PDF as download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="walkin_reservations_report.pdf"');
echo $dompdf->output();
exit();
?>
