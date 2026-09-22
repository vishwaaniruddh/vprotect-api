<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');


$response = array();

$data = $_POST;

$userid = isset($data['userid']) ? $data['userid'] : '' ;
$queid = isset($data['queid']) ? $data['queid'] : '' ;
$answer_submitted = isset($data['answer_submit']) ? $data['answer_submit'] : '' ;

$_iscorrect = 0;

$user_security_question = mysqli_query($con, "select * from user_security_que_ans where user_id='".$userid."' AND question_id='".$queid."'");
if (mysqli_num_rows($user_security_question) > 0) {
    $quedetail = [];
    while ($fetchall = mysqli_fetch_assoc($user_security_question)) {
        $answer = $fetchall['answer'];
       // $question = $fetchall['question'];
        
        if($answer_submitted==$answer){
            $_iscorrect = 1;
        }
    }
    $response = [
        'Code' => 200,
        'msg' => 'User Security Question fetched successfully',
        'data' => $_iscorrect,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>