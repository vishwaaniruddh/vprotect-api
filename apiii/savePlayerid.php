<?php

include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$upd_at = date("Y-m-d H:i:s");

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if the required data is sent
if (isset($data['userid']) && isset($data['onesignal_player_id'])) {
    $userid = $data['userid'];  // User's ID
    $onesignal_player_id = $data['onesignal_player_id'];  // OneSignal Player ID

    // Sanitize the inputs to prevent SQL injection
    $userid = mysqli_real_escape_string($con, $userid);
    $onesignal_player_id = mysqli_real_escape_string($con, $onesignal_player_id);

    // SQL query to update the Player ID if the user ID exists
    $sql = "UPDATE user_login SET onesignal_player_id = '$onesignal_player_id', updated_at = '$upd_at' WHERE id = '$userid'";

    // Execute the query to update Player ID
    if (mysqli_query($con, $sql)) {
        if (mysqli_affected_rows($con) > 0) {
            // Player ID updated successfully
            echo json_encode(["status" => "success", "message" => "Player ID updated successfully"]);
        } else {
            // If no rows were updated, insert a new user with the Player ID
            $insert_sql = "INSERT INTO user_login (id, onesignal_player_id) VALUES ('$userid', '$onesignal_player_id')";
            if (mysqli_query($con, $insert_sql)) {
                echo json_encode(["status" => "success", "message" => "Player ID inserted successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error inserting Player ID"]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating Player ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
}

?>
