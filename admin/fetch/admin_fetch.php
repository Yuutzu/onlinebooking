<?php
include('../config/config.php');

// Fetch page and search query
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$query = isset($_GET['query']) ? $mysqli->real_escape_string($_GET['query']) : '';
$results_per_page = 6; // Number of results per page
$start_limit = ($page - 1) * $results_per_page;

// Count total records
$count_query = "SELECT COUNT(*) AS total FROM clients
                WHERE (client_name LIKE '%$query%' 
                OR client_presented_id LIKE '%$query%' 
                OR client_phone LIKE '%$query%'
                OR client_email LIKE '%$query%'
                OR client_status LIKE '%$query%')
                AND role = 'Admin'";
$count_result = $mysqli->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $results_per_page);

// Fetch paginated records
$data_query = "SELECT id, client_id, client_name, client_presented_id, client_phone, client_email, client_status, client_picture 
               FROM clients
               WHERE (client_name LIKE '%$query%' 
               OR client_presented_id LIKE '%$query%'
               OR client_phone LIKE '%$query%'
               OR client_email LIKE '%$query%'
               OR client_status LIKE '%$query%')
               AND role = 'Admin'
               LIMIT $start_limit, $results_per_page";
$data_result = $mysqli->query($data_query);

// Generate table rows
$clients = '';
$cnt = $start_limit + 1;

while ($row = $data_result->fetch_assoc()) {
    $clients .= "
        <tr>
            <td class='align-middle'>{$cnt}</td>
            <td class='align-middle'>{$row['client_id']}</td>
            <td class='align-middle'>{$row['client_name']}</td>
            <td class='align-middle'>{$row['client_presented_id']}</td>

            <!-- New Column: Client ID Picture -->
            <td class='align-middle'>
                <a href='./dist/img/{$row['client_id_picture']}' target='_blank' style='text-decoration: none; color: blue;'>
                    View ID
                </a>
            </td>

            <td class='align-middle'>{$row['client_phone']}</td>
            <td class='align-middle'>{$row['client_email']}</td>
            <td class='align-middle'>{$row['client_status']}</td>
            <td class='align-middle'><img src='./dist/img/{$row['client_picture']}' style='width: 100px; height: auto;'></td>
            <td style='vertical-align: middle;'>
                <a class='btn btn-success me-1 btn-sm someText' style='padding: 5px;' href='clients.php?id={$row['id']}'>
                    <i class='bi bi-pencil-square iicon2'></i>
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
