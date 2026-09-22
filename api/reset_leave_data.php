<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// $date = isset($_POST['date']) ? $_POST['date'] : '';
$todays_date = date('Y-m-d');
$date = date('m-d');


$response = array();

if($date == '04-01'){
    
    $resetleavedata = mysqli_query($con,"update leave_count_details set leaves_taken = '0' ");
    
    $resetwfhdata = mysqli_query($con,"update wfh_count_details set wfh_taken='0',remaining_wfh='0' ");
    
    
    $response = [
            'Code' => 200,
            'msg' => "Data Updated!"
        ];
} else {
    $response = [
            'Code' => 400,
            'msg' => "Not Updated!! "
        ];
}

// $resetleavedata = mysqli_query($con,"update leave_count_details set leaves_taken = '0',remaining_leaves = '0',remaining_emergency_leaves = '0' ");

echo json_encode($response);



?>