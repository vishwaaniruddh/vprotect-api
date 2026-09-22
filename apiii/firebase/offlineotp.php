<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
// include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_offline_otp'])) {
    // Default pagination values
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 25;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Count total records
    $countQuery = "SELECT COUNT(*) AS total FROM aes_encrypted_data";
    $countResult = mysqli_query($con, $countQuery);
    $totalRecords = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Main data query with LIMIT and OFFSET
    $selectQuery = "SELECT
                        main_tbl.id,
                        main_tbl.decrypted_otp,
                        main_tbl.decrypted_generation_time,
                        main_tbl.decrypted_expiration_time,
                        user_tbl.name,
                        panel_tbl.panel_id
                    FROM aes_encrypted_data AS main_tbl
                    LEFT JOIN user_login AS user_tbl
                        ON main_tbl.user_id = user_tbl.id
                    LEFT JOIN panel_list AS panel_tbl
                        ON main_tbl.panel_id = panel_tbl.id
                    ORDER BY main_tbl.id DESC
                    LIMIT $limit OFFSET $offset";

    $result = mysqli_query($con, $selectQuery);

    $leads = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $leads[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $leads,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
    exit();
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>