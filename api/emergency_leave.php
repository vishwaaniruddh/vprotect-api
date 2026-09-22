<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
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
$leave_type = isset($data['leave_type']) ? $data['leave_type'] : '';
$reason = isset($data['reason']) ? $data['reason'] : '';

$date = date("Y-m-d", strtotime($datetime));
$time = date("H:i:s", strtotime($datetime));
$hour = date("H", strtotime($datetime));
$minute = date("i", strtotime($datetime));
$curr_month = date("m", strtotime($datetime));

if (!empty($from_date) && !empty($to_date)) {
    
    if ($hour > 10 || ($hour == 10 && $minute > 0)) {
        $approval_status = "Pending";
    } else {
        $approval_status = "Approved";
    }
    
    $fromdate = new DateTime($from_date);
    $todate = new DateTime($to_date);

    // Calculate the difference
    $interval = $fromdate->diff($todate);
    $total_days = $interval->d + 1;

    $applyleavecheck = mysqli_query($con, "SELECT COUNT(*) as total FROM apply_emergency_leave WHERE userid = '$userid' AND MONTH(applied_date) = '$curr_month' AND leave_type = 'emergency_leave'");

    $applyleavecount = mysqli_fetch_assoc($applyleavecheck)['total'];

    if ($applyleavecount > 2) {
        $response['Code'] = 201;
        $response['msg'] = "You have already reached the month's limit";
    } else {
        
        $checkemergencyleave = mysqli_query($con, "SELECT emergency_leaves, remaining_emergency_leaves FROM leave_count_details WHERE userid = '$userid'");
        
        if ($fetch_emergencycount = mysqli_fetch_assoc($checkemergencyleave)) {
            $remaining_emergency_leaves = $fetch_emergencycount['remaining_emergency_leaves'];

            if ($remaining_emergency_leaves > 0) {
                $_remaining_emergency_leaves = $remaining_emergency_leaves - $total_days;

                $leaveinsertsql = mysqli_query($con, "INSERT INTO apply_emergency_leave(userid, applied_date, from_date, to_date, leave_type, reason, total_days, approval_status, created_at) VALUES ('$userid', '$datetime', '$from_date', '$to_date', '$leave_type', '$reason', '$total_days', '$approval_status', '$created_at')");

                if ($leaveinsertsql) {
                    
                    $updateremaingleavecount = mysqli_query($con,"update leave_count_details set remaining_emergency_leaves = '$_remaining_emergency_leaves' where userid = '$userid' ");
                    
                    
                    $response['Code'] = 200;
                    $response['msg'] = "Emergency Leave Applied Successfully";
                    $response['days'] = $total_days;
                } else {
                    $response['Code'] = 250;
                    $response['msg'] = "Error Applying Leave!";
                }
            } else {
                $response['Code'] = 202;
                $response['msg'] = "No remaining emergency leaves available!";
            }
        } else {
            $response['Code'] = 203;
            $response['msg'] = "User leave details not found!";
        }
    }
} else {
    $response['Code'] = 400;
    $response['msg'] = "Some Error Occurred!";
}

echo json_encode($response);
?>
