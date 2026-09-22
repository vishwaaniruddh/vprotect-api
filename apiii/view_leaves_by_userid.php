<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';

if($userid){
    $viewleavesql = mysqli_query($con,"select * from apply_leave where userid = '$userid' order by id desc");
    // $viewleavesql_result = mysqli_fetch_assoc($viewleavesql);
    if(mysqli_num_rows($viewleavesql)>0){
        
        $usernamesql = mysqli_query($con, "select name,contact_no from user_login where id='$userid' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];

        
        $details = [];
        while($row = mysqli_fetch_assoc($viewleavesql)){
            if($row['leave_type']=='Half_Day_first'){
                $leave_type = "Half Day (First Half)";
            } else if($row['leave_type']=='Half_Day_second'){
                $leave_type = "Half Day (Second Half)";
            } else if($row['leave_type']=='Full_Day'){
                $leave_type = "Full Day";
            }else {
                $leave_type = '';
            }
            
            $_fromdate = date("d-M-Y",strtotime($row['from_date']));
            $_todate = date("d-M-Y",strtotime($row['to_date']));
            $_applieddate = date("d-M-Y",strtotime($row['applied_date']));
            
            
            $details[] = [
                'leave_details' => [
                    'userid' => $userid,
                    'username' => $username,
                    'applied_date' => $_applieddate,
                    'from_date' => $_fromdate,
                    'to_date' => $_todate,
                    'leave_type' => $leave_type,
                    'reason' => $row['reason'],
                    'total_days' => $row['total_days'],
                    'approval_status' => $row['approval_status']
                    ]
                ];
        }
        
        $response = [
            'Code' => 200,
            'msg' => 'Leave Details fetched successfully',
            'data' => $details,
            
        ];
        
    }else{
        $response = [
            'Code' => 250,
            'msg' => "There are no Leaves Details.",
            ];
    }
    
} else {
    $response = [
            'Code' => 400,
            'msg' => "Unable to Fetch User!!",
            ];
}

echo json_encode($response);
?>