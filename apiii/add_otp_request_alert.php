<?php
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$datetime = date('Y-m-d H:i:s');
$date = date('Y-m-d');


$data = $_POST;

$response = array();

$user_id = isset($data['user_id']) ? $data['user_id'] : '';
$type_access = isset($data['type_access']) ? $data['type_access'] : '';
$latitude = isset($data['latitude']) ? $data['latitude'] : '';
$longitude = isset($data['longitude']) ? $data['longitude'] : '';
$location = isset($data['location']) ? $data['location'] : '' ;
$panel_id = isset($data['panel_id']) ? $data['panel_id'] : '' ;

$status = 1;


// // =====================
// // VALIDATION
// // =====================
// if(empty($user_id) || empty($type_access)){
//     echo json_encode([
//         'Code' => 400,
//         'msg' => 'User ID and Access Type required'
//     ]);
//     exit;
// }

// if(empty($latitude) || empty($longitude)){
//     echo json_encode([
//         'Code' => 400,
//         'msg' => "User's Location Mandatory"
//     ]);
//     exit;
// }


// // =====================
// // GET PANEL ID FROM DB (if not provided)
// // =====================
// if(empty($panel_id)){

//     $user_data = mysqli_query($con, "
//         SELECT panel_id 
//         FROM user_set_panel_data 
//         WHERE user_id = '$user_id' AND status = 1
//         LIMIT 1
//     ");

//     if(mysqli_num_rows($user_data) > 0){
//         $row = mysqli_fetch_assoc($user_data);
//         $panel_id = $row['panel_id']; // ✅ auto set
//     } else {
//         echo json_encode([
//             'Code' => 404,
//             'msg' => 'No Panel assigned to this user'
//         ]);
//         exit;
//     }

// } else {

//     // =====================
//     // VALIDATE PANEL_ID (if provided)
//     // =====================
//     $check_panel = mysqli_query($con, "
//         SELECT id 
//         FROM user_set_panel_data 
//         WHERE user_id = '$user_id' 
//         AND panel_id = '$panel_id'
//         AND status = 1
//     ");

//     if(mysqli_num_rows($check_panel) == 0){
//         echo json_encode([
//             'Code' => 403,
//             'msg' => 'Invalid Panel ID for this user'
//         ]);
//         exit;
//     }
// }



if($user_id!='' && $type_access!='') {
    
        if($latitude=='' && $longitude==''){
            $response['Code'] = 400; 
            $response['msg'] = "User's Location Mandatory";
        } else {
            // $insertsql = mysqli_query($con,"insert into alert_otp_request(user_id,type_access,requested_at,latitude,longitude,location) values('$user_id','$type_access','$datetime','$latitude','$longitude','$location')  ");
            
            $insertsql = mysqli_query($con,"insert into alert_otp_request(user_id,panel_id,type_access,requested_at,latitude,longitude,location) values('$user_id','$panel_id','$type_access','$datetime','$latitude','$longitude','$location')  ");
            if($insertsql){
                $last_id = mysqli_insert_id($con);
                $response['Code']=200;
                $response['msg']="User Access Request Saved Successfully";
                $response['alert_id'] = $last_id;
                
            } else{
                $response['Code'] = 250;
                $response['msg'] = "Error Inserting Data!!";
            }
        }
}else {
        $response['Code']=450;
        $response['msg']="Please provide user and access type";
}

echo json_encode($response);



?>