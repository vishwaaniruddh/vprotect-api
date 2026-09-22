<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

// $userid = isset($data['userid']) ? $data['userid'] : '' ;
$application_name = isset($data['application_name']) ? $data['application_name'] : '';
$purpose = isset($data['purpose']) ? $data['purpose'] : '';

if($application_name!='' && $purpose!=''){
    $insertsql = mysqli_query($con,"insert into report_notification (application_name,purpose) values ('$application_name','$purpose') ");
    
    if($insertsql){
        $response = [
            'Code' => 200,
            'message' => 'Report Inserted'
            ];
    } else {
        $response = [
            'Code' => 400,
            'message' => 'Unable to insert the report!! Error!!'
            ];
    }
    
}



echo json_encode($response);
?>