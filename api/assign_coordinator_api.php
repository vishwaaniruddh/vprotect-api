<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_panels'])) {

//     // $sql = "SELECT id, panel_id, coordinator_id,status FROM panel_list WHERE status = 1";
//     $sql = "SELECT id, panel_id, coordinator_id,status FROM panel_list WHERE status = 1";
//     $res = mysqli_query($con, $sql);

//     if ($res) {

//         $data = [];

//         while ($row = mysqli_fetch_assoc($res)) {
//             $data[] = $row;
//         }

//         echo json_encode([
//             "Code" => 200,
//             "data" => $data
//         ]);

//     } else {

//         echo json_encode([
//             "Code" => 500,
//             "msg"  => "DB Error",
//             "error"=> mysqli_error($con)
//         ]);
//     }

//     exit;
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_panels'])) {

    // Fetch Panels
    $panelQuery = "SELECT id, panel_id, coordinator_id, status FROM panel_list WHERE status = 1";
    $panelRes = mysqli_query($con, $panelQuery);

    $panels = [];
    while ($row = mysqli_fetch_assoc($panelRes)) {
        $panels[] = $row;
    }

    // Fetch Coordinators (user_role = 7)
    $userQuery = "SELECT id, name FROM user_login WHERE user_role = 7 AND status = 1";
    $userRes = mysqli_query($con, $userQuery);

    $coordinators = [];
    while ($row = mysqli_fetch_assoc($userRes)) {
        $coordinators[] = $row;
    }

    echo json_encode([
        "Code" => 200,
        "panels" => $panels,
        "coordinators" => $coordinators
    ]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_coordinator'])) {

    $panel_id = $_POST['panel_id'];
    $coordinator_id = $_POST['coordinator_id'];

    // 🔁 STEP 1: Remove coordinator from any other panel first
    // $removeQuery = "UPDATE panel_list 
    //                 SET coordinator_id = NULL 
    //                 WHERE coordinator_id = '$coordinator_id'";

    // mysqli_query($con, $removeQuery);
    
    
    if($coordinator_id == ''){
        
        $assignQuery = "UPDATE panel_list 
                    SET coordinator_id = NULL 
                    WHERE id = '$panel_id'";
        $msg = "Coordinator Removed Successfully";
    }else{
        
        // 🔁 STEP 2: Assign to selected panel
        $assignQuery = "UPDATE panel_list 
                        SET coordinator_id = '$coordinator_id' 
                        WHERE id = '$panel_id'";
        $msg = "Coordinator Assigned Successfully";
    }


    if (mysqli_query($con, $assignQuery)) {

        echo json_encode([
            "Code" => 200,
             "msg" => $msg
        ]);

    } else {

        echo json_encode([
            "Code" => 500,
            "msg" => "DB Error",
            "error" => mysqli_error($con)
        ]);
    }

    exit;
}




// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_coordinator'])) {

//     $panel_id = $_POST['panel_id'];
//     $coordinator_id = $_POST['coordinator_id'];

//     $query = "UPDATE panel_list 
//               SET coordinator_id = '$coordinator_id' 
//               WHERE id = '$panel_id'";

//     if (mysqli_query($con, $query)) {

//         echo json_encode([
//             "Code" => 200,
//             "msg" => "Coordinator Assigned"
//         ]);

//     } else {

//         echo json_encode([
//             "Code" => 500,
//             "msg" => "DB Error"
//         ]);
//     }

//     exit;
// }








///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>