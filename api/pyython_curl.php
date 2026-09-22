<?php

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

//include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');


$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
// $image_url = isset($_GET['image_url']) ? $_GET['image_url'] : '';

$getusername = mysqli_query($con, "SELECT profile_img FROM user_login WHERE id = '$userid'");
    $img = mysqli_fetch_assoc($getusername)['profile_img'];

if (!$img) {
    echo json_encode(["status" => "error", "message" => "Image URL is required",]);
    exit();
}

// Python API URL
$pythonApiUrl = "http://195.35.7.83:5048/generatefaceencodings?image_url=" . $img;
//echo $pythonApiUrl;die;
// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
curl_setopt($ch, CURLOPT_POST, true);  // -X POST
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response
// echo json_encode([
//     "status" => $httpCode == 200 ? "success" : "error",
//     "message" => $httpCode == 200 ? "Face encoding generated successfully" : "Failed to generate face encoding",
//     "url" => $pythonApiUrl,
//     "response" => json_decode($response)
// ]);

$responseData = json_decode($response, true);

if ( $httpCode == 200 && isset($responseData['success']) && $responseData['success'] == true){

    echo json_encode([
        "status" => "success",
        "message" => "Face encoding generated successfully",
        "url" => $pythonApiUrl,
        "response" => $responseData
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => isset($responseData['message'])
            ? $responseData['message']
            : "Failed to generate face encoding",
        "url" => $pythonApiUrl,
        "response" => $responseData
    ]);

}

?>
