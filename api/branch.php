<?php
// include($_SERVER['DOCUMENT_ROOT'].'/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');


header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branch'])){
    $branch_name = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';

    $selectQuery = "SELECT id FROM branch_details WHERE branch_name = '".$branch_name."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Branch already exists.'
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO branch_details(branch_name, created_at) VALUES ('".$branch_name."','".$created_at."')";

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
    $selectQuery = "SELECT * FROM branch_details ORDER BY id DESC";
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

    $updateQuery = "UPDATE branch_details SET branch_name = '".$branch_name."' WHERE id = '".$id."' ";

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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>