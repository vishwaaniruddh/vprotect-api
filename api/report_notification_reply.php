<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1');

$data = $_POST;

$response = array();

// Input values
$userid = isset($data['userid']) ? $data['userid'] : '' ;
$notification_id = isset($data['report_id']) ? $data['report_id'] : '';
$_purpose = isset($data['reply_message']) ? trim($data['reply_message']) : '';

if ($notification_id != '' && $_purpose != '' && $userid !='') {
    
    $checkreportid = mysqli_query($con,"select id from report_notification where id = '$notification_id'");
    if(mysqli_num_rows($checkreportid)>0){
       
       $updatesql = mysqli_query($con, "UPDATE report_notification set reply_message= '$_purpose', is_reply = '1', created_by = '$userid', replied_at = '$created_at' where id = '$notification_id' ");
       
        // $sql = "UPDATE report_notification set reply_message= '$_purpose', is_reply = '1', created_by = '$userid', replied_at = '$created_at' where id = '$notification_id' ";
    
        if ($updatesql) {
            
            $response = [
                'Code' => 200,
                'message' => 'Reply Updated for the Notification.',
                // 'sql' => $sql
                
            ];
        } else {
            $response = [
                'Code' => 400,
                'message' => 'Some Error Occured!',
                // 'sql' => $sql
            ];
        }
       
    } else{
         $response = [
            'Code' => 250,
            'message' => 'Report id not found'
            ];
    }
   
} else {
    $response = [
        'Code' => 450,
        'msg' => "Some Data Missing!!"
        ];

}

echo json_encode($response);

?>