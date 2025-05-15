<?php
require_once './config/config.php'; // Database connection
require '../vendor/autoload.php'; // Load dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// Set Dompdf options
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // Support ₱ sign
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Allow external font loading

$dompdf = new Dompdf($options);

// Fetch Payment Data from both tables
$query = "SELECT reservation_id, client_id, payment_method, gcash_name, gcash_number, gcash_ref, gcash_screenshot, total_price, type, reservation_status, 'Online' AS source FROM reservations 
          UNION ALL 
          SELECT reservation_id, NULL AS client_id, payment_method, client_gcash_name, client_gcash_number, client_gcash_ref, client_gcash_ref_image, total_price, reservation_type, reservation_status, 'Walk-in' AS source FROM walkin_reservation 
          ORDER BY reservation_id DESC";

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
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; }
    th { background-color: #007bff; color: white; }
    .pending { background-color: #fff3cd; color: #856404; }
    .confirmed { background-color: #d4edda; color: #155724; }
    .cancelled { background-color: #f8d7da; color: #721c24; }
</style>';
$html .= '<h2>Payments Report</h2>';
$html .= '<table>
    <tr>
        <th>ID</th>
        <th>Reservation ID</th>
        <th>Payment Method</th>
        <th>Gcash Name</th>
        <th>Gcash Number</th>
        <th>Gcash Ref</th>
        <th>Total Price</th>
        <th>Type</th>
        <th>Status</th>
        <th>Source</th>
    </tr>';

// Add rows dynamically
while ($row = $res->fetch_assoc()) {
    // Apply color styling for status
    $statusClass = strtolower($row['reservation_status']);
    
    $html .= "<tr class='$statusClass'>
        <td>{$row['reservation_id']}</td>
        <td>{$row['reservation_id']}</td>
        <td>{$row['payment_method']}</td>
        <td>{$row['gcash_name']}</td>
        <td>{$row['gcash_number']}</td>
        <td>{$row['gcash_ref']}</td>
        <td>₱" . number_format($row['total_price'], 2) . "</td>
        <td>{$row['type']}</td>
        <td>{$row['reservation_status']}</td>
        <td>{$row['source']}</td>
    </tr>";
}

$html .= '</table>';

// Load HTML into dompdf
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output PDF as download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="payments_report.pdf"');
echo $dompdf->output();
exit();
?>
