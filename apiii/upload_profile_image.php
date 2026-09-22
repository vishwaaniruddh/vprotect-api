<?php
include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();
$data = $_POST;

$userid = isset($data['userid']) ? $data['userid'] : '';

if ($userid) {
    
    $getusername = mysqli_query($con, "SELECT name FROM user_login WHERE id = '$userid'");
    $username = mysqli_fetch_assoc($getusername)['name'];
    $newname = str_replace(' ', '', $username); // स्पेस हटाकर नाम तैयार करना
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $profile_img = isset($_FILES['profile_img']) ? $_FILES['profile_img'] : null;
        $image_url = "";
       
        if ($profile_img) {
            $uploadDir = "uploads/$userid/"; 
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageFileType = strtolower(pathinfo($profile_img["name"], PATHINFO_EXTENSION)); // फ़ाइल एक्सटेंशन लेना
            $imageName = $userid . "_" . $newname . "." . $imageFileType; // नई फ़ाइल का नाम: 4_RajeshBiswas.ext
            $targetFilePath = $uploadDir . $imageName;

            // Allowed file types
            $allowedTypes = array("jpg", "jpeg", "png", "gif");

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($profile_img["tmp_name"], $targetFilePath)) {
                    $image_url = "https://sarsspl.com/FRUtopia/api/" . $targetFilePath;
                    
                    $updateuser = mysqli_query($con, "UPDATE user_login SET profile_img ='$image_url', updated_at = '$created_at' WHERE id = '$userid'");
                    if ($updateuser) {
                        $response["status"] = "Success";
                        $response["message"] = "Image uploaded successfully.";
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    $response["status"] = "error";
                    $response["message"] = "Image upload failed.";
                    echo json_encode($response);
                    exit();
                }
            } else {
                $response["status"] = "error";
                $response["message"] = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed.";
                echo json_encode($response);
                exit();
            }
        }
       
    } else {
        $response = [
            'Code' => 400,
            'msg' => "Invalid request method!",
        ];
    }
} else {
    $response = [
        'Code' => 400,
        'msg' => "User ID is missing!",
    ];
}

echo json_encode($response);
?>
