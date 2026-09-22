<?php

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

try {

    if (!$con) {
        throw new Exception('Database connection failed.');
    }

    $filter = isset($_POST['filter'])
        ? strtolower(trim($_POST['filter']))
        : 'day';

    switch ($filter) {

        case 'week':

            $date_condition = "YEARWEEK(requested_at,1)=YEARWEEK(CURDATE(),1)";
            break;

        case 'month':

            $date_condition = "
                MONTH(requested_at)=MONTH(CURDATE())
                AND YEAR(requested_at)=YEAR(CURDATE())
            ";
            break;

        case 'day':
        default:

            $date_condition = "DATE(requested_at)=CURDATE()";
            break;
    }

    $query = "
        SELECT

            COUNT(*) AS total_requests,

            SUM(
                CASE
                    WHEN requested_status=0 THEN 1
                    ELSE 0
                END
            ) AS active_requests,

            SUM(
                CASE
                    WHEN requested_status IN (1,2) THEN 1
                    ELSE 0
                END
            ) AS accessed_requests,

            SUM(
                CASE
                    WHEN requested_status=0 THEN 1
                    ELSE 0
                END
            ) AS pending_requests

        FROM alert_otp_request

        WHERE $date_condition
    ";

    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $row = mysqli_fetch_assoc($result);

    echo json_encode([
        'Code' => 200,
        'msg' => 'Dashboard data fetched successfully.',
        'filter' => $filter,
        'data' => [
            'total_otp_requests_received' => (int)$row['total_requests'],
            'total_realtime_active_requests' => (int)$row['active_requests'],
            'total_accessed_requests' => (int)$row['accessed_requests'],
            'total_pending_requests' => (int)$row['pending_requests']
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        'Code' => 500,
        'msg' => $e->getMessage(),
        'data' => new stdClass()
    ]);
}

exit();

?>