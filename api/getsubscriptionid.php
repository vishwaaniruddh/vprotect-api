<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getSubscriptionIdFromOneSignalId($oneSignalId) {
    $appId = "3d731adb-d025-45d8-9fbf-0090df778589";
    $apiKey = "os_v2_app_hvzrvw6qevc5rh57acin654frh7qn2v4a2fujg5il7xkkmnvru5w4qpkyixx3xeam5ejt3hzxciy3xr5ebmijq3fx2kmitgivo5frbq";

    // ✅ Correct API URL with app_id
    $url = "https://onesignal.com/api/v1/players/$oneSignalId?app_id=$appId";

    $headers = [
        "Content-Type: application/json",
        "Authorization: Basic $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    // 🔍 Debugging API Response
    echo "API Response: ";
    print_r($data);

    if (isset($data['id'])) {
        return $data['id'];  // Subscription ID
    } else {
        return "No subscription ID found or invalid OneSignal ID.";
    }
}

// Example usage
$oneSignalId = '064246a5-a719-40f0-ba35-c27ad33d71a9';
$subscriptionId = getSubscriptionIdFromOneSignalId($oneSignalId);
echo "Subscription ID: " . $subscriptionId;
?>
