<?php
include($_SERVER['DOCUMENT_ROOT'].'/FRUtopia/api/config/config.php');
// include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])){
    $question = isset($_POST['question']) ? $_POST['question'] : '';

    $selectQuery = "SELECT id FROM user_security_question WHERE question = '".$question."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Record already exists.'
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO user_security_question(question, created_at) VALUES ('".$question."','".$created_at."')";

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

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_question'])){
    $selectQuery = "SELECT * FROM user_security_question ORDER BY id DESC";
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

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $question = isset($_POST['question']) ? $_POST['question'] : '';

    $updateQuery = "UPDATE user_security_question SET question = '".$question."' WHERE id = '".$id."' ";

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

    $updateQuery = "UPDATE user_security_question SET status = '".$status."' WHERE id = '".$id."' ";

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