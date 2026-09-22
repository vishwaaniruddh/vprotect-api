<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');


$response = array();

$data = $_POST;

$alert_id = isset($data['alert_id']) ? $data['alert_id'] : '' ;

$_iscorrect = 0;

$user_access_request = mysqli_query($con, "select * from alert_otp_request where id='".$alert_id."'");
if (mysqli_num_rows($user_access_request) > 0) {
    // $quedetail = [];
    // while ($fetchall = mysqli_fetch_assoc($user_access_request)) {
    //     $requested_status = $fetchall['requested_status'];
    //   // $question = $fetchall['question'];
        
    //     if($requested_status==1){
    //         $_iscorrect = 1;
    //     }
    // }
    $fetch = mysqli_fetch_assoc($user_access_request);
    $requested_status = $fetch['requested_status'];   // only one row needed
    $remark = $fetch['remark'];
    $response = [
        'Code' => 200,
        'msg' => 'User Request Access Status Details',
        // 'data' => $_iscorrect,
        'data' => $requested_status,
        'remark' => $remark
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>