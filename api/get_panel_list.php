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

// $userid = isset($data['userid']) ? $data['userid'] : '' ;

$user_data = mysqli_query($con, "select * from panel_list where status=1 order by id DESC");
if (mysqli_num_rows($user_data) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_data)) {
        $id = $fetchall['id'];
        $panel_id = $fetchall['panel_id'];
        $no_of_user_allotment = $fetchall['no_of_user_allotment'];
        $status = $fetchall['status'];
        
        $quedetail[] = [
            'id' => $id,
            'panel_id' => $panel_id,
            'no_of_user_allotment'=> $no_of_user_allotment,
            'status' => $status
            
        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'Panel Data fetched successfully',
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