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
$no_of_days = isset($data['no_of_days']) ? $data['no_of_days'] : '';
$reason = isset($data['reason']) ? $data['reason'] : '';

$date = date("Y-m-d", strtotime($datetime));
$time = date("H:i:s", strtotime($datetime));
$hour = date("H", strtotime($datetime));
$minute = date("i", strtotime($datetime));
$curr_month = date("m", strtotime($datetime));

$_remaining_wfh_leaves = 0;
$wfh_taken = 0;

    // $applied_date = new DateTime($datetime);
    // $_fromdate = new DateTime($to_date);

    // // Calculate the difference
    // $interval = $fromdate->diff($todate);
    // $total_days = $interval->d + 1;



if (!empty($from_date) && !empty($to_date)) {
    
    if ($hour > 9 || ($hour == 9 && $minute > 0)) {
        $approval_status = "Pending";
    } else {
        $approval_status = "Approved";
    }
    
    $fromdate = new DateTime($from_date);
    $todate = new DateTime($to_date);

    // Calculate the difference
    $interval = $fromdate->diff($todate);
    $total_days = $interval->d + 1;

    $applywfhcheck = mysqli_query($con, "SELECT COUNT(*) as total FROM apply_work_from_home WHERE userid = '$userid' AND MONTH(applied_date) = '$curr_month' ");

    $applywfhcount = mysqli_fetch_assoc($applywfhcheck)['total'];

    if ($applywfhcount > 3) {
        $response['Code'] = 201;
        $response['msg'] = "You have already reached the month's limit";
    } else {
        
        $checkwfh = mysqli_query($con, "SELECT total_wfh, remaining_wfh,wfh_taken FROM wfh_count_details WHERE userid = '$userid'");
        
        if ($fetch_wfhcount = mysqli_fetch_assoc($checkwfh)) {
            $remaining_wfh_leaves = $fetch_wfhcount['remaining_wfh'];
            $_wfh_taken = $fetch_wfhcount['wfh_taken'];

            if ($remaining_wfh_leaves > 0) {
                $_remaining_wfh_leaves = $remaining_wfh_leaves - 1;
                $wfh_taken = $_wfh_taken + 1;

                $leaveinsertsql = mysqli_query($con, "INSERT INTO apply_work_from_home(userid, applied_date, from_date, to_date, no_of_days, reason, total_days, approval_status, created_at) VALUES ('$userid', '$datetime', '$from_date', '$to_date', '$no_of_days', '$reason', '$total_days', '$approval_status', '$created_at')");

                if ($leaveinsertsql) {
                    
                    $last_insert_id = mysqli_insert_id($con);
                    
                    $updateremaingwfhcount = mysqli_query($con,"update wfh_count_details set remaining_wfh = '$_remaining_wfh_leaves', wfh_taken = '$wfh_taken' where userid = '$userid' ");
                    
                    // $insert_work_from_home_notifications = mysqli_query($con,"insert into work_from_home_notifications(userid,wfhID,from_date,to_date) values ('$userid','$last_insert_id','$from_date','$to_date')  ");
                    
                    $current_date = new DateTime($from_date);
    
                    for ($i = 0; $i < $total_days; $i++) {
                        $formatted_date = $current_date->format('Y-m-d');
                        $insert_work_from_home_notifications = mysqli_query($con, "INSERT INTO work_from_home_notifications(userid, wfhID, from_date,to_date,created_at) VALUES ('$userid', '$last_insert_id', '$formatted_date','$to_date','$created_at')");
                        
                        // Move to the next date
                        $current_date->modify('+1 day');
                    }
                    
                    
                    $response['Code'] = 200;
                    $response['msg'] = "Work From Home Applied Successfully for ". $total_days ." days";
                    $response['days'] = $total_days;
                } else {
                    $response['Code'] = 250;
                    $response['msg'] = "Error Applying Work From Home!";
                }
            } else {
                $response['Code'] = 202;
                $response['msg'] = "No remaining Work From Home available!";
            }
        } else {
            $response['Code'] = 203;
            $response['msg'] = "User Work From Home details not found!";
        }
    }
} else {
    $response['Code'] = 400;
    $response['msg'] = "Some Error Occurred!";
}

echo json_encode($response);
?>
