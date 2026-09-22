<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

// require_once __DIR__ . '/env.php';
// include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');
include(__DIR__ . '/config/config.php');

// include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/aesCryptodecrypt.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();

// $userid = isset($data['userid']) ? $data['userid'] : '';

$alert_list = mysqli_query($con, "select * from alert_otp_request order by id DESC");

if (mysqli_num_rows($alert_list) > 0) {

    $details = [];

    while ($fetchall = mysqli_fetch_assoc($alert_list)) {

        $id = $fetchall['id'];
        $user_id = $fetchall['user_id'];
        $access_type = $fetchall['type_access'];
        $requested_at = $fetchall['requested_at'];
        $requested_status = $fetchall['requested_status'];
        $updated_at = $fetchall['updated_at'];
        $updated_by = $fetchall['updated_by'];
        $latitude = $fetchall['latitude'];
        $longitude = $fetchall['longitude'];
        $location = $fetchall['location'];

        // Get user details
        $usernamesql = mysqli_query($con,"select name,contact_no,email_id from user_login where id='$user_id'");

        $fetch_username = mysqli_fetch_assoc($usernamesql);

        $username = $fetch_username['name']?? '';
        $usercontact_no = $fetch_username['contact_no']?? '';
        $user_emailid = $fetch_username['email_id']?? '';


        // Get branch/site name
        $sitesql = mysqli_query(
            $con,
            "SELECT s.site_name
             FROM site_alltoment sa
             LEFT JOIN sites s ON s.id = sa.site_id
             WHERE sa.user_id = '$user_id'
             LIMIT 1"
        );

        $fetch_site = mysqli_fetch_assoc($sitesql);

        $branch = !empty($fetch_site['site_name'])
            ? $fetch_site['site_name']
            : '';


        $details[] = [
            'alertid' => $id,
            'userid' => $user_id,
            'usercontact_no' => $usercontact_no,
            'username' => $username,
            'user_emailid' => $user_emailid,
            'access_type' => $access_type,
            'requested_at' => $requested_at,
            'requested_status' => $requested_status,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => $location,
            'branch' => $branch
        ];
    }

    $response = [
        'Code' => 200,
        'msg' => 'Alert List fetched successfully',
        'data' => $details,
    ];

} else {

    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>