<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// require_once __DIR__ . '/env.php';

include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_site'])){ 

    $site_name = isset($_POST['site_name']) ? $_POST['site_name'] : '';
    $no_of_doors = isset($_POST['no_of_doors']) ? $_POST['no_of_doors'] : 0;
    $doors = isset($_POST['doors']) ? json_decode($_POST['doors'], true) : [];

    // Check site already exists
    $selectQuery = "SELECT id FROM sites WHERE site_name = '".$site_name."' ";
    
    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Site already exists.'
        ];
        echo json_encode($response);
        exit();
    } 
    else {

        // Insert into sites table
        $insertSite = "INSERT INTO sites(site_name) 
                       VALUES ('".$site_name."')";

        if (mysqli_query($con, $insertSite)) {

            // Get last inserted site id
            $site_id = mysqli_insert_id($con);

            // Insert doors data into mac_master
            if(!empty($doors)){
                foreach($doors as $door){

                    $mac = $door['mac_id'];
                    $password = $door['password'];
                    $door_name = $door['door_name'];

                    $insertDoor = "INSERT INTO mac_master(site_id, mac_address, password,door_name) 
                                   VALUES ('".$site_id."', '".$mac."', '".$password."','".$door_name."')";
                    
                    mysqli_query($con, $insertDoor);
                }
            }

            $response = [
                'Code' => 200,
                'msg' => 'Site and Doors saved successfully'
            ];

        } else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save site',
                'con_error' => mysqli_error($con)
            ];
        }
    }    
    
    echo json_encode($response);
    exit();
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_sites'])){
    $selectQuery = "SELECT * FROM sites ORDER BY id DESC";
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


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_mac_by_site'])){
    
    $site_id = isset($_POST['site_id']) ? $_POST['site_id'] : '';

    if($site_id == ''){
        echo json_encode([
            "Code" => 400,
            "msg" => "site_id is required"
        ]);
        exit();
    }

    $selectQuery = "SELECT * FROM mac_master WHERE site_id ='".$site_id."'";
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

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_allotment'])){

    if (!isset($_POST['site_id']) || !isset($_POST['user_id'])) {
        echo json_encode([
            'Code' => 400,
            'msg' => 'site_id and user_id required'
        ]);
        exit();
    }

    $site_id = $_POST['site_id'];
    $user_id = $_POST['user_id'];

    // Check already exists
    $checkQuery = "SELECT id FROM site_alltoment 
                   WHERE site_id='$site_id' AND user_id='$user_id'";

    $checkResult = mysqli_query($con, $checkQuery);

    if(mysqli_num_rows($checkResult) > 0){
        echo json_encode([
            'Code' => 409,
            'msg' => 'Site already assigned to this user'
        ]);
        exit();
    }

    // Insert
    $insertQuery = "INSERT INTO site_alltoment(user_id, site_id, status) 
                    VALUES('$user_id', '$site_id', '1')";

    if(mysqli_query($con, $insertQuery)){
        echo json_encode([
            'Code' => 200,
            'msg' => 'Site assigned successfully'
        ]);
    } else {
        echo json_encode([
            'Code' => 500,
            'msg' => 'Failed to assign site',
            'error' => mysqli_error($con)
        ]);
    }

    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_site'])){

    $site_id = $_POST['site_id'];
    $doors = json_decode($_POST['doors'], true);

    if(empty($site_id) || empty($doors)){
        echo json_encode([
            "Code" => 400,
            "msg" => "Invalid data"
        ]);
        exit();
    }

    foreach($doors as $door){

        $door_name = $door['door_name'];
        $mac_id = $door['mac_id'];
        $password = $door['password'];

        $updateQuery = "UPDATE mac_master 
                        SET mac_address='".$mac_id."', password='".$password."'
                        WHERE site_id='".$site_id."' AND door_name='".$door_name."'";

        mysqli_query($con, $updateQuery);
    }

    echo json_encode([
        "Code" => 200,
        "msg" => "Updated successfully"
    ]);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_site'])){

    $site_id = $_POST['site_id'];

    if(empty($site_id)){
        echo json_encode([
            "Code" => 400,
            "msg" => "site_id required"
        ]);
        exit();
    }

    // First delete doors
    mysqli_query($con, "DELETE FROM mac_master WHERE site_id='".$site_id."'");

    // Then delete site
    mysqli_query($con, "DELETE FROM sites WHERE id='".$site_id."'");

    echo json_encode([
        "Code" => 200,
        "msg" => "Site deleted successfully"
    ]);
    exit();
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>