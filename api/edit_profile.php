<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Ensure the request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'Code' => 405,
        'status' => "error",
        'message' => "Invalid request method! Only POST allowed."
    ]);
    exit();
}

$created_at = date('Y-m-d H:i:s');

// Validate if userid is provided
if (empty($_POST['userid'])) {
    echo json_encode([
        'Code' => 401,
        'status' => "error",
        'message' => "User ID is missing!"
    ]);
    exit();
}

$userid = $_POST['userid'];
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$emailid = isset($_POST['email_id']) ? trim($_POST['email_id']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$profile_img = isset($_FILES['profile_img']) ? $_FILES['profile_img'] : null;
$image_url = "";

// Fetch existing user details
$getUser = mysqli_query($con, "SELECT name, email_id, password FROM user_login WHERE id = '$userid'");
if (!$getUser || mysqli_num_rows($getUser) === 0) {
    echo json_encode([
        'Code' => 404,
        'status' => "error",
        'message' => "User not found."
    ]);
    exit();
}

$user = mysqli_fetch_assoc($getUser);
$_name = $name !== '' ? $name : $user['name'];
$_email = $emailid !== '' ? $emailid : $user['email_id'];
$_pwd = $password !== '' ? $password : $user['password'];

// If image is uploaded
if ($profile_img && $profile_img['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/$userid/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($profile_img["name"], PATHINFO_EXTENSION));
    $allowedTypes = array("jpg", "jpeg", "png");

    if (!in_array($imageFileType, $allowedTypes)) {
        echo json_encode([
            "Code" => 400,
            "status" => "error",
            "message" => "Invalid file type. Only JPG, JPEG, PNG allowed."
        ]);
        exit();
    }

    $newname = str_replace(' ', '', $_name);
    $imageName = $userid . "_" . $newname . "." . $imageFileType;
    $targetFilePath = $uploadDir . $imageName;

    if (!move_uploaded_file($profile_img["tmp_name"], $targetFilePath)) {
        echo json_encode([
            "Code" => 500,
            "status" => "error",
            "message" => "Image upload failed."
        ]);
        exit();
    }

    $image_url = "https://qr.zimaxxtech.com/FRUtopia/api/" . $targetFilePath;

    // Update with image
    $query = "UPDATE user_login SET profile_img = '$image_url', name = '$_name', password = '$_pwd', email_id = '$_email', updated_at = '$created_at' WHERE id = '$userid'";
} else {
    // Update without image
    $query = "UPDATE user_login SET name = '$_name', password = '$_pwd', email_id = '$_email', updated_at = '$created_at' WHERE id = '$userid'";
}

$update = mysqli_query($con, $query);

if ($update) {
    echo json_encode([
        "Code" => 200,
        "status" => "Success",
        "message" => "Profile updated successfully."
    ]);
} else {
    echo json_encode([
        "Code" => 501,
        "status" => "error",
        "message" => "Database update failed."
    ]);
}
?>
