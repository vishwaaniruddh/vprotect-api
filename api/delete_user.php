<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow the following methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow the following headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request, respond and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Content-Type: application/json');

$response = array();

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'Code' => 405,
        'msg' => 'Method Not Allowed',
    ];
    echo json_encode($response);
    exit();
}

// Get the POST data
$userid = isset($_POST['userid']) ? mysqli_real_escape_string($con, $_POST['userid']) : '';

if (empty($userid)) {
    $response = [
        'Code' => 400,
        'msg' => 'User ID is required',
    ];
    echo json_encode($response);
    exit();
}

$date = date('Y-m-d H:i:s');

// Delete query
// WARNING: This is a hard delete!

// $delete_query = "DELETE FROM user_login WHERE id = '$userid' AND user_role = 2";


// if (mysqli_query($con, $delete_query)) {
//     if (mysqli_affected_rows($con) > 0) {
//         $response = [
//             'Code' => 200,
//             'msg' => 'User deleted successfully',
//         ];
//     }
//     else {
//         $response = [
//             'Code' => 404,
//             'msg' => 'User not found or already deleted',
//         ];
//     }
// }
// else {
//     $response = [
//         'Code' => 500,
//         'msg' => 'Failed to delete user',
//         'error' => mysqli_error($con)
//     ];
// }


$delete_query = "UPDATE user_login
                 SET is_delete = NOW()
                 WHERE id = '$userid'
                 AND user_role = 2";

if (mysqli_query($con, $delete_query)) {

    if (mysqli_affected_rows($con) > 0) {

        $response = [
            'Code' => 200,
            'msg' => 'User deleted successfully'
        ];

    } else {

        $response = [
            'Code' => 404,
            'msg' => 'User not found or already deleted'
        ];
    }

} else {

    $response = [
        'Code' => 500,
        'msg' => mysqli_error($con)
    ];
}

echo json_encode($response);
?>
