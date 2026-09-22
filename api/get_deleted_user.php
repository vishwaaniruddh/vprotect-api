<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow the following methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow the following headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request, respond and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$export = isset($_GET['export']) && $_GET['export'] == 'true';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';

$where_clause = "WHERE user_login.user_role = 2 AND user_login.is_delete IS NOT NULL";
if (!empty($search)) {
    $where_clause .= " AND (user_login.name LIKE '%$search%' OR user_login.email_id LIKE '%$search%' OR user_set_panel_data.panel_id LIKE '%$search%')";
}

// Count total records
$count_query = "SELECT COUNT(*) as total FROM user_login 
                LEFT JOIN user_set_panel_data ON user_set_panel_data.user_id = user_login.id AND user_set_panel_data.status = 1 
                $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = 0;
if ($count_result) {
    $row = mysqli_fetch_assoc($count_result);
    $total_records = $row['total'];
}
$total_pages = ceil($total_records / $limit);
$offset = ($page - 1) * $limit;

// Main query
$query = "SELECT
    user_login.*,
    user_set_panel_data.panel_id
FROM
    user_login
LEFT JOIN
    user_set_panel_data
ON
    user_set_panel_data.user_id = user_login.id
    AND user_set_panel_data.status = 1
$where_clause
ORDER BY
    user_login.id DESC";

if (!$export) {
    $query .= " LIMIT $limit OFFSET $offset;";
} else {
    $query .= ";";
}

$user_data = mysqli_query($con, $query);

if ($user_data && mysqli_num_rows($user_data) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_data)) {
        $id = $fetchall['id'];
        $name = $fetchall['name'];
        $email_id = $fetchall['email_id'];
        $status = $fetchall['status'];
        $panel_id = $fetchall['panel_id'];
        $is_deleted = $fetchall['is_deleted'];
        $profile_img = $fetchall['profile_img'];
        $faceEncoding = $fetchall['faceEncoding'];
        
        // Check NULL or empty
        $profile_img_status = (!empty($profile_img)) ? "yes" : "no";
        $faceEncoding_status = (!empty($faceEncoding)) ? "yes" : "no";
        
        $quedetail[] = [
            'userid' => $id,
            'name' => $name,
            'email_id' => $email_id,
            'panel_id' => $panel_id,
            'profile_img' => $profile_img_status,
            'faceEncoding' => $faceEncoding_status,
            'is_deleted' => $is_deleted
        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'Deleted User Data fetched successfully',
        'data' => $quedetail,
        'pagination' => [
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'limit' => $limit
        ]
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "No deleted users found",
        'data' => []
    ];
}

echo json_encode($response);
?>
