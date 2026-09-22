<?php

include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1');

$currdate = date("Y-m-d");
$today_datetime = date("Y-m-d H:i:s");

$notifications_sent = [];

// Function to send FCM Notification
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

$query_users = "SELECT id, fcm_token FROM user_login WHERE user_role = 6";
// $query_users = "SELECT id, fcm_token FROM user_login WHERE id = 4 ";
$result_users = mysqli_query($con, $query_users);
$users = [];

while ($row = mysqli_fetch_assoc($result_users)) {
    $users[] = [
        "id" => $row['id'],
        "fcm_token" => $row['fcm_token']
    ];
}

$tables = [
    "apply_work_from_home" => "Work From Home Requests",
    "apply_leave" => "Leave Requests",
    "apply_emergency_leave" => "Emergency Leave Requests"
];

foreach ($tables as $table => $message) {
    $query_new_entries = "SELECT COUNT(*) as new_entries FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) and approval_status !='Approved';";
    // $query_new_entries = "SELECT COUNT(*) as new_entries FROM $table WHERE DATE(created_at) = CURDATE() ";
    $result_new_entries = mysqli_query($con, $query_new_entries);
    $row_new_entries = mysqli_fetch_assoc($result_new_entries);
    
    if ($row_new_entries['new_entries'] > 0) {
        foreach ($users as $user) {
            if (!empty($user['fcm_token'])) {
                $response = sendNotification($user['fcm_token'], "📅🔔  New Entry Alert", "There are new entries in $message .");
                
                $response_data = json_decode($response, true);
                if (isset($response_data['name'])) {
                    $notifications_sent[] = [
                        "userid" => $user['id'],
                        "table" => $table,
                        "sent_at" => date("H:i:s")
                    ];
                }
            }
        }
    }
}

echo json_encode(["status" => "success", "notifications_sent" => $notifications_sent]);

?>
