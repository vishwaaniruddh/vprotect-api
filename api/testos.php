<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// OneSignal Credentials
define("ONESIGNAL_APP_ID", "3d731adb-d025-45d8-9fbf-0090df778589");
define("ONESIGNAL_REST_API_KEY", "os_v2_app_hvzrvw6qevc5rh57acin654frbq");

$limit = 50; // Max 300 per request
$offset = 0; // Start from 0

$all_player_ids = [];

while (true) {
    $url = "https://onesignal.com/api/v1/players?app_id=" . ONESIGNAL_APP_ID . "&limit=$limit&offset=$offset";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . ONESIGNAL_REST_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_code == 200 && isset($data['players']) && !empty($data['players'])) {
        // Extract Only Player IDs
        foreach ($data['players'] as $player) {
            if (!empty($player['id'])) {
                $all_player_ids[] = $player['id'];
            }
        }

        // If less than limit users fetched, stop loop
        if (count($data['players']) < $limit) {
            break;
        }

        // Increase offset for next batch
        $offset += $limit;
    } else {
        break;
    }
}

// Final Response with Only Player IDs
if (!empty($all_player_ids)) {
    echo json_encode([
        "status" => "success",
        "message" => count($all_player_ids) . " OneSignal IDs fetched successfully",
        "player_ids" => $all_player_ids
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No OneSignal IDs found or API limit exceeded."
    ], JSON_PRETTY_PRINT);
}
?>
