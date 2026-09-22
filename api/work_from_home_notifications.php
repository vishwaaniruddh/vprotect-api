<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);


$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';
$datetime = isset($data['datetime']) ? $data['datetime'] : '';
$from_date = isset($data['from_date']) ? $data['from_date'] : '';
$to_date = isset($data['to_date']) ? $data['to_date'] : '';

$first_notification_status = isset($data['first_notification']) ?  $data['first_notification'] : '' ;
$first_notification_time = isset($data['1st_notification_time']) ? $data['1st_notification_time'] : '';
$first_notification_latitude = isset($data['1st_notification_latitude']) ? $data['1st_notification_latitude'] : '';
$first_notification_longitude = isset($data['1st_notification_longitude']) ? $data['1st_notification_longitude'] : '';
$first_notification_update = isset($data['1st_notification_update']) ? $data['1st_notification_update'] : '';
$first_notification_location = isset($data['1st_notification_location']) ? $data['1st_notification_location'] : '';


$second_notification_status = isset($data['second_notification']) ?  $data['second_notification'] : '' ;
$second_notification_time = isset($data['2nd_notification_time']) ? $data['2nd_notification_time'] : '';
$second_notification_latitude = isset($data['2nd_notification_latitude']) ? $data['2nd_notification_latitude'] : '';
$second_notification_longitude = isset($data['2nd_notification_longitude']) ? $data['2nd_notification_longitude'] : '';
$second_notification_update = isset($data['2nd_notification_update']) ? $data['2nd_notification_update'] : '';
$second_notification_location = isset($data['2nd_notification_location']) ? $data['2nd_notification_location'] : '';


$third_notification_status = isset($data['third_notification']) ?  $data['third_notification'] : '' ;
$third_notification_time = isset($data['3rd_notification_time']) ? $data['3rd_notification_time'] : '';
$third_notification_latitude = isset($data['3rd_notification_latitude']) ? $data['3rd_notification_latitude'] : '';
$third_notification_longitude = isset($data['3rd_notification_longitude']) ? $data['3rd_notification_longitude'] : '';
$third_notification_update = isset($data['3rd_notification_update']) ? $data['3rd_notification_update'] : '';
$third_notification_location = isset($data['3rd_notification_location']) ? $data['3rd_notification_location'] : '';


$date = date("Y-m-d", strtotime($datetime));
$time = date("H:i:s", strtotime($datetime));
$hour = date("H", strtotime($datetime));
$minute = date("i", strtotime($datetime));
$curr_month = date("m", strtotime($datetime));

$checkapplywfh = mysqli_query($con,"select * from apply_work_from_home where userid = '$userid' and from_date = '$from_date' ");

if(mysqli_num_rows($checkapplywfh)>0){
 $fetch_details = mysqli_fetch_assoc($checkapplywfh);
 $wfhID = $fetch_details['id'];
 
 $checkloginstatus = mysqli_query($con,"select date,userid,punch_in,punch_out,punchedin_status,punchedout_status from punch_in_out where uaerid='$userid' and date='$from_date'  ");
 if(mysqli_num_rows($checkloginstatus)>0){
     $fetchlogindetails = mysqli_fetch_assoc($checkloginstatus);
     
     $login_date = $fetchlogindetails['date'];
     $login_userid = $fetchlogindetails['userid'];
     $login_punch_in_time = $fetchlogindetails['punch_in'];
     $login_punch_out_time = $fetchlogindetails['punch_out'];
     $login_punchedin_status = $fetchlogindetails['punchedin_status'];
     $login_punchedout_status = $fetchlogindetails['punchedout_status'];
 }
    
    $checknotificationdata = mysqli_query($con,"select * from work_from_home_notifications where userid = '$userid' and from_date='$from_date' and wfhID = '$wfhID' ");
    if(mysqli_num_rows($checknotificationdata)>0){
        
        // $
        
        $updatesql = "";
    } else {
        $insertsql = "INSERT INTO `work_from_home_notifications`(`userid`, `from_date`, `to_date`, `1st_notification_time`, `1st_notification_latitude`, `1st_notification_longitude`, `1st_notification_update`, `1st_notification_location`, `1st_notification_status`, `created_at`,`wfhID` ) values ('$userid','$from_date','$to_date','$first_notification_time','$first_notification_latitude','$first_notification_longitude','$first_notification_update','$first_notification_location','$first_notification_status','$created_at','$wfhID' )";
        
        $insertQry = mysqli_query($con,$insertsql);
        if($insertQry){
            $response['Code'] = 200;
            $response['msg'] = "Data Inserted Successfully";
            $response['sql'] = $insertsql;
            $response['login+_data'] = $fetchlogindetails;
        }
    }
    

    
}




echo json_encode($response);
?>
