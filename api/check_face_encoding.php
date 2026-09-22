<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$userid = $_POST['userid'] ?? '';

if(empty($userid)){
    echo json_encode([
        "status"=>"error",
        "message"=>"User ID missing"
    ]);
    exit;
}

$query = mysqli_query($con, "SELECT profile_img, faceEncoding FROM user_login WHERE id='$userid'");

if(mysqli_num_rows($query)==0){
    echo json_encode([
        "status"=>"error",
        "message"=>"User not found"
    ]);
    exit;
}

$user = mysqli_fetch_assoc($query);

if(is_null($user['faceEncoding'])){

    // Delete image from server
    if(!empty($user['profile_img'])){

        $imagePath = $_SERVER['DOCUMENT_ROOT'] .
            str_replace("http://195.35.7.83:5048", "", $user['profile_img']);

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    // Remove image URL from database
    mysqli_query($con, "UPDATE user_login SET profile_img=NULL WHERE id='$userid'");

    echo json_encode([
        "status"=>"failed",
        "blob_created"=>false,
        "message"=>"Face encoding not generated. Please capture the photo again."
    ]);

}else{

    echo json_encode([
        "status"=>"success",
        "blob_created"=>true,
        "message"=>"Face encoding generated successfully."
    ]);
}
?>