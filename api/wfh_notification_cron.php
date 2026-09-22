<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');
$time = date("H:i:s", strtotime($created_at));

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1');

$response = [];
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
        die(json_encode(["error" => "Firebase Admin SDK JSON file not found!"]));
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
        die(json_encode(["error" => "Failed to load private key!"]));
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
        die(json_encode(["error" => "Failed to get access token", "details" => $responseData]));
    }

    return $responseData['access_token'];
}

$checktodaywfh = mysqli_query($con,"select * from apply_work_from_home where from_date = '$todays_date' and approval_status='Approved' ");
if(mysqli_num_rows($checktodaywfh)>0){
    // Get all pending status updates
$query_approved = "SELECT * FROM work_from_home_notifications WHERE (`1st_status_updated_at` IS NULL OR `2nd_status_updated_at` IS NULL OR `3rd_status_updated_at` IS NULL) and from_date = '$todays_date' ";
$result_approved = mysqli_query($con, $query_approved);

if (mysqli_num_rows($result_approved) > 0) {
    while ($row = mysqli_fetch_assoc($result_approved)) {
        $user_id = $row['userid'];

        // Prevent sending multiple notifications per user
        if (isset($notifications_sent[$user_id])) continue;

        // Get FCM token
        $getfcmtoken = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE id = '$user_id'");
        $fcmToken = mysqli_fetch_assoc($getfcmtoken)['fcm_token'];

        $_1st_notification_status = $row['1st_notification_status'];
        $_2nd_notification_status = $row['2nd_notification_status'];
        $_3rd_notification_status = $row['3rd_notification_status'];

        $_1st_status_updated_at = $row['1st_status_updated_at'] ?: '';
        $_2nd_status_updated_at = $row['2nd_status_updated_at'] ?: '';
        $_3rd_status_updated_at = $row['3rd_status_updated_at'] ?: '';

        // Get punch-in time
        $punchintimesql = mysqli_query($con, "SELECT punch_in FROM punch_in_out WHERE userid = '$user_id' AND date = '$todays_date'");
        $punch_in_time = mysqli_fetch_assoc($punchintimesql)['punch_in'];

        // Time difference from punch in
        $punchInTime = new DateTime($punch_in_time);
        $punchOutTime = new DateTime($time);
        $interval = $punchInTime->diff($punchOutTime);

        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;

        // Notification logic: Only one notification per user
        $stage = null;
        if ($_1st_notification_status == 1 && $_2nd_notification_status == 0 && $_3rd_notification_status == 0 && $_1st_status_updated_at == '') {
            $stage = "1st";
        } elseif ($_1st_notification_status == 1 && $_2nd_notification_status == 1 && $_3rd_notification_status == 0 && $_2nd_status_updated_at == '') {
            $stage = "2nd";
        } elseif ($_1st_notification_status == 1 && $_2nd_notification_status == 1 && $_3rd_notification_status == 1 && $_3rd_status_updated_at == '') {
            $stage = "3rd";
        }

        if ($stage) {
            $responseFCM = sendNotification($fcmToken, "🕒⏳ Work From Home Notification Reminder", "Work From Home Updates Pending!! ($stage)");
            $response_data = json_decode($responseFCM, true);

            if (isset($response_data['name'])) {
                $notifications_sent[$user_id] = [
                    "userid" => $user_id,
                    "stage" => $stage,
                    "status" => "Notification Sent",
                    "sent_at" => date("H:i:s")
                ];
            }
        }
    }

    $response = [
        'Code' => 200,
        'message' => 'WFH Notification Sent',
        'notifications' => array_values($notifications_sent)
    ];
} else {
    $response = [
        'Code' => 400,
        'message' => 'No pending WFH notifications.',
        'notifications' => []
    ];
}
}else {
    $response= [
        'Code' => 500,
        'msg' => "No Work From Home Today."
        ];
}



echo json_encode($response);
?>
