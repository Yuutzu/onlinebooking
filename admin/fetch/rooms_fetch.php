<?php
include('../config/config.php');

// Fetch page and search query
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$query = isset($_GET['query']) ? "%{$_GET['query']}%" : '%%';
$results_per_page = 6; // Number of results per page
$start_limit = ($page - 1) * $results_per_page;

// Count total records with prepared statement
$count_query = "SELECT COUNT(*) AS total FROM rooms
                WHERE room_name LIKE ? 
                OR room_number LIKE ? 
                OR room_description LIKE ? 
                OR room_category LIKE ? 
                OR room_status LIKE ?";
$stmt = $mysqli->prepare($count_query);
$stmt->bind_param("sssss", $query, $query, $query, $query, $query);
$stmt->execute();
$count_result = $stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $results_per_page);

// Fetch paginated records with prepared statement
$data_query = "SELECT * FROM rooms
               WHERE room_name LIKE ? 
               OR room_number LIKE ? 
               OR room_description LIKE ? 
               OR room_category LIKE ? 
               OR room_status LIKE ? 
               LIMIT ?, ?";
$stmt = $mysqli->prepare($data_query);
$stmt->bind_param("ssssssi", $query, $query, $query, $query, $query, $start_limit, $results_per_page);
$stmt->execute();
$data_result = $stmt->get_result();

// Generate table rows
$clients = '';
$cnt = $start_limit + 1;

while ($row = $data_result->fetch_assoc()) {
    $clients .= "
        <tr>
            <td><input type='checkbox' class='deleteCheckbox' style='vertical-align:middle;' name='selected_ids[]' value='{$row['room_id']}'></td>
            <td class='align-middle'>{$cnt}</td>
            <td class='align-middle'>{$row['room_id']}</td>
            <td class='align-middle'>{$row['room_name']}</td>
            <td class='align-middle'>{$row['room_number']}</td>
            <td class='align-middle'>{$row['room_description']}</td>
            <td class='align-middle'>{$row['room_category']}</td>
            <td class='align-middle'>{$row['room_adult']}</td>
            <td class='align-middle'>{$row['room_child']}</td>
            <td class='align-middle'>{$row['room_status']}</td>
            <td class='align-middle'>₱ " . number_format($row['room_price'], 2) . "</td>
            <td class='align-middle'><img src='./dist/img/{$row['room_picture']}' style='width: 180px;'></td>
            <td style='vertical-align: middle;'>
                <a class='btn btn-success me-1 btn-sm someText' style='padding: 5px;' href='room_update.php?room_id={$row['room_id']}'>
                    <i class='bi bi-pencil-square iicon2'></i>
                </a>
                <a class='btn btn-danger btn-sm someText' style='padding: 5px;' href='#'
                    onClick='confirmDelete(\"rooms.php?deleteCategory={$row['room_id']}\")'>
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
    $prev_disabled = ($page <= 1) ? 'disabled' : '';
    $prev_page = max(1, $page - 1);
    $pagination .= "<li class='page-item $prev_disabled'>
                        <a href='#' class='page-link someText' data-page='$prev_page'>Previous</a>
                    </li>";

    // Page Numbers (Limited to 5 pages before and after the current page)
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $active'>
                            <a href='#' class='page-link someText' data-page='$i'>$i</a>
                        </li>";
    }

    // Next Button
    $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
    $next_page = min($total_pages, $page + 1);
    $pagination .= "<li class='page-item $next_disabled'>
                        <a href='#' class='page-link someText' data-page='$next_page'>Next</a>
                    </li>";
}

// Return JSON response
echo json_encode(['clients' => $clients, 'pagination' => $pagination]);
?>
