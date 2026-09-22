<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';

if($userid){
    $viewleavesql = mysqli_query($con,"select * from apply_emergency_leave where userid = '$userid'");
    // $viewleavesql_result = mysqli_fetch_assoc($viewleavesql);
    if(mysqli_num_rows($viewleavesql)>0){
        
        $usernamesql = mysqli_query($con, "select name,contact_no from user_login where id='$userid' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];

        
        $details = [];
        while($row = mysqli_fetch_assoc($viewleavesql)){
            $details[] = [
                'leave_details' => [
                    'userid' => $userid,
                    'username' => $username,
                    'applied_date' => $row['applied_date'],
                    'from_date' => $row['from_date'],
                    'to_date' => $row['to_date'],
                    'leave_type' => $row['leave_type'],
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
            'msg' => "There are no Emergency Leaves Applied!!",
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