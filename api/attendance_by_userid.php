<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

function getMonthValue($monthName) {
    // Array mapping month names to their numeric values
    $months = [
        'January' => 1,
        'February' => 2,
        'March' => 3,
        'April' => 4,
        'May' => 5,
        'June' => 6,
        'July' => 7,
        'August' => 8,
        'September' => 9,
        'October' => 10,
        'November' => 11,
        'December' => 12
    ];

    // Normalize input to ensure case-insensitive matching
    $monthName = ucfirst(strtolower(trim($monthName)));

    // Return the corresponding numeric value or null if the month is invalid
    return $months[$monthName] ?? null;
}


$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';
$curr_month = isset($data['monthName']) ? $data['monthName'] : '';
$date = isset($data['date']) ? $data['date'] : '' ;

$month = getMonthValue($curr_month);


$attsql = "select * from punch_in_out where userid = '$userid'";
if($month){
    $attsql .= "and month(date) = '$month'";
} 

if($date){
    $attsql .= "and date = '$date'";
}
$attendancesql = mysqli_query($con,$attsql);


if(mysqli_num_rows($attendancesql)>0){
    $total_records = mysqli_num_rows($attendancesql);
    $userdetail = [];
    while($row = mysqli_fetch_assoc($attendancesql)){
        $userid = $row['userid'];
        $date = $row['date'];
        
        $punch_in_time = $row['punch_in'];
        // $punch_in_lat = $row['punch_in_latitude'];
        // $punch_in_long = $row['punch_in_longitude'];
        $punch_in_location = $row['punchin_location'];
        
        $punch_out_time = $row['punch_out'];
        // $punch_out_lat = $row['punch_out_latitude'];
        // $punch_out_long = $row['punch_out_longitude'];
        $punch_out_location = $row['punchout_location'];
        
        $hrs_diff = $row['hrs_diff'];
        $login_remark = $row['login_remark'];
        $half_day = $row['half_day'];
        $punchedin_status = $row['punchedin_status'];
        $punchedout_status = $row['punchedout_status'];
        $attendance_status = $row['attendance_status'];
        $wfh_status = $row['wfh_status'];
        
        $usernamesql = mysqli_query($con, "select name,contact_no from user_login where id='$userid' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];
        
        $userdetail[] = [
            'userid' => $userid,
            'name' => $username,
            'date' => $date,
            'punch_in_time' => $punch_in_time,
            'punch_in_location' => $punch_in_location,
            'punch_out_time' => $punch_out_time,
            'punch_out_location' => $punch_out_location,
            'hrs_diff' => $hrs_diff,
            'half_day' => $half_day,
            'punchedin_status' => $punchedin_status,
            'punchedout_status' => $punchedout_status,
            'login_remark' => $login_remark,
            'attendance_status' => $attendance_status,
            'wfh_status' => $wfh_status

        ];
    }$response = [
        'Code' => 200,
        'msg' => 'Attendance Data Fetched Successfully',
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