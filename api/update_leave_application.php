<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response =[];

$userid = isset($data['userid']) ? $data['userid'] : '' ;
$date = isset($data['date']) ? $data['date'] : '' ;
$status = isset($data['status']) ? $data['status'] : '' ;
$applied_date = isset($data['applied_date']) ? $data['applied_date'] : '' ;

if($userid){
    $updateleave = mysqli_query($con,"update appply_leave set status = '$status' where userid = '$userid' and applied_date='$applied_date' ");
    if($updateleave){
        $response = [
            'Code' => 200,
            'msg' => " Status Updated Successfully"
            ];
    } else {
        $response = [
            'Code' => 250,
            'msg' => "Status Update Error!!"
            ];
    }
} else {
    $response = [
            'Code' => 400,
            'msg' => "Userid not found!!"
            ];
}

echo json_encode($response);
?>