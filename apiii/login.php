<?php
include($_SERVER['DOCUMENT_ROOT'].'/FRUtopia/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$data = $_POST;

$todays_date = date('Y-m-d');

$response = array();

$mobile_no = isset($data['mobile_no']) ? $data['mobile_no'] : '';
$password = isset($data['password']) ? $data['password'] : '';

if(!empty($mobile_no) && !empty($password)){
    
    if(strlen($mobile_no) !== 10){
        $response['Code'] = "422";  // 422 = senantic errors
        $response['msg'] = "Mobile number exceeds 10 digits";
    } else {
        $checksql = mysqli_query($con, "select * from user_login where contact_no = '$mobile_no' and password = '$password'");
        if(mysqli_num_rows($checksql) > 0){
            $fetchres = mysqli_fetch_assoc($checksql);
            $user_id = $fetchres['id'];
            $username = $fetchres['name'];
            
            $response['Code'] = 200; // 200 = successfull
            $response['msg'] = "Login successful";
            $response['user_id']=$user_id;
            $response['username'] = $username;
            
            $_is_sequrity_ans_submit = 0;
            
            $checkusersequritystatus = mysqli_query($con,"SELECT * FROM user_security_que_ans WHERE user_id='$user_id' GROUP BY question_id;");
            
            //new logic add
            $no_of_security_question = mysqli_query($con,"SELECT * FROM user_security_question WHERE status =1;");
           
            
            if(mysqli_num_rows($checkusersequritystatus) == mysqli_num_rows($no_of_security_question)){
                    $_is_sequrity_ans_submit = 1;
            }
            
            $response['is_security_ans_submit'] = $_is_sequrity_ans_submit;
            
            
            $checkpunchedinstatus = mysqli_query($con,"select punchedin_status,punchedout_status from punch_in_out where date = '$todays_date' and userid = '$user_id' ");
            $statusdetail = mysqli_fetch_assoc($checkpunchedinstatus);
            if(mysqli_num_rows($checkpunchedinstatus)>0){
                
                $punchedin_status = $statusdetail['punchedin_status'];
                $punchedout_status = $statusdetail['punchedout_status'];
            } else{
                $punchedin_status = 0;
                $punchedout_status = 0;
            }
            
            $response['punchedin_status'] = $punchedin_status;
            $response['punchedout_status'] = $punchedout_status;
            
            
            
        } else {
            $response['Code'] = 401; // 401 = unauthorised
            $response['msg'] = "Invalid mobile number or password";
        }
    }
} else {
    $response['Code'] = 404; // 404 = not found
    $response['msg'] = "Mobile number or password is missing";
}

echo json_encode($response);
?>
