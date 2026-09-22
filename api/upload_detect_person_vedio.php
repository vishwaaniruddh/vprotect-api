<?php

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '120M');
ini_set('max_execution_time', '120');
ini_set('max_input_time', '120');

file_put_contents(
    __DIR__ . '/upload_debug.txt',
    "\n\n========== NEW REQUEST ==========\n" .
    "TIME: " . date('Y-m-d H:i:s') . "\n" .
    "METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n" .
    "CONTENT_LENGTH: " . ($_SERVER['CONTENT_LENGTH'] ?? '') . "\n" .
    "CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? '') . "\n" .
    "POST: " . print_r($_POST, true) . "\n" .
    "FILES: " . print_r($_FILES, true) . "\n",
    FILE_APPEND
);

/* ===================== REQUEST START ===================== */

$requestStartTime = date('Y-m-d H:i:s');
$current_date_time = date('Y-m-d H:i:s');
$startMicroTime = microtime(true);

$userid = isset($_POST['userid']) ? trim($_POST['userid']) : '';

if (empty($userid) && isset($_POST['emp_id'])) {
    $userid = trim($_POST['emp_id']);
}
$response = [];

/* ===================== LOG FUNCTION ===================== */
function logVideoAction(
    $con,
    $userid,
    $username,
    $action,
    $status,
    $startTime,
    $endTime,
    $totalTime,
    $curlTime = 0,
    $httpCode = 0,
    $pythonResponse = '',
    $current_date_time = ''
) {

    if (empty($current_date_time)) {
        $current_date_time = date('Y-m-d H:i:s');
    }

    $action = mysqli_real_escape_string($con, $action);
    $pythonResponse = mysqli_real_escape_string($con, $pythonResponse);

    $sql = "
    INSERT INTO daily_action_vedio_log
    (
        user_id,
        user_name,
        action,
        action_status,
        request_start_time,
        request_end_time,
        total_time_seconds,
        curl_time_seconds,
        http_code,
        python_response,
        created_at
    )
    VALUES
    (
        '$userid',
        '$username',
        '$action',
        '$status',
        '$startTime',
        '$endTime',
        '$totalTime',
        '$curlTime',
        '$httpCode',
        '$pythonResponse',
        '$current_date_time'
    )";

    mysqli_query($con, $sql);
}

/* ===================== VALID REQUEST ===================== */

if (
    !empty($userid) &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    (
        isset($_FILES['detect_person_video']) ||
        isset($_FILES['video'])
    )
) {

    $userid = mysqli_real_escape_string($con, $userid);
    if (isset($_FILES['video']) && !isset($_FILES['detect_person_video'])) {
    $_FILES['detect_person_video'] = $_FILES['video'];
}

    /* ===================== USER DETAILS ===================== */

    $getusername = mysqli_query(
        $con,
        "SELECT name FROM user_login WHERE id='$userid'"
    );

    $userRow = mysqli_fetch_assoc($getusername);

    if (!$userRow) {

        $requestEndTime = date('Y-m-d H:i:s');
        $totalTime = round((microtime(true) - $startMicroTime), 2);

        echo json_encode([
            'Code' => 404,
            'status' => 'error',
            'message' => 'User not found',
            'execution_time' => $totalTime . ' sec'
        ]);
        exit;
    }

    $username = $userRow['name'];

    /* ===================== VIDEO VALIDATION ===================== */

    $videoFile = $_FILES['detect_person_video'];

    $videoFileType = strtolower(
        pathinfo($videoFile["name"], PATHINFO_EXTENSION)
    );

    $allowedTypes = ["mp4", "avi", "mov", "mkv"];

    if (!in_array($videoFileType, $allowedTypes)) {

        $requestEndTime = date('Y-m-d H:i:s');
        $totalTime = round((microtime(true) - $startMicroTime), 2);

        logVideoAction(
            $con,
            $userid,
            $username,
            "Invalid video format",
            "Failed",
            $requestStartTime,
            $requestEndTime,
            $totalTime,
            0,
            400,
            '',
            $current_date_time
        );

        echo json_encode([
            'Code' => 400,
            'status' => 'error',
            'message' => 'Only MP4, AVI, MOV, MKV allowed',
            'execution_time' => $totalTime . ' sec'
        ]);
        exit;
    }

    /* ===================== PYTHON API ===================== */

    $pythonApiUrl = "http://195.35.7.83:6009/detectspoof";

    $video = new CURLFile(
        $videoFile["tmp_name"],
        $videoFile["type"],
        $videoFile["name"]
    );

    $postData = [
        "emp_id" => $userid,
        "video" => $video
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $curlStart = microtime(true);
    $curlResponse = curl_exec($ch);
    $curlEnd = microtime(true);
    $curlExecutionTime = round(
        ($curlEnd - $curlStart),
        2
    );

    /* ===================== CURL ERROR ===================== */

    if (curl_errno($ch)) {

        $curlError = curl_error($ch);

        $requestEndTime = date('Y-m-d H:i:s');
        $totalTime = round((microtime(true) - $startMicroTime), 2);

        logVideoAction(
            $con,
            $userid,
            $username,
            "Video spoof detection curl error",
            "Failed",
            $requestStartTime,
            $requestEndTime,
            $totalTime,
            $curlExecutionTime,
            500,
            $curlError,
            $current_date_time
        );

        curl_close($ch);

        echo json_encode([
            'Code' => 500,
            'status' => 'error',
            'message' => $curlError,
            'execution_time' => $totalTime . ' sec'
        ]);
        exit;
    }

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* ===================== REQUEST END ===================== */

$requestEndTime = date('Y-m-d H:i:s');
$totalTime = round((microtime(true) - $startMicroTime), 2);

/* ===================== PYTHON RESPONSE ===================== */

$pythonData = json_decode($curlResponse, true);

$pythonCode = $pythonData['Code'] ?? null;
$pythonMsg  = $pythonData['msg'] ?? '';


/* ===================== PYTHON API RESPONSE HANDLING ===================== */

if ($httpCode == 200 && $pythonCode !== null) {

    switch ($pythonCode) {

        /* ===================== FACE MATCH SUCCESS ===================== */

        case 200:

            logVideoAction(
                $con,
                $userid,
                $username,
                "Face match successful",
                "Success",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 200,
                'status' => 'Success',
                'message' => $pythonMsg,
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;


        /* ===================== NO FACE FOUND ===================== */

        case 201:

            logVideoAction(
                $con,
                $userid,
                $username,
                "No face found",
                "Failed",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 201,
                'status' => 'error',
                'message' => $pythonMsg,
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;


        /* ===================== MATCH NOT FOUND ===================== */

        case 202:

            logVideoAction(
                $con,
                $userid,
                $username,
                "Face match not found",
                "Failed",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 202,
                'status' => 'error',
                'message' => $pythonMsg,
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;


        /* ===================== SPOOF ATTEMPT ===================== */

        case 203:

            logVideoAction(
                $con,
                $userid,
                $username,
                "Spoof attempt detected",
                "Failed",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 203,
                'status' => 'error',
                'message' => $pythonMsg,
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;


        /* ===================== INVALID IMAGE / NO FACE ===================== */

        case 204:

            logVideoAction(
                $con,
                $userid,
                $username,
                "Invalid image - no face found",
                "Failed",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 204,
                'status' => 'error',
                'message' => $pythonMsg,
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;


        /* ===================== UNKNOWN PYTHON CODE ===================== */

        default:

            logVideoAction(
                $con,
                $userid,
                $username,
                "Unknown Python response",
                "Failed",
                $requestStartTime,
                $requestEndTime,
                $totalTime,
                $curlExecutionTime,
                $httpCode,
                $curlResponse,
                $current_date_time
            );

            $response = [
                'Code' => 250,
                'status' => 'error',
                'message' => 'Unknown response from Python API',
                'execution_time' => $totalTime . ' sec',
                'python_response' => $pythonData
            ];

            break;
    }

}


/* ===================== PYTHON HTTP/API FAILURE ===================== */

else {

    logVideoAction(
        $con,
        $userid,
        $username,
        "Video spoof detection failed",
        "Failed",
        $requestStartTime,
        $requestEndTime,
        $totalTime,
        $curlExecutionTime,
        $httpCode,
        $curlResponse,
        $current_date_time
    );

    $response = [
        'Code' => 250,
        'status' => 'error',
        'message' => 'Python API failed',
        'execution_time' => $totalTime . ' sec',
        'python_response' => $curlResponse
    ];
}

} else {

    $requestEndTime = date('Y-m-d H:i:s');
    $totalTime = round((microtime(true) - $startMicroTime), 2);

    $response = [
        'Code' => 450,
        'status' => 'error',
        'message' => 'User ID missing or invalid request',
        'execution_time' => $totalTime . ' sec',
        'curren_datetime'=>$requestEndTime
    ];
}

echo json_encode($response);

?>