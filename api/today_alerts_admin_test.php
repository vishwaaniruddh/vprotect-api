<?php

include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');

$response = [];

try {

    if (!$con) {
        throw new Exception("Database connection failed");
    }

    $query = "
        SELECT
            a.id,
            a.user_id,
            a.type_access,
            a.requested_at,
            a.requested_status,
            a.remark,
            a.updated_at,
            a.updated_by,
            a.latitude,
            a.longitude,
            a.location,
            a.panel_id,

            p.branch_code,

            b.branch_name AS branch_addr,

            u.name AS username,
            u.contact_no AS usercontact_no,
            u.email_id AS user_emailid

        FROM alert_otp_request a

        LEFT JOIN panel_list p
            ON a.panel_id = p.panel_id

        LEFT JOIN branch_code b
            ON b.branch_code = p.branch_code

        LEFT JOIN user_login u
            ON u.id = a.user_id

        WHERE DATE(a.requested_at) = CURDATE()

        ORDER BY a.id DESC
    ";

    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $details = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $details[] = [

            'alertid' => $row['id'],

            'userid' => $row['user_id'],

            'branch_code' => $row['branch_code'] ?? '',

            'panel_id' => $row['panel_id'],

            'usercontact_no' => $row['usercontact_no'] ?? '',

            'username' => $row['username'] ?? 'User Not Found',

            'user_emailid' => $row['user_emailid'] ?? '',

            'access_type' => $row['type_access'],

            'requested_at' => $row['requested_at'],

            'requested_status' => $row['requested_status'],

            'remark' => $row['remark'],

            'updated_at' => $row['updated_at'],

            'latitude' => $row['latitude'],

            'longitude' => $row['longitude'],

            'location' => $row['location'],

            'branch_addr' => $row['branch_addr'] ?? ''

        ];
    }

    if (count($details) > 0) {

        $response = [
            'Code' => 200,
            'msg' => 'Alert List fetched successfully',
            'count' => count($details),
            'data' => $details
        ];

    } else {

        $response = [
            'Code' => 204,
            'msg' => 'No Data Found',
            'data' => []
        ];
    }

} catch (Exception $e) {

    $response = [
        'Code' => 500,
        'msg' => 'Server Error',
        'error' => $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>