<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = [];

$title = isset($data['title']) ? $data['title'] : '' ;
$description = isset($data['description']) ? $data['description'] : '' ;
$date = isset($data['date']) ? $data['date'] : '' ;
$date = date("Y-m-d", strtotime($date));
$type = isset($data['type']) ? $data['type'] : '' ;

if($date !='' && $title !=''){
    
    $insertholiday = mysqli_query($con,"insert into holidays (title,description,date,type,created_at) values ('$title','$description','$date','$type','$created_at') ");
    
    if($insertholiday){
        $response=[
            'Code' => 200,
            'msg' => "Holiday Inserted Successfully"
            ];
    } else{
        $response=[
            'Code' => 250,
            'msg' => "Error Inserting Holiday!!"
            ];
    }
} else {
    $response = [
        'Code' => 400,
        'msg' => "Date and Title Missing!!"
        ];
}


echo json_encode($response);
?>