<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// $created_at = date('Y-m-d H:i:s');
// $todays_date = date('Y-m-d');

$data = $_POST;
$_userid = $data['userid'];
$fetchdate = $data['date'];
$punchin_time = $data['punchin_time'];
$punchout_time = $data['punchout_time'];
// $punchin_date = $data['punchin_date'];
// $punchout_date = $data['punchout_date'];



$response = array();

$sql = "select * from punch_in_out where userid='$_userid' and date='$fetchdate' ";


if($punchin_time!='' ){
    $sql.= "and punch_in = '$punchin_time' ";
}

if($punchout_time!=''){
    $sql.= " and punch_out = '$punchout_time' ";
}

$sql .= "order by id desc limit 1";

// $logindata = mysqli_query($con,"select * from punch_in_out where userid='$_userid' and date='$fetchdate' and punch_in = '$punchin_time' and punch_out = '$punchout_time'  order by id desc limit 1 ");

$logindata = mysqli_query($con,$sql);


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
        $punchedin_status = $logindatalist['punchedin_status'];
        $punchedout_status = $logindatalist['punchedout_status'];
        $punchin_location = $logindatalist['punchin_location'];
        $punchout_location = $logindatalist['punchout_location'];
        
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
            'half_day' => $half_day,
            'punchedin_status' => $punchedin_status,
            'punchedout_status' => $punchedout_status,
            'punchin_location' => $punchin_location,
            'punchout_location' => $punchout_location
            

        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'PunchIn & PunchOut Data Fetched Successfully',
        'total_records' => $total_records,
        'data' => $userdetail,
        'sql' => $sql
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}
echo json_encode($response);
?>