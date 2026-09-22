<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$datetime = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');


$response = array();

$data = $_POST;

$userid = isset($data['userid']) ? $data['userid'] : '' ;
$panelid = isset($data['panelid']) ? $data['panelid'] : '' ;
$created_by = isset($data['created_by']) ? $data['created_by'] : '' ;

$_set_count = 0;
$panel_set_to_user_count = 0;

$user_panel_data = mysqli_query($con, "select * from user_set_panel_data where panel_id='".$panelid."'");
if (mysqli_num_rows($user_panel_data) > 0) {
    while ($fetchall = mysqli_fetch_assoc($user_panel_data)) {
        $user_id = $fetchall['user_id'];
        $status = $fetchall['status'];
        
        if($user_id==$userid){
            $_set_count = 1;
        }else{
            $panel_set_to_user_count = $panel_set_to_user_count + 1;
        }
    }
    
    if($_set_count==0){
        if($panel_set_to_user_count==2){
            $response = [
                'Code' => 201,
                'msg' => 'Panel Already Set to 2 different User.So unable to set',
            ];
        }else{
            $insertsql = mysqli_query($con,"insert into user_set_panel_data(user_id,panel_id,created_at,updated_at,created_by,updated_by) values('$userid','$panelid','$datetime','$datetime','$created_by','$created_by')  ");
             $response = [
                'Code' => 200,
                'msg' => "Panel Set Successfully!!",
            ];
        }
    }else{
        $response = [
            'Code' => 202,
            'msg' => 'Panel Already Set to this User.So unable to set again',
            
        ];
    }
    
    
} else {
     $insertsql = mysqli_query($con,"insert into user_set_panel_data(user_id,panel_id,created_at,updated_at,created_by,updated_by) values('$userid','$panelid','$datetime','$datetime','$created_by','$created_by')  ");
    $response = [
        'Code' => 200,
        'msg' => "Panel Set Successfully!!",
    ];
}

echo json_encode($response);
?>