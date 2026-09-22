<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = $_POST;

define('PROJECT_ID', 'testing-app-e27e1');

function sendNotification($fcmToken, $title, $body)
{
    $url = "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send";

    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => $title,
                "body" => $body
            ],
            "data" => [
                "extra_payload" => "some_value"
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

// **Google OAuth Token प्राप्त करने का सही तरीका**
function getAccessToken()
{
    $keyFilePath = 'firebase/testing-app-e27e1-firebase-adminsdk-fbsvc-6e3da28fce.json';
    $keyData = json_decode(file_get_contents($keyFilePath), true);

    $header = base64_encode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $claimSet = base64_encode(json_encode([
        "iss" => $keyData["client_email"],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://oauth2.googleapis.com/token",
        "exp" => time() + 3600,
        "iat" => time()
    ]));

    openssl_sign("$header.$claimSet", $signature, $keyData["private_key"], OPENSSL_ALGO_SHA256);
    $jwt = "$header.$claimSet." . base64_encode($signature);

    // **cURL के जरिए Access Token लेना**
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    return $responseData['access_token'] ?? null;
}

// **नोटिफिकेशन भेजने के लिए**
// $fcmToken = 'eC2LtLxQSKSum-CSqyLoU9:APA91bFn-OYQRwu81cMJRf-jwG-x5-XZQnOnMJy4EAFWY_7TZwpB11n5YXjdphVRFyJfzGISRl4bto3_kpaqAug4y3u23V91fGnyUCPrNLwZGIrsUGJdp7M';
$fcmToken = $data['device_token'];
$title = "New Notification From PHP API!";
$body = "You have a new message.";

$response = sendNotification($fcmToken, $title, $body);
echo $response;
?>
