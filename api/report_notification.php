<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ID', 'testing-app-e27e1');

$data = $_POST;
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
                "custom" => "data",
                "screen" => "ReportNotification"
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

// Input values
$_application_name = isset($data['application_name']) ? trim($data['application_name']) : '';
$_purpose = isset($data['purpose']) ? trim($data['purpose']) : '';

if ($_application_name != '' && $_purpose != '') {
    // Insert record
    $insertsql = mysqli_query($con, "INSERT INTO report_notification (application_name, purpose, created_at) VALUES ('$_application_name', '$_purpose', '$created_at')");

    if ($insertsql) {
        $query_approved = "SELECT * FROM report_notification WHERE send_report_notification_status = 0";
        $result_approved = mysqli_query($con, $query_approved);

        while ($row = mysqli_fetch_assoc($result_approved)) {
            $report_id = $row['id'];
            $application_name = $row['application_name'];
            $purpose = $row['purpose'];

            // For now sending only to user ID 4
            // $getTokensQuery = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE id = '4'");
            $getTokensQuery = mysqli_query($con, "SELECT fcm_token FROM user_login WHERE fcm_token IS NOT NULL AND fcm_token != ''");

            while ($tokenRow = mysqli_fetch_assoc($getTokensQuery)) {
                $fcmToken = $tokenRow['fcm_token'];

                if (!empty($fcmToken)) {
                    $responseFCM = sendNotification($fcmToken, "🔔 Report Notification", "Application Name: " . $application_name . " | Message: " . $purpose);
                    $response_data = json_decode($responseFCM, true);

                    if (isset($response_data['name'])) {
                        $notifications_sent[] = [
                            "token" => $fcmToken,
                            "status" => "Notification Sent",
                            "sent_at" => date("H:i:s")
                        ];
                    }
                }
            }

            // Update notification sent status
            mysqli_query($con, "UPDATE report_notification SET send_report_notification_status = 1, updated_at = '$created_at' WHERE id = '$report_id'");
        }

        $response = [
            'Code' => 200,
            'message' => 'Report Inserted and Notification Sent',
            'notifications' => $notifications_sent
        ];
    } else {
        $response = [
            'Code' => 400,
            'message' => 'Unable to insert the report!'
        ];
    }
} else {
    $response = [
        'Code' => 422,
        'message' => 'Required fields missing!'
    ];
}

echo json_encode($response);
?>
