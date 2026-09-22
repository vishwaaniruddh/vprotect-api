<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
$response = [];

if ($userid && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['detect_person_img'])) {

    // --- GET USER NAME ---
    $getusername = mysqli_query($con, "SELECT name FROM user_login WHERE id = '$userid'");
    $username = mysqli_fetch_assoc($getusername)['name'];
    $newname = str_replace(' ', '', $username);

    // --- FILE UPLOAD SETUP ---
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/FRUtopia/api/uploads/detect_person/$userid/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $detect_person_img = $_FILES['detect_person_img'];
    $imageFileType = strtolower(pathinfo($detect_person_img["name"], PATHINFO_EXTENSION));
    $imageName = $userid . "_" . $newname . "." . $imageFileType;
    $targetFilePath = $uploadDir . $imageName;

    $allowedTypes = array("jpg", "jpeg", "png");

    if (!in_array($imageFileType, $allowedTypes)) {
        $response = [
            "Code" => 400,
            "status" => "error",
            "message" => "Invalid file type. Only JPG, JPEG, PNG allowed."
        ];
        echo json_encode($response);
        exit;
    }

    // --- MOVE FILE ---
    if (move_uploaded_file($detect_person_img["tmp_name"], $targetFilePath)) {

        // Public Image URL
        $imgUrl = "https://sarsspl.com/FRUtopia/api/uploads/detect_person/$userid/$imageName";

        // --- CALL PYTHON API ---
        $pythonApiUrl = "http://195.35.7.83:5045/detectface?image_url=" . urlencode($imgUrl);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $curlResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $pythonData = json_decode($curlResponse, true);

        // --- CHECK PYTHON API RESULT ---
        if ($httpCode == 200 && isset($pythonData['Code']) && $pythonData['Code'] == 200) {

            $response['Code'] = 200;
            $response['status'] = "Success";
            $response['message'] = $pythonData['msg'] ?? "Face detected successfully";
            $response['python_api_url'] = $pythonApiUrl;
            $response['python_raw_response'] = $curlResponse;

            unlink($targetFilePath);

        } else {

            $response['Code'] = 250;
            $response['status'] = "error";
            $response['message'] = $pythonData['msg'] ?? "Face detection failed.";
            $response['python_api_url'] = $pythonApiUrl;
            $response['python_raw_response'] = $curlResponse;
            $response['python_http_code'] = $httpCode;

            unlink($targetFilePath);
        }

    } else {

        $response = [
            "Code" => 410,
            "status" => "error",
            "message" => "Image upload failed."
        ];
    }

} else {

    $response = [
        'Code' => 450,
        'status' => 'error',
        'message' => "User ID is missing or invalid request!",
    ];
}

echo json_encode($response);
?>
