<?php
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branch'])){
    
    $user_id = $_POST['user_id'];
    $branch_name = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';

    $selectQuery = "SELECT id FROM branch_details WHERE branch_name = '".$branch_name."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Branch already exists.'
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO branch_details(branch_name,contact, email, status, created_at, created_by) VALUES ('".$branch_name."','".$contact."','".$email."','1','".$created_at."','".$user_id."')";

        if (mysqli_query($con, $insertQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Data saved successfully'
        ];
        } else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save details',
                'con_error' => mysqli_error($con)
            ];
        }
        
    }    
    
    echo json_encode($response);
    exit();
}



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_branch'])){
    $selectQuery = "SELECT * FROM branch_details WHERE status = 1 ORDER BY id DESC";
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



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $branch_name = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
    
    $updateQuery = "UPDATE branch_details SET branch_name = '".$branch_name."' ,contact='".$contact."',email='".$email."',updated_at='".$created_at."' WHERE id = '".$id."' ";

    if (mysqli_query($con, $updateQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Branch updated successfully'
        ];
    } else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to update branch',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
    
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])){

    $id = $_POST['id'];

    $deleteQuery = "DELETE FROM branch_details WHERE id = '$id'";
    $result = mysqli_query($con, $deleteQuery);

    if($result){
        $response = [
            'Code' => 200,
            'message' => 'Deleted Successfully'
        ];
    } else {
        $response = [
            'Code' => 400,
            'message' => 'Delete Failed'
        ];
    }

    echo json_encode($response);
    exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>