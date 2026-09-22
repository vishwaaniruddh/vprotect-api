<?php

include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$upd_at = date("Y-m-d H:i:s");
$curr_date = date("Y-m-d");

$data = $_POST;
$response = [];

if (!$con) {
    die(json_encode(['Code' => 500, 'msg' => 'Database Connection Failed']));
}

$userid = isset($data['userid']) ? mysqli_real_escape_string($con, $data['userid']) : '';
$latitude = isset($data['latitude']) ? mysqli_real_escape_string($con, $data['latitude']) : '';
$longitude = isset($data['longitude']) ? mysqli_real_escape_string($con, $data['longitude']) : '';
$location = isset($data['location']) ? mysqli_real_escape_string($con, $data['location']) : '';
$update_remark = isset($data['update_remark']) ? mysqli_real_escape_string($con, $data['update_remark']) : '';

$punch_sql = mysqli_query($con, "SELECT punch_in_latitude, punch_in_longitude FROM punch_in_out WHERE userid = '$userid' AND date = '$curr_date'");

if (mysqli_num_rows($punch_sql) > 0) {
    $punch_data = mysqli_fetch_assoc($punch_sql);
    $punch_lat = $punch_data['punch_in_latitude'];
    $punch_lon = $punch_data['punch_in_longitude'];

    function getDistance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    $distance = getDistance($punch_lat, $punch_lon, $latitude, $longitude);

    if ($distance <= 20) {
        $checkwfhnotificationstatussql = mysqli_query($con, "SELECT * FROM work_from_home_notifications WHERE userid = '$userid' and from_date = '$curr_date' ");

        if (mysqli_num_rows($checkwfhnotificationstatussql) > 0) {
            $fetchdetail = mysqli_fetch_assoc($checkwfhnotificationstatussql);
            $first_notification_status = $fetchdetail['1st_notification_status'];
            $second_notification_status = $fetchdetail['2nd_notification_status'];
            $third_notification_status = $fetchdetail['3rd_notification_status'];

            // $updatesqlQuery = "";

            if ($first_notification_status == 1 && $second_notification_status == 0 && $third_notification_status == 0) {
                $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                    `1st_notification_latitude` = '$latitude', 
                    `1st_notification_longitude` = '$longitude', 
                    `1st_notification_update` = '$update_remark', 
                    `1st_notification_location` = '$location', 
                    `1st_status_updated_at` = '$upd_at' 
                    WHERE userid='$userid' and `1st_notification_status` = 1 and from_date = '$curr_date' ";
            } else if ($first_notification_status == 1 && $second_notification_status == 1 && $third_notification_status == 0) {
                $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                    `2nd_notification_latitude` = '$latitude', 
                    `2nd_notification_longitude` = '$longitude', 
                    `2nd_notification_update` = '$update_remark', 
                    `2nd_notification_location` = '$location', 
                    `2nd_status_updated_at` = '$upd_at' 
                    WHERE `userid`='$userid' AND `2nd_notification_status` = 1 and from_date = '$curr_date' ";
            } else if ($first_notification_status == 1 && $second_notification_status == 1 && $third_notification_status == 1) {
                $updatesqlQuery = "UPDATE work_from_home_notifications SET 
                    `3rd_notification_latitude` = '$latitude', 
                    `3rd_notification_longitude` = '$longitude', 
                    `3rd_notification_update` = '$update_remark', 
                    `3rd_notification_location` = '$location', 
                    `3rd_status_updated_at` = '$upd_at' 
                    WHERE `userid`='$userid' AND `3rd_notification_status` = 1 and from_date = '$curr_date' ";
            } else {
                echo json_encode([
                    'Code' => 400,
                    'msg' => 'No More WFH Updates Required'
                ]);
                exit;
            }

            $updatesql = mysqli_query($con, $updatesqlQuery);

            if ($updatesql) {
                $response = [
                    'Code' => 200,
                    'msg' => 'WFH Details Updated Successfully',
                    'sql' => $updatesqlQuery
                ];
            } else {
                $response = [
                    'Code' => 250,
                    'msg' => 'Error Updating!'
                ];
            }
        } else {
            $response = [
                'Code' => 404,
                'msg' => 'User WFH details not found'
            ];
        }
    } else {
        $response = [
            'Code' => 403,
            'msg' => 'Location Mismatch! You are not within 20 meters of your punch-in location.'
        ];
    }
} else {
    $response = [
        'Code' => 404,
        'msg' => 'Punch-in details not found for today'
    ];
}

echo json_encode($response);

?>
