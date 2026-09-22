<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define("ONESIGNAL_APP_ID", "3d731adb-d025-45d8-9fbf-0090df778589"); 
define("ONESIGNAL_REST_API_KEY", "Basic os_v2_app_hvzrvw6qevc5rh57acin654frh7qn2v4a2fujg5il7xkkmnvru5w4qpkyixx3xeam5ejt3hzxciy3xr5ebmijq3fx2kmitgivo5frbq");

$subscribed_users = [];
$unsubscribed_users = [];
$debug_data = [];

// WFH स्टेटस वाले यूजर्स लाओ
$sql = "SELECT userid FROM punch_in_out WHERE wfh_status != 0";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $userid = $row['userid'];

        // OneSignal Player ID निकालो
        $onesignalQuery = "SELECT onesignal_player_id FROM user_login WHERE id = '$userid' AND onesignal_player_id IS NOT NULL AND onesignal_player_id != ''";
        $onesignalResult = mysqli_query($con, $onesignalQuery);

        if ($onesignalResult && mysqli_num_rows($onesignalResult) > 0) {
            while ($os_row = mysqli_fetch_assoc($onesignalResult)) {
                $player_id = trim($os_row['onesignal_player_id']);

                // OneSignal API से Subscription Status चेक करो
                $subscription_status = getSubscriptionStatus($player_id);
                $debug_data[] = ["userid" => $userid, "player_id" => $player_id, "status" => $subscription_status];

                if ($subscription_status === 'subscribed') {
                    $subscribed_users[] = $player_id;
                } else {
                    $unsubscribed_users[] = $userid;
                }
            }
        } else {
            $unsubscribed_users[] = $userid;
        }
    }

    // अगर कोई यूजर सब्सक्राइब्ड है, तो नोटिफिकेशन भेजो
    if (!empty($subscribed_users)) {
        sendNotification($subscribed_users);
        echo json_encode(["status" => "okay", "message" => "Notifications Sent", "subscribed_users" => $subscribed_users, "unsubscribed_users" => $unsubscribed_users, "debug" => $debug_data]);
    } else {
        echo json_encode(["status" => "error", "message" => "No subscribed users found.", "unsubscribed_users" => $unsubscribed_users, "debug" => $debug_data]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No employees found with WFH status."]);
}

// 🔹 **OneSignal Subscription Status API Call**
function getSubscriptionStatus($player_id) {
    $ch = curl_init("https://api.onesignal.com/apps/".ONESIGNAL_APP_ID."/players/".$player_id);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . ONESIGNAL_REST_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // **Debugging के लिए Response देखें**
    file_put_contents('onesignal_debug.log', print_r($data, true));

    return isset($data['subscription_status']) ? $data['subscription_status'] : 'unknown';
}

// 🔹 **OneSignal Push Notification Send**
function sendNotification($playerIds) {
    $content = [
        "en" => "Reminder: You're working from home today. Stay productive!"
    ];

    $fields = [
        'app_id' => ONESIGNAL_APP_ID,
        'include_player_ids' => $playerIds,
        'data' => ["type" => "WFH_NOTIFICATION"],
        'contents' => $content
    ];

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.onesignal.com/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . ONESIGNAL_REST_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
