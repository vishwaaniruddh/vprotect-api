<?php

include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1'); 

$currdate = date("Y-m-d");
$data = $_POST;
$check_wfh_query = "SELECT * FROM punch_in_out WHERE wfh_status = 1 AND date = '$currdate'";
$wfh_result = mysqli_query($con, $check_wfh_query);

if (mysqli_num_rows($wfh_result) > 0) {
    $punchinresult = mysqli_fetch_assoc($wfh_result);
    $punch_in_time = $punchinresult['punch_in'];

    $query = "SELECT * FROM work_from_home_notifications 
              WHERE from_date = '$currdate' 
              AND (1st_notification_status = 0 
              OR 2nd_notification_status = 0 
              OR 3rd_notification_status = 0)";
    
    $result = mysqli_query($con, $query);
    $notifications_sent = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $userid = $row['userid'];
        // $userid = 3; // Manually setting for now
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

        $current_time = date("H:i:s");
        // $current_time = "18:48:32";

        if ($current_time >= $next_notification_time) {
            $getTokenQuery = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE id='$userid'");
            $tokenData = mysqli_fetch_assoc($getTokenQuery);
            $fcmToken = $tokenData['fcm_token'] ?? null;

            if ($fcmToken) {
                $response = sendNotification($fcmToken);

                $response_data = json_decode($response, true);
                if (isset($response_data['name'])) {
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
                error_log("No FCM Token found for user $userid");
            }
        }
    }

    echo json_encode(["status" => "success", "notifications_sent" => $notifications_sent]);
} else {
    echo json_encode(["status" => "error", "message" => "No WFH users found today!"]);
}

function sendNotification($fcmToken) {
    $url = "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send";

    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "Work From Home Reminder Notification",
                "body" => "Please update the work progress in the APP."
            ],
            "data" => [
                "custom" => "data"
            ]
        ]
    ];

    $headers = [
        'Authorization: Bearer ' . getAccessToken(),
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function getAccessToken() {
    $keyFilePath = $_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/firebase/testing-app-e27e1-firebase-adminsdk-fbsvc-6e3da28fce.json';
    
    if (!file_exists($keyFilePath)) {
        die("Firebase Admin SDK JSON file not found!");
    }

    $keyData = json_decode(file_get_contents($keyFilePath), true);

    $jwtHeader = base64_encode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $jwtClaim = base64_encode(json_encode([
        "iss" => $keyData["client_email"],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://oauth2.googleapis.com/token",
        "exp" => time() + 3600,
        "iat" => time()
    ]));

    $privateKey = openssl_pkey_get_private($keyData['private_key']);
    if (!$privateKey) {
        die("Failed to load private key!");
    }

    openssl_sign("$jwtHeader.$jwtClaim", $signature, $privateKey, "SHA256");
    $jwt = "$jwtHeader.$jwtClaim." . base64_encode($signature);

    $response = file_get_contents("https://oauth2.googleapis.com/token", false, stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query([
                "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                "assertion" => $jwt
            ])
        ]
    ]));

    $responseData = json_decode($response, true);
    if (!$responseData || !isset($responseData['access_token'])) {
        die("Failed to get access token: " . print_r($responseData, true));
    }

    return $responseData['access_token'];
}
?>
