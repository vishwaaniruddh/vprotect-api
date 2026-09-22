<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$response = array();
$data = $_POST;

$userid = isset($data['userid']) ? $data['userid'] : '';


if ($userid != '') {
    // Escape the input to avoid SQL injection
    $userid = mysqli_real_escape_string($con, $userid);
    // echo 1; die;

    $checkprofilestatus = mysqli_query($con, "SELECT * FROM user_login WHERE id = '$userid'");

    if (mysqli_num_rows($checkprofilestatus) > 0) {
        // echo 1; die;
        $fetchuserdata = mysqli_fetch_assoc($checkprofilestatus);
        $profile_img = $fetchuserdata['profile_img'];

        if (!empty($profile_img)) {
            $response = [
                'Code' => 200,
                'status' => 'User Profile Image Exist'
            ];
        } else {
            $response = [
                'Code' => 250,
                'status' => 'User Profile Image Not Found'
            ];
        }

    } else {
        $response = [
            'Code' => 401,
            'status' => 'User Not Found'
        ];
    }
} else {
    $response = [
        'Code' => 400,
        'status' => 'UserId Not Found'
    ];
}

echo json_encode($response);
?>
