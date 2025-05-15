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

// Fetch Request data
$query = "SELECT request_id, reservation_id, products, services, total_price, status FROM requests ORDER BY request_id DESC";
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
$html .= '<h2>Request Report</h2>';
$html .= '<table>
    <tr>
        <th>Request ID</th>
        <th>Reservation ID</th>
        <th>Products</th>
        <th>Services</th>
        <th>Total Price</th>
        <th>Status</th>
    </tr>';

// Add rows dynamically
while ($row = $res->fetch_assoc()) {
    // Decode the JSON strings for products and services
    $products = json_decode($row['products'], true);
    $services = json_decode($row['services'], true);

    // Format the products and services into a readable string
    $productList = is_array($products) ? implode(", ", $products) : '';
    $serviceList = is_array($services) ? implode(", ", $services) : '';

    // Apply color styling for status
    $statusClass = strtolower($row['status']);
    
    $html .= "<tr class='$statusClass'>
        <td>{$row['request_id']}</td>
        <td>{$row['reservation_id']}</td>
        <td>{$productList}</td>
        <td>{$serviceList}</td>
        <td>₱" . number_format($row['total_price'], 2) . "</td>
        <td>{$row['status']}</td>
    </tr>";
}

$html .= '</table>';

// Load HTML into dompdf
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output PDF as download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="request_report.pdf"');
echo $dompdf->output();
exit();
?>
