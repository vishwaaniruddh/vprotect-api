<?php

include(__DIR__ . '/config/config.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

try {

    // Database connection check
    if (!$con) {
        throw new Exception('Database connection failed.');
    }

    $selectQuery = "
        SELECT id, request_category
        FROM otp_request_category
        WHERE status = 1
        ORDER BY id ASC
    ";

    $result = mysqli_query($con, $selectQuery);

    // Query execution check
    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    // No data found
    if (empty($data)) {

        echo json_encode([
            'Code' => 201,
            'msg'  => 'No data found.',
            'data' => []
        ]);

        exit();
    }

    // Success response
    echo json_encode([
        'Code' => 200,
        'msg'  => 'Data fetched successfully.',
        'data' => $data
    ]);

} catch (Exception $e) {

    echo json_encode([
        'Code'  => 500,
        'msg'   => 'Something went wrong.',
        'error' => $e->getMessage(),
        'data'  => []
    ]);
}

exit();

?>