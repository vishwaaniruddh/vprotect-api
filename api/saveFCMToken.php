<?php

include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$upd_at = date("Y-m-d H:i:s");

// Get the POST data
// $data = json_decode(file_get_contents("php://input"));
$data = $_POST;

// Check if the required data is sent
if (isset($data['userid']) && isset($data['fcm_token'])) {
    $userid = $data['userid'];  // User's ID
    $fcm_token = $data['fcm_token'];  // OneSignal FCM TOKEN

    // Sanitize the inputs to prevent SQL injection
    $userid = mysqli_real_escape_string($con, $userid);
    // $fcm_token = mysqli_real_escape_string($con, $fcm_token);

    // SQL query to update the FCM TOKEN if the user ID exists
    $sql = "UPDATE user_login SET fcm_token = '$fcm_token', updated_at = '$upd_at' WHERE id = '$userid'";

    // Execute the query to update FCM TOKEN
    if (mysqli_query($con, $sql)) {
        if (mysqli_affected_rows($con) > 0) {
            // FCM TOKEN updated successfully
            echo json_encode(["status" => "success", "message" => "FCM TOKEN updated successfully"]);
        } 
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating FCM TOKEN"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
}

?>
