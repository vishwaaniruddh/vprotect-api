<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';

if (empty($userid)) {
    echo json_encode([
        "status" => "error",
        "message" => "userid is required"
    ]);
    exit;
}

$getusername = mysqli_query(
    $con,
    "SELECT profile_img FROM user_login WHERE id = '".mysqli_real_escape_string($con,$userid)."'"
);

$row = mysqli_fetch_assoc($getusername);

if (!$row || empty($row['profile_img'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Profile image not found"
    ]);
    exit;
}

$image_url = $row['profile_img'];

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://195.35.7.83:5050/generatefaceencoding',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => array(
        'emp_id'   => $userid,
        'image_url'=> $image_url
    ),
));

$response = curl_exec($curl);

if (curl_errno($curl)) {

    echo json_encode([
        "status" => "error",
        "message" => curl_error($curl)
    ]);

    curl_close($curl);
    exit;
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$responseData = json_decode($response, true);

if (
    $httpCode == 200 &&
    isset($responseData['encoding']) &&
    isset($responseData['emp_id'])
) {

    $emp_id   = mysqli_real_escape_string($con, $responseData['emp_id']);
    $encoding = mysqli_real_escape_string($con, $responseData['encoding']);

    $updateQuery = "
        UPDATE user_login
        SET faceEncoding2 = '".$encoding."'
        WHERE id = '$emp_id'
    ";

    if (mysqli_query($con, $updateQuery)) {

        echo json_encode([
            "status" => "success",
            "message" => "Face encoding generated and saved successfully",
            "emp_id" => $emp_id
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => "Database update failed",
            "db_error" => mysqli_error($con)
        ]);
    }

} else {

    echo json_encode([
        "status" => "error",
        "message" => isset($responseData['message'])
            ? $responseData['message']
            : "Failed to generate face encoding",
        "response" => $responseData
    ]);
}