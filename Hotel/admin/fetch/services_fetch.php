<?php
include('../config/config.php');

// Fetch page and search query
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$query = isset($_GET['query']) ? $mysqli->real_escape_string($_GET['query']) : '';
$results_per_page = 2; // Number of results per page
$start_limit = ($page - 1) * $results_per_page;

// Count total records
$count_query = "SELECT COUNT(*) AS total FROM room_services
                WHERE service_id LIKE '%$query%' 
                OR service_name LIKE '%$query%' 
                OR service_description LIKE '%$query%'
                OR service_price LIKE '%$query%'
                OR service_picture LIKE '%$query%'
                OR service_status LIKE '%$query%'";
$count_result = $mysqli->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $results_per_page);

// Fetch paginated records
$data_query = "SELECT * FROM room_services 
            WHERE service_id LIKE '%$query%' 
               OR service_name LIKE '%$query%'
               OR service_description LIKE '%$query%'
               OR service_price LIKE '%$query%'
               OR service_picture LIKE '%$query%'
               OR service_status LIKE '%$query%'
               LIMIT $start_limit, $results_per_page";
$data_result = $mysqli->query($data_query);

// Generate table rows
$clients = '';
$cnt = $start_limit + 1;

while ($row = $data_result->fetch_assoc()) {
    $clients .= "
        <tr>
            <td><input type='checkbox' class='deleteCheckbox' style='vertical-align:middle;' name='selected_ids[]' value='{$row['service_id']}'></td>
            <td class='align-middle'>{$cnt}</td>
            <td class='align-middle'>{$row['service_id']}</td>
            <td class='align-middle'>{$row['service_name']}</td>
            <td class='align-middle'>{$row['service_description']}</td>
            <td class='align-middle'>{$row['service_price']}</td>
            <td class='align-middle'>{$row['service_status']}</td>
            <td class='align-middle'><img src='./dist/img/{$row['service_picture']}' style='width: 180px;'></td>
            <td style='vertical-align: middle;'>
                <a class='btn btn-success me-1 btn-sm someText' style='padding: 5px;' href='services_update.php?service_id={$row['service_id']}'>
                    <i class='bi bi-pencil-square iicon2'></i>
                </a>
                <a class='btn btn-danger btn-sm someText' style='padding: 5px;' href='#' onClick='confirmDelete(\"services.php?deleteCategory={$row['service_id']}\")'>
                    <i class='bi bi-trash iicon2'></i>
                </a>
            </td>
        </tr>";
    $cnt++;
}

// Generate pagination links
$pagination = '';
if ($total_pages > 1) {
    // Previous Button
    $prev_disabled = $page <= 1 ? 'disabled' : '';
    $prev_page = $page > 1 ? $page - 1 : 1;
    $pagination .= "<li class='page-item $prev_disabled'>
                        <a href='#' class='page-link someText' data-page='$prev_page'>Previous</a>
                    </li>";

    // Page Numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $page ? 'active' : '';
        $pagination .= "<li class='page-item $active'>
                            <a href='#' class='page-link someText' data-page='$i'>$i</a>
                        </li>";
    }

    // Next Button
    $next_disabled = $page >= $total_pages ? 'disabled' : '';
    $next_page = $page < $total_pages ? $page + 1 : $total_pages;
    $pagination .= "<li class='page-item $next_disabled'>
                        <a href='#' class='page-link someText' data-page='$next_page'>Next</a>
                    </li>";
}

// Return JSON response
echo json_encode(['clients' => $clients, 'pagination' => $pagination]);
?>
