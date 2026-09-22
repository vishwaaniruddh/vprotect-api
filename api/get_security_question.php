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

$user_security_question = mysqli_query($con, "select * from user_security_question ");
if (mysqli_num_rows($user_security_question) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_security_question)) {
        $id = $fetchall['id'];
        $question = $fetchall['question'];
        
        $quedetail[] = [
            'userid' => $id,
            'question' => $question,
           
        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'User Security Question fetched successfully',
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