<?php

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

// include($_SERVER['DOCUMENT_ROOT'].'/vprotectFR/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$datetime = date('Y-m-d H:i:s');

$data = $_POST;
$response = array();

// USER DATA
$user_name = $data['user_name'] ?? '';
$mobile_no = $data['mobile_no'] ?? '';
$password   = $data['password'] ?? '';
$email      = $data['email_id'] ?? '';
$user_role  = $data['user_role'] ?? '';
$mobileName = $data['MobileName'] ?? '';   // ✅ MobileName


// LOCATION DATA ✅
$latitude  = $data['latitude'] ?? '';
$longitude = $data['longitude'] ?? '';
$location  = $data['location'] ?? '';

$status = 1;

// 🔥 VALIDATION
if(strlen($mobile_no) != 10 || empty($password)){
    $response['Code']=450;
    $response['msg']="Invalid Mobile Number or Password";
    echo json_encode($response);
    exit;
}

if($latitude == '' || $longitude == ''){
    $response['Code']=400;
    $response['msg']="Location is required";
    echo json_encode($response);
    exit;
}

// CHECK EXISTING USER
$checksql = mysqli_query($con,"SELECT contact_no FROM user_login WHERE contact_no='$mobile_no'");

if(mysqli_num_rows($checksql) > 0){
    $response['Code']=400;
    $response['msg']="User already exists";
    echo json_encode($response);
    exit;
}

// 🔥 START TRANSACTION
mysqli_begin_transaction($con);

try {

    // ✅ INSERT USER
    $userInsert = mysqli_query($con,"INSERT INTO user_login(name,MobileName,contact_no,email_id,password,status,user_role,created_at)
    VALUES('$user_name', '$mobileName','$mobile_no','$email','$password','$status','$user_role','$datetime')");

    if(!$userInsert){
        throw new Exception("User insert failed");
    }

    $user_id = mysqli_insert_id($con);

    // ✅ INSERT LOCATION
    $locationInsert = mysqli_query($con,"INSERT INTO office_location(user_id,latitude,longitude,location,created_at,status)
    VALUES('$user_id','$latitude','$longitude','$location','$datetime','$status')");

    if(!$locationInsert){
        throw new Exception("Location insert failed");
    }

    // ✅ COMMIT (Both success)
    mysqli_commit($con);

    $response['Code'] = 200;
    $response['msg']  = "User + Location saved successfully";
    $response['id']   = $user_id;

} catch (Exception $e) {

    // ❌ ROLLBACK (If anything fails)
    mysqli_rollback($con);

    $response['Code'] = 500;
    $response['msg']  = $e->getMessage();
}

echo json_encode($response);
?>