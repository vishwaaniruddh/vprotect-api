<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();

$alert_list = mysqli_query($con, "
            SELECT
    a.*,
    p.branch_code,
    b.branch_name AS branch_addr
FROM
    alert_otp_request a
LEFT JOIN panel_list p ON
    a.panel_id = p.panel_id
LEFT JOIN branch_code b ON
    b.branch_code = p.branch_code
WHERE
    DATE(a.requested_at) = CURDATE()
ORDER BY
    a.id
DESC
        ");

if (mysqli_num_rows($alert_list) > 0) {
    $details = [];
    while ($fetchall = mysqli_fetch_assoc($alert_list)) {
        $id = $fetchall['id'];
        $user_id = $fetchall['user_id'];
        $branch_code = $fetchall['branch_code'];
        $branch_addr = $fetchall['branch_addr'];
        $access_type = $fetchall['type_access'];
        $requested_at = $fetchall['requested_at'];
        $requested_status = $fetchall['requested_status'];
        $remark = $fetchall['remark'];
        $updated_at = $fetchall['updated_at'];
        $updated_by = $fetchall['updated_by'];
        $latitude = $fetchall['latitude'];
        $longitude = $fetchall['longitude'];
        $location = $fetchall['location'];
        $panel_id = $fetchall['panel_id'];
        
        $usernamesql = mysqli_query($con, "select name,contact_no,email_id from user_login where id='$user_id' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];
        $usercontact_no = $fetch_username['contact_no'];
        $user_emailid = $fetch_username['email_id'];
        
        $details[] = [
            'alertid' => $id,
            'userid' => $user_id,
            'branch_code' => $branch_code,
            'panel_id' => $panel_id,
            'usercontact_no' => $usercontact_no,
            'username' => $username,
            'user_emailid' => $user_emailid,
            'access_type' => $access_type,
            'requested_at' => $requested_at,
            'requested_status' => $requested_status,
            'remark' => $remark,
            'updated_at' => $updated_at,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => $location,
            'branch_addr'=>$branch_addr
            
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
        'msg' => "No Data Found",
    ];
}

echo json_encode($response);
?>