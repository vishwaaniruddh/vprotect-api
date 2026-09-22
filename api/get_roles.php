<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = array();

$sql = "SELECT id, role FROM role";
$result = mysqli_query($con, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $response['Code'] = 200;
        $response['data'] = $data;
    }
    else {
        $response['Code'] = 404;
        $response['msg'] = "No roles found";
    }
}
else {
    $response['Code'] = 500;
    $response['msg'] = "Database query failed";
    $response['con_error'] = mysqli_error($con);
}

echo json_encode($response);
exit();
?>
