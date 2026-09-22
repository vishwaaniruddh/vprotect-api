<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

//$date = isset($data['date']) ? $data['date'] : '' ;

$holidaysql = mysqli_query($con,"select * from holidays  ");
// $holiday_result = mysqli_fetch_assoc($holidaysql);
if(mysqli_num_rows($holidaysql)>0){
    $details = [];
    
    while($row = mysqli_fetch_assoc($holidaysql)){
        $details [] = [
            'holiday_data' => [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'date' => $row['date'],
                'type' => $row['type'],
                
                ]
            
            ];
            
    } 
    $response = [
            'Code' => 200,
            'msg' => 'Holiday Details fetched successfully',
            'data' => $details,
            
        ];
} else {
    $response = [
            'Code' => 250,
            'msg' => 'No Holiday',
            // 'data' => $details,
            
        ];
}


echo json_encode($response);
?>