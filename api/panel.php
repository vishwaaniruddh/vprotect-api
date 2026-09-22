<?php

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_active_branch_codes'])) {
    $selectQuery = "SELECT id, branch_code, branch_name FROM branch_code WHERE status = 1 ORDER BY branch_name ASC";
    $result = mysqli_query($con, $selectQuery);

    $branches = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $branches
    ];

    echo json_encode($response);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_panel'])){
    $panel_id = isset($_POST['panel_id']) ? $_POST['panel_id'] : '';
    $no_of_user_allotment = isset($_POST['$no_of_user_allotment']) ? $_POST['$no_of_user_allotment'] : 2;
    $branch_code = isset($_POST['branch_code']) ? $_POST['branch_code'] : NULL;

    $selectQuery = "SELECT id FROM panel_list WHERE panel_id = '".$panel_id."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Record already exists.'
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO panel_list(panel_id,branch_code,no_of_user_allotment,created_at) VALUES ('".$panel_id."','".$branch_code."','".$no_of_user_allotment."','".$created_at."')";

        if (mysqli_query($con, $insertQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Record saved successfully'
        ];
        } else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save Record',
                'con_error' => mysqli_error($con)
            ];
        }
        
    }    
    
    echo json_encode($response);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_panel'])){
    $selectQuery = "SELECT * FROM panel_list ORDER BY id DESC";
    $result = mysqli_query($con, $selectQuery);

    $data = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    
    $response = [
        'Code' => 200,
        'data' => $data,
    ];
    
    echo json_encode($response);
    exit();
    
}


// if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_panel'])){

   

//     $id = $_POST['id'] ?? '';
//     $panel_id = $_POST['panel_id'] ?? '';
//     $no_of_user_allotment = $_POST['no_of_user_allotment'] ?? '';
//     $branch_code = isset($_POST['branch_code']) ? $_POST['branch_code'] : NULL;
    
    

//     if(!$id || !$panel_id){
//         echo json_encode(['Code'=>400,'msg'=>'Invalid input']);
//         exit();
//     }

//     $check_no_of_user = mysqli_query(
//         $con,
//         "SELECT COUNT(*) AS total 
//          FROM user_set_panel_data 
//          WHERE panel_id = '$panel_id' AND status = 1"
//     );

//     $countRow = mysqli_fetch_assoc($check_no_of_user);
//     $count_of_user = (int)$countRow['total'];

//     if($count_of_user <= $no_of_user_allotment){

//         $updateQuery = "
//             UPDATE panel_list 
//             SET panel_id='".$panel_id."',
//                 no_of_user_allotment='".$no_of_user_allotment."',
//                 branch_code='".$branch_code."',
//                 updated_at='".$created_at."'
//             WHERE id='".$id."'
//         ";

//         if(mysqli_query($con, $updateQuery)){
//             $response = ['Code'=>200,'msg'=>'Record updated successfully'];
//         }else{
//             $response = [
//                 'Code'=>500,
//                 'msg'=>'Failed to update record',
//                 'error'=>mysqli_error($con)
//             ];
//         }

//     }else{
//         $response = [
//             'Code'=>201,
//             'msg'=>"Cannot reduce user allotment below assigned users ($count_of_user)"
//         ];
//     }

//     echo json_encode($response);
//     exit();
// }

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_panel'])){

    $id = $_POST['id'] ?? '';
    $panel_id = $_POST['panel_id'] ?? '';
    $no_of_user_allotment = $_POST['no_of_user_allotment'] ?? '';
    $branch_code = $_POST['branch_code'] ?? NULL;
    $created_at = date('Y-m-d H:i:s');

    if(!$id || !$panel_id){
        echo json_encode(['Code'=>400,'msg'=>'Invalid input']);
        exit();
    }

    // ✅ Step 1: Check assigned users
    $check_no_of_user = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total 
         FROM user_set_panel_data 
         WHERE panel_id = '$panel_id' AND status = 1"
    );

    $countRow = mysqli_fetch_assoc($check_no_of_user);
    $count_of_user = (int)$countRow['total'];

    if($count_of_user <= $no_of_user_allotment){

        // ✅ Step 2: Check if branch already assigned to another panel
        if(!empty($branch_code)){
            $checkBranch = mysqli_query(
                $con,
                "SELECT id FROM panel_list 
                 WHERE branch_code = '$branch_code' AND id != '$id'"
            );

            if(mysqli_num_rows($checkBranch) > 0){
                // ✅ Remove branch from other panel
                mysqli_query(
                    $con,
                    "UPDATE panel_list 
                     SET branch_code = NULL 
                     WHERE branch_code = '$branch_code'"
                );
            }
        }

        // ✅ Step 3: Update current panel
        $updateQuery = "
            UPDATE panel_list 
            SET panel_id='".$panel_id."',
                no_of_user_allotment='".$no_of_user_allotment."',
                branch_code='".$branch_code."',
                updated_at='".$created_at."'
            WHERE id='".$id."'
        ";

        if(mysqli_query($con, $updateQuery)){
            $response = ['Code'=>200,'msg'=>'Record updated successfully'];
        }else{
            $response = [
                'Code'=>500,
                'msg'=>'Failed to update record',
                'error'=>mysqli_error($con)
            ];
        }

    }else{
        $response = [
            'Code'=>201,
            'msg'=>"Cannot reduce user allotment below assigned users ($count_of_user)"
        ];
    }

    echo json_encode($response);
    exit();
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>