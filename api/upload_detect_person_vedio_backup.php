<?php

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');

include(__DIR__ . '/config/config.php');

// echo "ABC";
// die;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
$response = [];

if ($userid && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['detect_person_video'])) {

    $videoFile = $_FILES['detect_person_video'];

    $videoFileType = strtolower(pathinfo($videoFile["name"], PATHINFO_EXTENSION));

    // allowed video formats
    $allowedTypes = array("mp4","avi","mov","mkv");

    if (in_array($videoFileType, $allowedTypes)) {

        // Python API URL
        $pythonApiUrl = "http://192.168.10.25:8090/detectspoof";
        // $pythonApiUrl = "http://192.168.10.25:8090/check";

        $video = new CURLFile(
            $videoFile["tmp_name"],
            $videoFile["type"],
            $videoFile["name"]
        );

        $postData = [
            "emp_id" => $userid,
            "video"  => $video
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $curlResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode == 200) {

            $response['Code'] = 200;
            $response["status"] = "Success";
            $response["message"] = "Video processed successfully.";
            $response["python_response"] = json_decode($curlResponse, true);

        } else {

            $response['Code'] = 250;
            $response["status"] = "error";
            $response["message"] = "Python API failed.";
            $response["python_response"] = $curlResponse;
        }

    } else {

        $response['Code'] = 400;
        $response["status"] = "error";
        $response["message"] = "Invalid video format. Only MP4, AVI, MOV, MKV allowed.";
    }

} else {

    $response = [
        'Code' => 450,
        'status' => 'error',
        'message' => "User ID missing or invalid request!",
    ];
}

echo json_encode($response);

?>