<?php
// require_once __DIR__ . '/env.php';
// include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');

include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$userid = isset($_POST['userid']) ? $_POST['userid'] : '';


    if (!isset($_POST['userid']) || $_POST['userid'] == '') {
        echo json_encode([
            'Code' => 400,
            'msg' => 'userid is required'
        ]);
        exit();
    }

    $userid = $_POST['userid'];

    // Step 1: Get site_id from site_alltoment
    $siteQuery = mysqli_query($con, "SELECT site_id 
                                     FROM site_alltoment 
                                     WHERE user_id='$userid' AND status='1'");

    if (mysqli_num_rows($siteQuery) == 0) {
        echo json_encode([
            'Code' => 404,
            'msg' => 'No site assigned to this user'
        ]);
        exit();
    }

    $siteData = mysqli_fetch_assoc($siteQuery);
    $site_id = $siteData['site_id'];

    // Step 2: Get mac data from mac_master
    $macQuery = mysqli_query($con, "SELECT door_name, mac_address, password 
                                    FROM mac_master 
                                    WHERE site_id='$site_id'");

    $doors = [];
    while ($row = mysqli_fetch_assoc($macQuery)) {
        $doors[] = $row;
    }

    echo json_encode([
        'Code' => 200,
        'site_id' => $site_id,
        'doors' => $doors
    ]);
    exit();
?>