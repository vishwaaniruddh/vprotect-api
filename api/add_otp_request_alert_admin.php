<?php
include(__DIR__ . '/config/config.php');
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

$status = 1;



if($user_id!='' && $type_access!='') {
    
        if($latitude=='' && $longitude==''){
            $response['Code'] = 400; 
            $response['msg'] = "User's Location Mandatory";
        } else {
            $insertsql = mysqli_query($con,"insert into alert_otp_request(user_id,type_access,requested_at,latitude,longitude,location) values('$user_id','$type_access','$datetime','$latitude','$longitude','$location')  ");
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