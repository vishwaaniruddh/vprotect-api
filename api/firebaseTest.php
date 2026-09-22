<?php

include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// **JSON या POST से डेटा प्राप्त करें**
$json = file_get_contents('php://input');
$data = json_decode($json, true) ?: $_POST;

// **Debugging Logs**
error_log("Received JSON: " . $json);
error_log("Decoded Data: " . print_r($data, true));

if (!isset($data['device_token']) || empty($data['device_token'])) {
    die(json_encode(["error" => "Device token is missing"]));
}

$_device_token = trim($data['device_token']);

// **JWT Token Generation**
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function getAccessToken() {
    $keyFile = $_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/firebase/testing-app-e27e1-firebase-adminsdk-fbsvc-6e3da28fce.json';
    
    if (!file_exists($keyFile)) {
        error_log("Firebase key file missing: " . $keyFile);
        return null;
    }

    $serviceAccount = json_decode(file_get_contents($keyFile), true);
    if (!$serviceAccount) {
        error_log("Invalid Firebase service account JSON.");
        return null;
    }

    if (!isset($serviceAccount['private_key'])) {
        error_log("Private key missing in Firebase service account JSON.");
        return null;
    }

    $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
    if (!$privateKey) {
        error_log("Failed to load private key.");
        return null;
    }

    $now = time();
    $jwtHeader = base64UrlEncode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $jwtPayload = base64UrlEncode(json_encode([
        "iss" => $serviceAccount['client_email'],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://oauth2.googleapis.com/token",
        "exp" => $now + 3600,
        "iat" => $now
    ]));

    openssl_sign("$jwtHeader.$jwtPayload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $jwt = "$jwtHeader.$jwtPayload." . base64UrlEncode($signature);

    $data = [
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decodedResponse = json_decode($response, true);
    if ($httpCode !== 200 || !isset($decodedResponse['access_token'])) {
        error_log("Failed to get access token. HTTP Code: $httpCode, Response: " . json_encode($decodedResponse));
        return null;
    }

    return $decodedResponse['access_token'];
}

// **Firebase Notification भेजने का Function**
function sendNotification($_device_token, $title, $body) {
    $accessToken = getAccessToken();
    if (!$accessToken) {
        return json_encode(["error" => "Failed to get access token."]);
    }

    $url = "https://fcm.googleapis.com/v1/projects/attendance-app-2d5e0/messages:send";

    $payload = [
        "message" => [
            "token" => $_device_token,
            "notification" => [
                "title" => $title,
                "body" => $body
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("cURL Error: $curlError");
        return json_encode(["error" => "cURL Error: $curlError"]);
    }

    if ($httpCode !== 200) {
        error_log("Firebase API Response: HTTP Code: $httpCode, Response: " . $response);
        return json_encode(["error" => "HTTP Code: $httpCode", "response" => json_decode($response, true)]);
    }

    return json_decode($response, true);
}

// **Firebase Notification भेजें**
$title = "Test Notification";
$body = "AAya kya notification Shivam";
$response = sendNotification($_device_token, $title, $body);

echo json_encode($response);

?>
