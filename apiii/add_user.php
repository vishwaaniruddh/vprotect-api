<?php
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$datetime = date('Y-m-d H:i:s');
$date = date('Y-m-d');



 $data = $_POST;

$response = array();

$user_name = isset($data['user_name']) ? $data['user_name'] : '';
$mobile_no = isset($data['mobile_no']) ? $data['mobile_no'] : '';
$password = isset($data['password']) ? $data['password'] : '';
$email = isset($data['email_id']) ? $data['email_id'] : '' ;
$user_role = isset($data['user_role']) ? $data['user_role'] : '' ;
$status = 1; 

//echo json_encode($data);

// $sql = "insert into user_login(name,contact_no,email,password,status,created_at) values('$user_name','$mobile_no','$email','$password','$status','$datetime')";
// echo json_encode($sql);



    if(strlen($mobile_no) == 10 && !empty($password)) {
        $checksql = mysqli_query($con,"select contact_no,password from user_login where contact_no = '$mobile_no'");
        $response['Code'] = mysqli_num_rows($checksql);
        
            if(mysqli_num_rows($checksql)>0){
                $response['Code'] = 400; 
                $response['msg'] = "User's Contact Already Exist";
                } else {
                    $insertsql = mysqli_query($con,"insert into user_login(name,contact_no,email_id,password,status,user_role,created_at) values('$user_name','$mobile_no','$email','$password','$status','$user_role','$datetime')  ");
                    if($insertsql){
                        $last_id = mysqli_insert_id($con);
                        
                       // $insertleaveuser = mysqli_query($con,"insert into leave_count_details(userid,created_at) values ('$last_id','$datetime')  ");
                        
                        $response['Code']=200;
                        $response['msg']="User Created Successfully";
                        $response['id'] = $last_id;
                        
                    } else{
                        $response['Code'] = 250;
                        $response['msg'] = "Error Inserting Data!!";
                    }
                }  
        }else {
            $response['Code']=450;
            $response['msg']="Invalid Mobile Number or Password";
        }

echo json_encode($response);


?>