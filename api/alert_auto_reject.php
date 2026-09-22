<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$response = [];
$data = $_POST;
$remark = isset($data['remark']) ? $data['remark'] : '' ;


// 5 minute se purane pending requests ko auto reject karo
// $autoRejectQuery = mysqli_query($con, "
//     UPDATE alert_otp_request 
//     SET requested_status = 2,
//         remark = '".$remark."' ,
//         updated_at = NOW()
//     WHERE requested_status = 0 
//     AND TIMESTAMPDIFF(MINUTE, requested_at, CONVERT_TZ(NOW(), '+00:00', '+05:30')) > 5
// ");

$autoRejectQuery = mysqli_query($con, "
    UPDATE alert_otp_request 
    SET 
        requested_status = 2,
        remark = '".$remark."' ,
        updated_at = '$created_at'
    WHERE 
        requested_status = 0
    AND requested_at <= DATE_SUB('$created_at', INTERVAL 5 MINUTE)
");
if($autoRejectQuery){
    $affected_rows = mysqli_affected_rows($con);
    $response = [
        'Code' => 200,
        'status' => 'Success',
        'message' => $affected_rows . ' requests auto rejected'
    ];
} else {
    $response = [
        'Code' => 500,
        'status' => 'Error',
        'message' => 'Query Failed',
        'error' => mysqli_error($con)
    ];
}

echo json_encode($response);
?>