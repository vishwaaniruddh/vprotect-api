<?php

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$current_datetime = date('Y-m-d H:i:s');

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
$response = [];

/* ===================== COMMON FUNCTIONS ===================== */

function logAction($con, $userid, $username, $action, $status, $time) {
    $action = mysqli_real_escape_string($con, $action);
    mysqli_query($con, "INSERT INTO daily_action_log(user_id,user_name,action,action_status,action_time,created_at)
    VALUES ('$userid','$username','$action','$status','$time','$time')");
}

function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE: return "File exceeds upload_max_filesize";
        case UPLOAD_ERR_FORM_SIZE: return "File exceeds MAX_FILE_SIZE";
        case UPLOAD_ERR_PARTIAL: return "File partially uploaded";
        case UPLOAD_ERR_NO_FILE: return "No file uploaded";
        case UPLOAD_ERR_NO_TMP_DIR: return "Missing temp folder";
        case UPLOAD_ERR_CANT_WRITE: return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION: return "Upload stopped by extension";
        case UPLOAD_ERR_OK: return "No error";
        default: return "Unknown error";
    }
}

/* ===================== MAIN LOGIC ===================== */

if ($userid && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['detect_person_img'])) {

    $userid = mysqli_real_escape_string($con, $userid);

    $getusername = mysqli_query($con, "SELECT name FROM user_login WHERE id = '$userid'");
    $userRow = mysqli_fetch_assoc($getusername);

    if (!$userRow) {
        echo json_encode(['Code'=>404,'status'=>'error','message'=>'User not found']);
        exit;
    }

    $username = $userRow['name'];
    $newname = str_replace(' ', '', $username);

    $file = $_FILES['detect_person_img'];

    /* ===================== DIRECTORY ===================== */

    // $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/FRUtopia/api/uploads/detect_person/$userid/";
    $uploadDir = __DIR__ . "/uploads/detect_person/$userid/";
    

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!is_writable($uploadDir)) {
        echo json_encode([
            'Code' => 500,
            'status' => 'error',
            'message' => 'Directory not writable',
            'path' => $uploadDir
        ]);
        exit;
    }

    /* ===================== FILE VALIDATION ===================== */

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png"];

    if (!in_array($imageFileType, $allowedTypes)) {

        logAction($con, $userid, $username, "Invalid file type", "Failed", $current_datetime);

        echo json_encode([
            'Code'=>400,
            'status'=>'error',
            'message'=>'Only JPG, JPEG, PNG allowed'
        ]);
        exit;
    }

    /* ===================== UNIQUE FILE NAME ===================== */

    $imageName = $userid . "_" . time() . "." . $imageFileType;
    $targetFilePath = $uploadDir . $imageName;

    /* ===================== UPLOAD FILE ===================== */

    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {

        /* ===================== CALL PYTHON API ===================== */
        
        $getuserfaceencoding2 = mysqli_query($con, "SELECT FaceEncoding2 FROM user_login WHERE id = '$userid'");
        $getuserfaceencoding2Row = mysqli_fetch_assoc($getuserfaceencoding2);
    
        if ( !$getuserfaceencoding2Row || empty($getuserfaceencoding2Row['FaceEncoding2'])) 
        {
            
                logAction(
                    $con,
                    $userid,
                    $username,
                    "User FaceEncoding2 not found",
                    "Failed",
                    $current_datetime
                );
            
                echo json_encode([
                    'Code' => 404,
                    'status' => 'error',
                    'message' => 'Face encoding not found for this user'
                ]);
                exit;
            }

        $faceEncoding2 = $getuserfaceencoding2Row['FaceEncoding2'];
        

        $pythonApiUrl = "http://195.35.7.83:5050/detectface";

        $imageUrl = "https://sarsspl.com/" . PROJECT_NAME . "/api/uploads/detect_person/$userid/" . $imageName;
    
        $postData = [
            "emp_id" => $userid,
            "image_url"  => $imageUrl,
            "encoding" => $faceEncoding2
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $curlResponse = curl_exec($ch);

        /* ===================== CURL ERROR ===================== */

        if (curl_errno($ch)) {

            $curlError = curl_error($ch);

            logAction($con, $userid, $username, "Curl failed: ".$curlError, "Failed", $current_datetime);

            curl_close($ch);

            echo json_encode([
                'Code'=>500,
                'status'=>'error',
                'message' => 'Something went wrong! Please try again later.',
                'error'=>$curlError
            ]);
            exit;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode($curlResponse, true);
        
        if (
            isset($responseData['Code']) &&
            $responseData['Code'] == 200 &&
            isset($responseData['detected']) &&
            $responseData['detected'] == true
        ) {
        
            logAction(
                $con,
                $userid,
                $username,
                "Face detected successfully",
                "Success",
                $current_datetime
            );
        
            if (file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }
        
            echo json_encode([
                'Code' => 200,
                'status' => 'Success',
                'message' => 'Face detected successfully',
                'python_response' => $responseData
            ]);
        
        } else {
        
            $errorMsg = mysqli_real_escape_string(
                $con,
                substr($curlResponse, 0, 500)
            );
        
            logAction(
                $con,
                $userid,
                $username,
                "Face detection failed: ".$errorMsg,
                "Failed",
                $current_datetime
            );
        
            echo json_encode([
                'Code' => 250,
                'status' => 'error',
                'message' => 'Face not matched',
                'python_response' => $responseData
            ]);
        }

    } else {

        /* ===================== UPLOAD FAIL DEBUG ===================== */

        $uploadError = $file['error'];
        $errorMessage = getUploadErrorMessage($uploadError);

        $tmpCheck = file_exists($file['tmp_name']) ? 'TMP EXISTS' : 'TMP MISSING';

        $fullError = $errorMessage . " | " . $tmpCheck;

        logAction($con, $userid, $username, "Upload failed: ".$fullError, "Failed", $current_datetime);

        // Smart user-friendly messages for the frontend
        if ($uploadError === UPLOAD_ERR_PARTIAL) {
            $userFriendlyMessage = "Upload failed: Slow network connection / Poor internet connectivity. Please try again.";
        } elseif ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $userFriendlyMessage = "Upload failed: The image file size is too large.";
        } else {
            $userFriendlyMessage = "Upload failed: " . $errorMessage;
        }

        echo json_encode([
            'Code' => 250,
            'status' => 'error',
            'message' => $userFriendlyMessage,
            'python_response' => $fullError
        ]);
    }

} else {

    echo json_encode([
        'Code'=>450,
        'status'=>'error',
        'message'=>'Invalid request or missing user ID'
    ]);
}
?>