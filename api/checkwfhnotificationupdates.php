<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
$todays_date = date('Y-m-d');

$response = array();

$checkstatus = mysqli_query($con,"select * from work_from_home_notifications where userid  = '$userid' and from_date = '$todays_date' ");
$fetch_result = mysqli_fetch_assoc($checkstatus);
if(mysqli_num_rows($checkstatus)>0){
    $first_notification_status = $fetch_result['1st_notification_status'];
    $first_update_status = $fetch_result['1st_status_updated_at']??'';
    
    $second_notification_status = $fetch_result['2nd_notification_status'];
    $second_update_status = $fetch_result['2nd_status_updated_at']??'';
    
    $third_notification_status = $fetch_result['3rd_notification_status'];
    $third_update_status = $fetch_result['3rd_status_updated_at']??'';
    
    
    
    if($first_notification_status == 1 && $second_notification_status == 1 && $third_notification_status == 1){
        if(!$first_update_status=='' && !$second_update_status=='' && !$third_update_status==''){
            $response = [
                'Code' => 200,
                'msg' => "All WFH Updates are completed. No More Update Requires."
                ];
        } else {
            $response = [
                'Code' => 250,
                'msg' => "More updates are Incoming!!"
                ];
        }
    } else {
        $response = [
                'Code' => 400,
                'msg' => "Notifications are Remaining!!"
                ];
    }
} else {
    $response = [
            'Code' => 450,
            'msg' => "No Details for WFH for Date: ".$todays_date
        ];
}



echo json_encode($response);






?>