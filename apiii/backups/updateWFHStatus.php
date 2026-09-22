<?php

include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$upd_at = date("Y-m-d H:i:s");
$curr_date = date("Y-m-d");

$data = $_POST;
$response = [
    'Code' => 500,
    'msg' => 'Unexpected Error'
];

// $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    die(json_encode(['Code' => 500, 'msg' => 'Database Connection Failed']));
}

$userid = isset($data['userid']) ? mysqli_real_escape_string($con, $data['userid']) : '';
$latitude = isset($data['latitude']) ? mysqli_real_escape_string($con, $data['latitude']) : '';
$longitude = isset($data['longitude']) ? mysqli_real_escape_string($con, $data['longitude']) : '';
$location = isset($data['location']) ? mysqli_real_escape_string($con, $data['location']) : '';
$update_remark = isset($data['update_remark']) ? mysqli_real_escape_string($con, $data['update_remark']) : '';

$checkwfhnotificationstatussql = mysqli_query($con, "SELECT * FROM work_from_home_notifications WHERE userid = '$userid'");

if (mysqli_num_rows($checkwfhnotificationstatussql) > 0) {
    while ($fetchdetail = mysqli_fetch_assoc($checkwfhnotificationstatussql)) {
        $from_dt = $fetchdetail['from_date'];
        $first_notification_status = $fetchdetail['1st_notification_status'];
        $second_notification_status = $fetchdetail['2nd_notification_status'];
        $third_notification_status = $fetchdetail['3rd_notification_status'];

        if ($first_notification_status == 1 && $second_notification_status == 0 && $third_notification_status == 0) {
            $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                `1st_notification_latitude` = '$latitude', 
                `1st_notification_longitude` = '$longitude', 
                `1st_notification_update` = '$update_remark', 
                `1st_notification_location` = '$location', 
                `1st_status_updated_at` = '$upd_at' 
                WHERE userid='$userid'";
        } else if ($first_notification_status == 1 && $second_notification_status == 1 && $third_notification_status == 0) {
            $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                `2nd_notification_latitude` = '$latitude', 
                `2nd_notification_longitude` = '$longitude', 
                `2nd_notification_update` = '$update_remark', 
                `2nd_notification_location` = '$location', 
                `2nd_status_updated_at` = '$upd_at' 
                WHERE userid='$userid'";
        } else if ($first_notification_status == 1 && $second_notification_status == 1 && $third_notification_status == 1) {
            $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                `3rd_notification_latitude` = '$latitude', 
                `3rd_notification_longitude` = '$longitude', 
                `3rd_notification_update` = '$update_remark', 
                `3rd_notification_location` = '$location', 
                `3rd_status_updated_at` = '$upd_at' 
                WHERE userid='$userid'";
        } else {
            $response = [
                'Code' => 400,
                'msg' => 'No More WFH Updates Required'
            ];
            break;
        }

        $updatesql = mysqli_query($con, $updatesqlQuery);

        if ($updatesql) {
            $response = [
                'Code' => 200,
                'msg' => 'WFH Details Updated Successfully',
                'details' => $updatesqlQuery
            ];
        } else {
            $response = [
                'Code' => 250,
                'msg' => 'Error Updating!',
                'details' => $updatesqlQuery
            ];
        }
    }
} else {
    $response = [
        'Code' => 404,
        'msg' => 'User WFH details not found'
    ];
}

echo json_encode($response);

?>
