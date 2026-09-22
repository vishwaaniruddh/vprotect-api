<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');

// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow the following methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS,REQUEST");

// Allow the following headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request, respond and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
header('Content-Type: application/json');


$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

// $data = $_POST;
$data= $_REQUEST;
$response = array();

 $userid = isset($data['userid']) ? $data['userid'] : '' ;

$user_data = mysqli_query($con, "select * from user_set_panel_data where status = 1 and user_id='".$userid."'");
if (mysqli_num_rows($user_data) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_data)) {
        $id = $fetchall['id'];
        $panel_id = $fetchall['panel_id'];
        
    }
    $response = [
        'Code' => 200,
        'msg' => 'User Panel ID fetched successfully',
        'data' => $panel_id,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "No Panel Set to this User!!",
    ];
}

echo json_encode($response);
?>