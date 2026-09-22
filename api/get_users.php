<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');

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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// $userid = isset($data['userid']) ? $data['userid'] : '' ;

$where = "WHERE user_role = 2 AND is_delete IS NULL";

if ($search != '') {
    $search = mysqli_real_escape_string($con, $search);

    $where .= " AND (
        name LIKE '%$search%'
        OR user_login.email_id LIKE '%$search%'
        OR user_login.contact_no LIKE '%$search%'
        OR branch_code.branch_name LIKE '%$search%'
    )";
}

$query = "
    SELECT user_login.*,
    user_set_panel_data.panel_id,
    branch_code.branch_name
    FROM user_login
    LEFT JOIN user_set_panel_data ON user_set_panel_data.user_id = user_login.id
    LEFT JOIN branch_code ON user_set_panel_data.panel_id = branch_code.branch_code
    $where
    ORDER BY id DESC
";

$user_data = mysqli_query($con, $query);
if (mysqli_num_rows($user_data) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_data)) {
        $id = $fetchall['id'];
        $name = $fetchall['name'];
        $email_id = $fetchall['email_id'];
        $contact_no = $fetchall['contact_no'];
        $status = $fetchall['status'];
        
        $profile_img = $fetchall['profile_img'];
        $faceEncoding = $fetchall['faceEncoding'];
        $panel_id = $fetchall['panel_id'];
        $branch_name = $fetchall['branch_name'];
        
        // Check NULL or empty
        $profile_img_status = (!empty($profile_img)) ? "yes" : "no";
        $faceEncoding_status = (!empty($faceEncoding)) ? "yes" : "no";
        
        
        $quedetail[] = [
            'userid' => $id,
            'name' => $name,
            'email_id' => $email_id,
            'contact_no' => $contact_no,
            'status' => $status,
            'profile_img' => $profile_img_status,
            'faceEncoding' => $faceEncoding_status,
            'panel_id' => $panel_id,
            'branch_name' => $branch_name
        ];
        
    }
    $response = [
        'Code' => 200,
        'msg' => 'User Data fetched successfully',
        'data' => $quedetail,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>