<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

$logindata = mysqli_query($con,"select * from punch_in_out order by id desc ");
if(mysqli_num_rows($logindata)>0){
    $total_records = mysqli_num_rows($logindata);
    $userdetail = [];
    while ($logindatalist = mysqli_fetch_assoc($logindata)){
        $userid = $logindatalist['userid'];
        $date = $logindatalist['date'];
        
        $punch_in_time = $logindatalist['punch_in'];
        $punch_in_lat = $logindatalist['punch_in_latitude'];
        $punch_in_long = $logindatalist['punch_in_longitude'];
        $punch_in_location = $logindatalist['punchin_location'];
        
        $punch_out_time = $logindatalist['punch_out'];
        $punch_out_lat = $logindatalist['punch_out_latitude'];
        $punch_out_long = $logindatalist['punch_out_longitude'];
        $punch_out_location = $logindatalist['punchout_location'];
        
        $hrs_diff = $logindatalist['hrs_diff'];
        $login_remark = $logindatalist['login_remark'];
        $half_day = $logindatalist['half_day'];
        
        $usernamesql = mysqli_query($con, "select name,contact_no from user_login where id='$userid' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];
        
        $userdetail[] = [
            'userid' => $userid,
            'name' => $username,
            'date' => $date,
            'punch_in_time' => $punch_in_time,
            'punch_in_latitude' => $punch_in_lat,
            'punch_in_longitude' => $punch_in_long,
            'punch_in_location' => $punch_in_location,
            'punch_out_time' => $punch_out_time,
            'punch_out_latitude' => $punch_out_lat,
            'punch_out_longitude' => $punch_out_long,
            'punch_out_location' => $punch_out_location,
            'hrs_diff' => $hrs_diff,
            'login_remark' => $login_remark,
            'half_day' => $half_day

        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'PunchIn & PunchOut Data Fetched Successfully',
        'total_records' => $total_records,
        'data' => $userdetail,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}
echo json_encode($response);
?>