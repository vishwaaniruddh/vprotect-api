<?php

include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$onesignal_app_id = "3d731adb-d025-45d8-9fbf-0090df778589";
$onesignal_api_key = "Basic os_v2_app_hvzrvw6qevc5rh57acin654frh7qn2v4a2fujg5il7xkkmnvru5w4qpkyixx3xeam5ejt3hzxciy3xr5ebmijq3fx2kmitgivo5frbq";

$currdate = date("Y-m-d");
$current_time = date("H:i:s");

$check_wfh_query = "SELECT * FROM punch_in_out WHERE wfh_status = 1 AND date = '$currdate'";
$wfh_result = mysqli_query($con, $check_wfh_query);

if (mysqli_num_rows($wfh_result) > 0) {
    $punchinresult = mysqli_fetch_assoc($wfh_result);
    $punch_in_time = $punchinresult['punch_in'];

    $query = "SELECT * FROM work_from_home_notifications WHERE from_date = '$currdate' AND (1st_notification_status = 0 OR 2nd_notification_status = 0 OR 3rd_notification_status = 0)";
            //   echo $query;

    $result = mysqli_query($con, $query);
    $notifications_sent = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $userid = $row['userid'];
        // $userid = 3;
        $wfhID = $row['wfhID'];

        if ($row['1st_notification_status'] == 0) {
            $notification_field = '1st_notification';
            $next_notification_time = date("H:i:s", strtotime($punch_in_time . ' +2 hours'));
        } elseif ($row['2nd_notification_status'] == 0) {
            $notification_field = '2nd_notification';
            $next_notification_time = date("H:i:s", strtotime($row['1st_notification_time'] . ' +2 hours'));
        } elseif ($row['3rd_notification_status'] == 0) {
            $notification_field = '3rd_notification';
            $next_notification_time = date("H:i:s", strtotime($row['2nd_notification_time'] . ' +2 hours'));
        } else {
            continue;
        }


        // $current_time = "12:50:40";
        
        if ($current_time >= $next_notification_time) {
            $getplayerid = mysqli_query($con, "SELECT onesignal_player_id FROM user_login WHERE id='$userid'");
            $player_data = mysqli_fetch_assoc($getplayerid);
            $playerid = $player_data['onesignal_player_id'] ?? null;

            if ($playerid) {
                $response = sendNotification($onesignal_app_id, $onesignal_api_key, $playerid);

                
                $response_data = json_decode($response, true);
                if (isset($response_data['id'])) {
                    
                    $update_query = "UPDATE work_from_home_notifications SET 
                                     {$notification_field}_time = '$current_time', 
                                     {$notification_field}_status = 1, 
                                     updated_at = NOW() 
                                     WHERE wfhID = '$wfhID'";
                    mysqli_query($con, $update_query);
                    $notifications_sent[] = [
                        "userid" => $userid,
                        "notification_type" => $notification_field,
                        "sent_at" => $current_time
                    ];
                }
            } else {
                error_log("No OneSignal Player ID found for user $userid");
            }
        }
    }

    echo json_encode(["status" => "success", "notifications_sent" => $notifications_sent]);
} else {
    echo json_encode(["status" => "error", "message" => "No WFH users found today!"]);
}

function sendNotification($app_id, $api_key, $player_id) {
    $notification_content = [
        "app_id" => $app_id,
        "include_player_ids" => [$player_id],
        "headings" => ["en" => "Work From Home Reminder Notification"],
        "contents" => ["en" => "Please update the work progress in the APP."],
        "data" => ["custom" => "data"],
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $api_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification_content));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('OneSignal API Error: ' . curl_error($ch));
    } else {
        error_log("OneSignal API Response: HTTP $http_code - $response");
    }

    curl_close($ch);
    return $response;
}

?>
