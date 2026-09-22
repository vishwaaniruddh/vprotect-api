<?php
// include($_SERVER['DOCUMENT_ROOT'].'/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$datetime = date('Y-m-d H:i:s');
$date = date('Y-m-d');

$data = $_POST;

$response = array();

$user_id = isset($data['user_id']) ? $data['user_id'] : '';
$question_id = isset($data['question_id']) ? $data['question_id'] : '';
$answer = isset($data['answer']) ? $data['answer'] : '';


// $sql = "insert into user_login(name,contact_no,email,password,status,created_at) values('$user_name','$mobile_no','$email','$password','$status','$datetime')";
// echo json_encode($sql);

if($user_id!='') {
    
        if($question_id=='' && $answer==''){
            $response['Code'] = 400; 
            $response['msg'] = "Security Answer Mandatory";
        } else {
            $insertsql = mysqli_query($con,"insert into user_security_que_ans(user_id,question_id,answer,created_at) values('$user_id','$question_id','$answer','$datetime')  ");
            if($insertsql){
                
                $response['Code']=200;
                $response['msg']="User Security Answer Saved Successfully";
                
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