<?php

include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1');

$currdate = date("Y-m-d");
$today_datetime = date("Y-m-d H:i:s");

$notifications_sent = [];

// Function to send notification
function sendNotification($fcmToken, $title, $body) {
    $url = "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send";

    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => $title,
                "body" => $body
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

// Function to fetch access token
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

// **1. APPROVED Users के लिए नोटिफिकेशन भेजना**
$query_approved = "SELECT * FROM apply_emergency_leave 
                   WHERE approval_status = 'approved' 
                   AND approval_notification_status = 0";

$result_approved = mysqli_query($con, $query_approved);

while ($row = mysqli_fetch_assoc($result_approved)) {
    $userid = $row['userid'];
    $leaveID = $row['id'];
    $from_date = $row['from_date'];
    $_fromdate = date("d-M-Y", strtotime($from_date));
    
    $to_date = $row['to_date'];
    $_todate = date("d-M-Y", strtotime($to_date));

    // यूजर का FCM टोकन प्राप्त करें
    $getTokenQuery = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE id='$userid'");
    $tokenData = mysqli_fetch_assoc($getTokenQuery);
    $fcmToken = $tokenData['fcm_token'] ?? null;

    if ($fcmToken) {
        $response = sendNotification($fcmToken, "Emergency Leave Approved", "Your Emergency Leave request has been approved from ". $_fromdate ." to ".$_todate );

        $response_data = json_decode($response, true);
        if (isset($response_data['name'])) {
            // नोटिफिकेशन भेजने के बाद approval_notification_status को 1 में अपडेट करें
            $update_query = "UPDATE apply_emergency_leave 
                             SET approval_notification_status = 1, 
                                 status_updated_at = '$today_datetime' 
                             WHERE id = '$leaveID'";
            mysqli_query($con, $update_query);

            $notifications_sent[] = [
                "userid" => $userid,
                "status" => "approved",
                "sent_at" => date("H:i:s")
            ];
        }
    } else {
        error_log("No FCM Token found for user $userid");
    }
}

// **2. REJECTED Users के लिए नोटिफिकेशन भेजना**
$query_rejected = "SELECT * FROM apply_emergency_leave 
                   WHERE approval_status = 'rejected' 
                   AND approval_notification_status = 0";

$result_rejected = mysqli_query($con, $query_rejected);

while ($row = mysqli_fetch_assoc($result_rejected)) {
    $userid = $row['userid'];
    $leaveID = $row['id'];
    $from_date = $row['from_date'];
    $_fromdate = date("d-M-Y", strtotime($from_date));
    
    $to_date = $row['to_date'];
    $_todate = date("d-M-Y", strtotime($to_date));

    // यूजर का FCM टोकन प्राप्त करें
    $getTokenQuery = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE id='$userid'");
    $tokenData = mysqli_fetch_assoc($getTokenQuery);
    $fcmToken = $tokenData['fcm_token'] ?? null;

    if ($fcmToken) {
        $response = sendNotification($fcmToken, "Emergency Leave Rejected", "Your Emergency Leave Request has been rejected from ". $_fromdate ." to ".$_todate   );

        $response_data = json_decode($response, true);
        if (isset($response_data['name'])) {
            // नोटिफिकेशन भेजने के बाद approval_notification_status को 1 में अपडेट करें
            $update_query = "UPDATE apply_emergency_leave 
                             SET approval_notification_status = 1, 
                                 status_updated_at = '$today_datetime' 
                             WHERE id = '$leaveID'";
            mysqli_query($con, $update_query);

            $notifications_sent[] = [
                "userid" => $userid,
                "status" => "rejected",
                "sent_at" => date("H:i:s")
            ];
        }
    } else {
        error_log("No FCM Token found for user $userid");
    }
}

echo json_encode(["status" => "success", "notifications_sent" => $notifications_sent]);

?>
