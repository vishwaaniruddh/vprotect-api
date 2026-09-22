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
    
    $getusername = mysqli_query($con, "SELECT name FROM user_login WHERE id = '$userid'");
    $username = mysqli_fetch_assoc($getusername)['name'];
    $newname = str_replace(' ', '', $username);
    
    $detect_person_img = $_FILES['detect_person_img'];
    $uploadDir = "uploads/detect_person/$userid/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($detect_person_img["name"], PATHINFO_EXTENSION));
    $imageName = $userid . "_" . $newname .  "." . $imageFileType;
    $targetFilePath = $uploadDir . $imageName;

    $allowedTypes = array("jpg", "jpeg", "png");
    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($detect_person_img["tmp_name"], $targetFilePath)) {
            // sleep(5);
            // $image_url = "https://sarsspl.com/SAR_payroll/api/" . $targetFilePath;
            
            // $imgUrl =  str_replace('https://sarsspl.com','https://qr.zimaxxtech.com',$image_url);
            
            $imgUrl = "https://sarsspl.com/FRUtopia/api/" . $targetFilePath;
            
            // Call Python API for face detection asynchronously
            $pythonApiUrl = "http://195.35.7.83:5045/detectface?image_url=" .$imgUrl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Timeout to prevent long waiting
            $curlResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $response['Code'] = 200;
                $response["status"] = "Success";
                $response["message"] = "Face detected successfully, image deleted.";
                unlink($targetFilePath); // Delete the image after successful detection
            } else {
                $response['Code'] = 250;
                $response["status"] = "error";
                $response["message"] = "Face detection failed.";
                $response['link'] = $pythonApiUrl;
                unlink($targetFilePath);
                
            }
        } else {
            $response['Code'] = 410;
            $response["status"] = "error";
            $response["message"] = "Image upload failed.";
        }
    } else {
        $response['Code'] = 400;
        $response["status"] = "error";
        $response["message"] = "Invalid file type. Only JPG, JPEG, PNG allowed.";
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
