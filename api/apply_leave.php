<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '' ;
$from_date = isset($data['from_date']) ? $data['from_date'] : '';
$to_date = isset($data['to_date']) ? $data['to_date'] : '';
$approval_status = "Pending";
$leave_type = isset($data['leave_type']) ? $data['leave_type'] : '';
$reason = isset($data['reason']) ? $data['reason'] : '';


$count_halfday = 0;
$count_leavetaken = 0;

if($from_date !='' && $to_date != ''){
    // $total_days = '';
    
    $fromdate = new DateTime($from_date);
    $todate = new DateTime($to_date);

    // Calculate the difference
    $interval = $fromdate->diff($todate);

    // Format the interval as days
    $total_days = $interval->d + 1;
    
    $leaveinsertsql = mysqli_query($con,"insert into apply_leave(userid,applied_date,from_date,to_date,leave_type,reason,total_days,approval_status,created_at) values ('$userid','$todays_date','$from_date','$to_date','$leave_type','$reason','$total_days','$approval_status','$created_at') ");

    if($leaveinsertsql){
        
        $checkleavecount = mysqli_query($con,"select * from leave_count_details where userid = '$userid' ");
        if(mysqli_num_rows($checkleavecount)>0){
            
            $fecth_count = mysqli_fetch_assoc($checkleavecount);
            $leaves_taken = $fecth_count['leaves_taken'];
            $remaining_leaves = $fecth_count['remaining_leaves'];
            $half_day_count = $fecth_count['half_day_count'];
            
            if($leave_type=='half_day_first' || $leave_type == 'half_day_second'){
                $count_halfday++;
                $count_leavetaken++;
            } 
            
            
            $total_leaves_taken = $leaves_taken + $total_days;
            $total_remaining_leaves = $remaining_leaves - $total_days;
            
            $updateleavecount = mysqli_query($con,"update leave_count_details set leaves_taken = '$total_leaves_taken', remaining_leaves = '$total_remaining_leaves' where userid = '$userid' ");
            
        }
        
        
        $response['Code'] = 200;
        $response['msg'] = "Leave Applied Successfully";
        $response['days'] = $total_days;
        $response['total_remaining_leaves'] = $total_remaining_leaves;
        $response['total_leaves_taken'] = $total_leaves_taken;
    } else{
        $response['Code'] = 250;
        $response['msg'] = "Error Applying Leave!!";
    }
    // $response['days'] = $days;
} else {
    $response['Code'] = 400;
    $response['msg'] = "Some Error Occured!!";
}

echo json_encode($response);

?>