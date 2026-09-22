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
    
   // $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/FRUtopia/api/uploads/detect_person/$userid/";
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
            $response['Code'] = 200;
            $response["status"] = "Success";
            $response["message"] = $imgUrl;
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
