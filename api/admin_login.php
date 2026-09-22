<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

$data = $_POST;

$todays_date = date('Y-m-d');

$response = array();

/* Validity Check */

$current_date = date('Y-m-d');

$validity_query = mysqli_query($con,"
    SELECT id, start_date, end_date
    FROM validity
    LIMIT 1
");

if(mysqli_num_rows($validity_query) == 0){

    echo json_encode([
        'Code' => 500,
        'msg'  => 'Validity record not found'
    ]);
    exit;
}

$validity_data = mysqli_fetch_assoc($validity_query);

if(
    $current_date < $validity_data['start_date'] ||
    $current_date > $validity_data['end_date']
){

    echo json_encode([
        'Code'       => 403,
        'msg'        => 'Application validity expired',
        'start_date' => $validity_data['start_date'],
        'end_date'   => $validity_data['end_date']
    ]);
    exit;
}

$email_id = isset($data['email_id']) ? $data['email_id'] : '';
$password = isset($data['password']) ? $data['password'] : '';

if(!empty($email_id) && !empty($password)){
    
    if(strlen($email_id) == '' && $password==''){
        $response['Code'] = "422";  // 422 = senantic errors
        $response['msg'] = "Email ID Required";
    } else {
        $checksql = mysqli_query($con, "select * from user_login where email_id = '".$email_id."' and password = '".$password."' ");
        if(mysqli_num_rows($checksql) > 0){
            $fetchres = mysqli_fetch_assoc($checksql);
            $user_id = $fetchres['id'];
            $username = $fetchres['name'];
            $user_role = $fetchres['user_role'];
            
            $response['Code'] = 200; // 200 = successfull
            $response['msg'] = "Login successful";
            $response['user_id']=$user_id;
            $response['username'] = $username;
            $response['user_role'] = $user_role;
            
        } else {
            $response['Code'] = 401; // 401 = unauthorised
            $response['msg'] = "Invalid email id or password";
        }
    }
} else {
    $response['Code'] = 404; // 404 = not found
    $response['msg'] = "Email ID or password is missing";
}

echo json_encode($response);  
?>
