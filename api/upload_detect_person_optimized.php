<?php
include(__DIR__ . '/config/config.php');
header('Content-Type: application/json');

$current_datetime = date('Y-m-d H:i:s');

$userid = $_POST['userid'] ?? '';

if (!$userid || !isset($_FILES['detect_person_img'])) {
    echo json_encode([
        'Code' => 400,
        'message' => 'Invalid request'
    ]);
    exit;
}

// 🔹 Get user
$user = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM user_login WHERE id='$userid'"));
$username = $user['name'] ?? 'Unknown';

// 🔹 Upload Path
$uploadDir = __DIR__ . "/uploads/detect_person/$userid/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// 🔹 File Info
$file = $_FILES['detect_person_img'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['jpg','jpeg','png'])) {
    echo json_encode(['Code'=>400,'message'=>'Invalid file']);
    exit;
}

$imageName = $userid . "_" . time() . "." . $ext;
$targetPath = $uploadDir . $imageName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['Code'=>500,'message'=>'Upload failed']);
    exit;
}

// 🔹 Insert log (pending)
mysqli_query($con, "INSERT INTO daily_action_log 
(user_id,user_name,action,action_status,status,retry_count,created_at) 
VALUES ('$userid','$username','Face detection started','Pending','pending',0,'$current_datetime')");

$log_id = mysqli_insert_id($con);

// 🔥 CALL PYTHON API
$response = callPythonAPI($targetPath, $userid);

// 🔥 HANDLE RESPONSE
if ($response['success']) {

    unlink($targetPath); // delete image

    mysqli_query($con, "UPDATE daily_action_log 
        SET status='success', action_status='Success' 
        WHERE id='$log_id'");

    echo json_encode([
        'Code'=>200,
        'message'=>'Face detected successfully'
    ]);

} else {

    mysqli_query($con, "UPDATE daily_action_log 
        SET status='failed', action_status='Failed' 
        WHERE id='$log_id'");

    echo json_encode([
        'Code'=>500,
        'message'=>'Detection failed',
        'error'=>$response['error']
    ]);
}


// 🔥 FUNCTION
function callPythonAPI($filePath, $userid) {

    $url = "http://192.168.10.25:5045/detectface";

    $cfile = new CURLFile($filePath);

    $post = [
        "emp_id" => $userid,
        "image" => $cfile
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($code == 200) {
        return ['success'=>true];
    } else {
        return ['success'=>false, 'error'=>$err ?: $result];
    }
}
?>