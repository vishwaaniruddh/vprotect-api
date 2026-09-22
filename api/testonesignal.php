<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// OneSignal Credentials
define("ONESIGNAL_APP_ID", "3d731adb-d025-45d8-9fbf-0090df778589"); // OneSignal App ID
define("ONESIGNAL_REST_API_KEY", "os_v2_app_hvzrvw6qevc5rh57acin654frh7qn2v4a2fujg5il7xkkmnvru5w4qpkyixx3xeam5ejt3hzxciy3xr5ebmijq3fx2kmitgivo5frbq"); // OneSignal REST API Key

$player_id = "dbdf02de-d05e-4130-a77d-c04d66754d67";

if (empty($player_id)) {
    echo json_encode(["status" => "error", "message" => "Player ID is missing"]);
    exit;
}

$url = "https://onesignal.com/api/v1/players/$player_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . ONESIGNAL_REST_API_KEY
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL Verification disable if needed

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo json_encode([
        "status" => "success",
        "message" => "Player ID details fetched successfully",
        "data" => json_decode($response, true)
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch player details",
        "response" => json_decode($response, true)
    ]);
}
?>
