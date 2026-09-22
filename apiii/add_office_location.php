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
$latitude = isset($data['latitude']) ? $data['latitude'] : '';
$longitude = isset($data['longitude']) ? $data['longitude'] : '';
$location = isset($data['location']) ? $data['location'] : '' ;

$status = 1;


if($user_id!='') {
    
        if($latitude=='' && $longitude==''){
            $response['Code'] = 400; 
            $response['msg'] = "User's Location Mandatory";
        } else {
            $insertsql = mysqli_query($con,"insert into office_location(user_id,latitude,longitude,location,created_at,status) values('$user_id','$latitude','$longitude','$location','$datetime','$status')  ");
            if($insertsql){
                
                $response['Code']=200;
                $response['msg']="User Office Location Saved Successfully";
                
            } else{
                $response['Code'] = 250;
                $response['msg'] = "Error Inserting Data!!";
            }
        }
}else {
        $response['Code']=450;
        $response['msg']="Please provide user";
}

echo json_encode($response);



?>