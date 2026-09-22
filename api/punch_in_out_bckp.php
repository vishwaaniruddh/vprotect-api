<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;



$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';
$datetime = isset($data['datetime']) ? $data['datetime'] : '';
$latitude = isset($data['latitude']) ? $data['latitude'] : '';
$longitude = isset($data['longitude']) ? $data['longitude'] : '';
$type = $data['type'];
$punchin_location = isset($data['punchin_location']) ? $data['punchin_location'] : '';
$punchout_location = isset($data['punchout_location']) ? $data['punchout_location'] : '';
$status = 1;

$date = date("Y-m-d", strtotime($datetime));
$time = date("H:i:s", strtotime($datetime));
$hour = date("H", strtotime($datetime));
$minute = date("i", strtotime($datetime));

// var_dump($data); 

$latecount = 0;
if ($type == "punch_in") {

    //check login late or not
    if ($hour > 10 || ($hour == 10 && $minute > 45)) {
        $remark = "Late Login";
        $latecount = 1;
    } else {
        $remark = "On-Time";
    }

    
    $attendance_status = "";
    $punchedin_status = 1;
    
    // if()
    //     var_dump($hour);
    //         var_dump($minute);
    //          var_dump($remark);
    // var_dump($latecount); 

    $insertsql = mysqli_query($con, "insert into punch_in_out(userid,date,punch_in,punch_in_latitude,punch_in_longitude,login_remark,created_at,punchedin_status,punchin_location,latecount) values ('$userid','$date','$time','$latitude','$longitude','$remark','$created_at','$punchedin_status','$punchin_location','$latecount')  ");

    if ($insertsql) {
        $response['Code'] = 200;
        $response['msg'] = "PunchedIn Successfully!!";
        $response['latecount'] = $latecount;
    } else {
        $response['Code'] = 250;
        $response['msg'] = "Error Logging In";
    }
} elseif ($type == "punch_out") {

    //check for half day
    $punchintimesql = mysqli_query($con, "SELECT punch_in FROM punch_in_out WHERE userid = '$userid' and date = '$date'");
    $fetch_time = mysqli_fetch_assoc($punchintimesql);
    
    if ($fetch_time) {
        $punch_in_time = $fetch_time['punch_in'];
    
        $punchInTime = new DateTime($punch_in_time);
        $punchOutTime = new DateTime($time);
    
        // समय अंतर निकालें
        $interval = $punchInTime->diff($punchOutTime);
    
        // कुल घंटे और मिनट निकालें
        $hours = $interval->h;
        $minutes = $interval->i;
    
        // Decimal hours में निकालें
        $totalHours = $hours .':'. ($minutes);
        $totalMinutes = ($hours * 60) + $minutes;
    
        echo "Total Time Worked: " . $hours . " Hours " . $minutes . " Minutes<br>";
        // echo "Total Hours in Decimal: " . number_format($totalHours, 2) . " Hours<br>";
        echo "Total Minutes Worked: " . $totalMinutes . " Minutes<br>";
    } else {
        echo "Error: Punch In Time not found!";
    }

    
    
    if ($hours < 7) {
        $halfday = "yes";
    } else {
        $halfday = "no";
    }
    
    $total_hrs_diff = "Total Time Worked: " . $hours . " Hours " . $minutes . " Minutes";
    
    $punchedout_status = 1;
    
    $sql = "update punch_in_out set punch_out = '$time', punch_out_latitude = '$latitude', punch_out_longitude = '$longitude', hrs_diff = '$totalHours', half_day = '$halfday', updated_at = '$created_at', punchedout_status = '$punchedout_status', punchout_location='$punchout_location' where date = '$todays_date'  ";
    
    $updatesql = mysqli_query($con, "update punch_in_out set punch_out = '$time', punch_out_latitude = '$latitude', punch_out_longitude = '$longitude', hrs_diff = '$totalHours', half_day = '$halfday', updated_at = '$created_at', punchedout_status = '$punchedout_status', punchout_location='$punchout_location' where date = '$todays_date' and userid = '$userid'  ");

    if ($updatesql) {
        $response['Code'] = 200;
        $response['msg'] = "Successfully PunchedOut";
        $response['diff'] = $total_hrs_diff;
        // $response['sql'] = $sql;
    } else {
        $response['Code'] = 250;
        $response['msg'] = "Error Loggin Out!!";
    }
} else {
    $response['Code'] = 400;
    $response['msg'] = "Some Error Occured!!";
}

echo json_encode($response);
?>