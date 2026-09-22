<?php
include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');


$response = array();

// $userid = isset($data['userid']) ? $data['userid'] : '' ;

$alert_list = mysqli_query($con, "select * from alert_otp_request order by id DESC");
if (mysqli_num_rows($alert_list) > 0) {
    $details = [];
    while ($fetchall = mysqli_fetch_assoc($alert_list)) {
        $id = $fetchall['id'];
        $user_id = $fetchall['user_id'];
        $access_type = $fetchall['type_access'];
        $requested_at = $fetchall['requested_at'];
        $requested_status = $fetchall['requested_status'];
        $updated_at = $fetchall['updated_at'];
        $updated_by = $fetchall['updated_by'];
        $latitude = $fetchall['latitude'];
        $longitude = $fetchall['longitude'];
        $location = $fetchall['location'];
        $remark = $fetchall['remark'];
        $panel_id = $fetchall['panel_id'];
        
        $usernamesql = mysqli_query($con, "select name,contact_no,email_id from user_login where id='$user_id' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];
        $usercontact_no = $fetch_username['contact_no'];
        $user_emailid = $fetch_username['email_id'];
        
        
        if(isset($panel_id)){
            $mapping_sql = mysqli_query($con,"SELECT * FROM `bm_panel_mapping` where panel_id='".$panel_id."' order by id desc");
            $mapping_sql_result = mysqli_fetch_assoc($mapping_sql);
            $branch_manager_id = $mapping_sql_result['branch_manager_id'];
            
            
            $fetch_branch_data = mysqli_query($con,"SELECT * FROM `branch_details` where id='".$branch_manager_id."'");
            $fetch_branch_data_result = mysqli_fetch_assoc($fetch_branch_data);
            $branch_name = $fetch_branch_data_result['branch_name'];
            $branch_code = $fetch_branch_data_result['branch_code'];
            $contact = $fetch_branch_data_result['contact'];
            
            
        }
        
        
        
        $details[] = [
            'alertid' => $id,
            'userid' => $user_id,
            'usercontact_no' => $usercontact_no,
            'username' => $username,
            'user_emailid' => $user_emailid,
            'access_type' => $access_type,
            'requested_at' => $requested_at,
            'requested_status' => $requested_status,
            'updated_at' => $updated_at,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => $location,
            'remark'=>$remark,
            'panel_id'=>$panel_id,
            'branch_name'=>$branch_name,
            'branch_code'=>$branch_code
            
        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'Alert List fetched successfully',
        'data' => $details,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>