<?php
// include($_SERVER['DOCUMENT_ROOT'].'/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_otp_category'])){
    $request_category = isset($_POST['request_category']) ? $_POST['request_category'] : '';

    $selectQuery = "SELECT id FROM otp_request_category WHERE request_category = '".$request_category."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Record already exists.'
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO otp_request_category(request_category, created_at) VALUES ('".$request_category."','".$created_at."')";

        if (mysqli_query($con, $insertQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Record saved successfully'
        ];
        } else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save record',
                'con_error' => mysqli_error($con)
            ];
        }
        
    }    
    
    echo json_encode($response);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_otp_category'])){
    $selectQuery = "SELECT * FROM otp_request_category ORDER BY id DESC";
    $result = mysqli_query($con, $selectQuery);

    $leads = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $leads[] = $row;
        }
    }
    
    $response = [
        'Code' => 200,
        'data' => $leads,
    ];
    
    echo json_encode($response);
    exit();
    
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_otp_category'])){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $request_category = isset($_POST['request_category']) ? $_POST['request_category'] : '';

    $updateQuery = "UPDATE otp_request_category SET request_category = '".$request_category."' WHERE id = '".$id."' ";

    if (mysqli_query($con, $updateQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Record updated successfully'
        ];
    } else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to update record',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
    
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeStatus'])){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    $updateQuery = "UPDATE otp_request_category SET status = '".$status."' WHERE id = '".$id."' ";

    if (mysqli_query($con, $updateQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Record updated successfully'
        ];
    } else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to update record',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>