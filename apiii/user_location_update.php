<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_user_set_panel'])) {
    // Default pagination values
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 25;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Count total records
    $countQuery = "SELECT COUNT(*) AS total FROM office_location";
    $countResult = mysqli_query($con, $countQuery);
    $totalRecords = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Main data query with LIMIT and OFFSET
    $selectQuery = "SELECT 
                            loc_tbl.id,
                            loc_tbl.user_id,
                            loc_tbl.location,
                            loc_tbl.status,
                            loc_tbl.is_aprove,
                            loc_tbl.last_id,
                            loc_tbl.remark,
                            user_tbl.name
                        FROM office_location AS loc_tbl
                        LEFT JOIN user_login AS user_tbl
                            ON loc_tbl.user_id = user_tbl.id
                        ORDER BY loc_tbl.id DESC
                    LIMIT $limit OFFSET $offset";


    $result = mysqli_query($con, $selectQuery);

    $leads = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $leads[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $leads,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Approve_request'])) {

    $current_id = $_POST['id'] ?? '';
    $user_id    = $_POST['user_id'] ?? '';

    mysqli_begin_transaction($con);

    try {

        // 1️⃣ Get old active location
        $oldQuery = mysqli_query($con, "
            SELECT id FROM office_location
            WHERE user_id = '$user_id' AND status = 1
            ORDER BY id DESC
            LIMIT 1
        ");

        $old_id = null;
        if ($oldQuery && mysqli_num_rows($oldQuery) > 0) {
            $row = mysqli_fetch_assoc($oldQuery);
            $old_id = $row['id'];

            // Deactivate old
            $updateOld = mysqli_query($con, "
                UPDATE office_location 
                SET status = 0 
                WHERE id = '$old_id'
            ");

            if (!$updateOld) {
                throw new Exception('Failed to deactivate old location');
            }
        }

        // 2️⃣ Approve new location
        $updateNew = mysqli_query($con, "
            UPDATE office_location 
            SET status = 1,
                is_aprove = 1,
                last_id = '$old_id'
            WHERE id = '$current_id'
        ");

        if (!$updateNew) {
            throw new Exception('Failed to approve new location');
        }

        // ✅ All good
        mysqli_commit($con);

        echo json_encode([
            'status' => 'success',
            'message' => 'Location approved successfully'
        ]);

    } catch (Exception $e) {

        // ❌ Something failed → rollback
        mysqli_rollback($con);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit();
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Rejected_request'])) {

    $current_id = $_POST['id'] ?? '';
    $user_id    = $_POST['user_id'] ?? '';
    $remark     = $_POST['remark'] ?? '';

    if ($current_id == '' || $remark == '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
        exit();
    }

    mysqli_begin_transaction($con);

    try {

        $rejectQuery = mysqli_query($con, "
            UPDATE office_location 
            SET 
                status = 0,
                is_aprove = 2,
                remark = '".mysqli_real_escape_string($con, $remark)."'
            WHERE id = '$current_id'
        ");

        if (!$rejectQuery) {
            throw new Exception('Failed to reject location request');
        }

        mysqli_commit($con);

        echo json_encode([
            'status' => 'success',
            'message' => 'Location rejected successfully'
        ]);

    } catch (Exception $e) {

        mysqli_rollback($con);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_for_location_update'])) {

    $user_id   = $_POST['user_id'] ?? '';
    $latitude  = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $location  = $_POST['location'] ?? '';
    $status    = 0;

    if ($user_id == '') {
        echo json_encode([
            'Code' => 450,
            'msg'  => 'Please provide user'
        ]);
        exit;
    }

    // 🔍 Check pending request
    $check_sql = mysqli_query(
        $con,
        "SELECT id 
         FROM office_location 
         WHERE user_id='".$user_id."' 
           AND status=0 
           AND is_aprove=0"
    );

    if (mysqli_num_rows($check_sql) > 0) {
        echo json_encode([
            'Code' => 201,
            'msg'  => 'Already Requested Please Wait For Approval'
        ]);
        exit;
    }

    if ($latitude == '' || $longitude == '') {
        echo json_encode([
            'Code' => 400,
            'msg'  => "User's Location Mandatory"
        ]);
        exit;
    }

    // ✅ Insert new request
    $insert = mysqli_query(
        $con,
        "INSERT INTO office_location 
         (user_id, latitude, longitude, location, created_at, status, is_aprove)
         VALUES
         ('".$user_id."','".$latitude."','".$longitude."','".$location."','".$created_at."','".$status."','0')"
    );

    if ($insert) {
        echo json_encode([
            'Code' => 200,
            'msg'  => 'Change Office Location Request Successfully Sent'
        ]);
    } else {
        echo json_encode([
            'Code' => 500,
            'msg'  => 'Error Inserting Data',
            'error'=> mysqli_error($con)
        ]);
    }

    exit;
}




// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_for_location_update'])){
    
// $data = $_POST;
    
// $user_id = isset($data['user_id']) ? $data['user_id'] : '';
// $latitude = isset($data['latitude']) ? $data['latitude'] : '';
// $longitude = isset($data['longitude']) ? $data['longitude'] : '';
// $location = isset($data['location']) ? $data['location'] : '' ;

// $status = 0;


// if($user_id!='') {
    
//     $check_request_sql = mysqli_query($con,"SELECT id FROM office_location WHERE user_id = '".$user_id."' AND status = 0 AND is_aprove = 0 ");
    
//     if($check_request_sql){
        
//         $response['Code']=201;
//         $response['msg']="Already Requested Please Wait For Approveal";
        
//     }else{
        
        
//         if($latitude=='' && $longitude==''){
//             $response['Code'] = 400; 
//             $response['msg'] = "User's Location Mandatory";
//         } else {
//             $insertsql = mysqli_query($con,"insert into office_location(user_id,latitude,longitude,location,created_at,status) values('".$user_id."','".$latitude."','".$longitude."','".$location."','".$created_at."','".$status."')");
//             if($insertsql){
                
//                 $response['Code']=200;
//                 $response['msg']="Change Office Location Request Successfully Send";
                
//             } else{
//                 $response['Code'] = 250;
//                 $response['msg'] = "Error Inserting Data!!";
//             }
//         }
        
//     }
    
        
// }else {
//         $response['Code']=450;
//         $response['msg']="Please provide user";
// }

// echo json_encode($response);
    
// }



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>