<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$date = isset($data['date']) ? $data['date'] : '';

$response = array();

$getreportQuery = "select * from report_notification " ;

$where = ' where ';

if($date !=''){
    $getreportQuery .= $where."date(created_at) = '$date' ";
}

$getreportQuery .= "order by id desc";

$getreport = mysqli_query($con,$getreportQuery);

$num_rows = mysqli_num_rows($getreport);



if($num_rows>0){

        $details = [];
        while($row = mysqli_fetch_assoc($getreport)){
            
            
            
            $created_at = $row['created_at'];
            $createdate = date("d-M-Y H:i:s",strtotime($created_at));
            $replied_at = $row['replied_at'];
            $created_by = $row['created_by'];
            
            $getusename = mysqli_query($con,"select name from user_login where id = '$created_by' ");
            $username = mysqli_fetch_assoc($getusename)['name'];
            
            
            $details[] = [
                    
                    'id' => $row['id'],
                    'application_name' => $row['application_name'],
                    'purpose' => $row['purpose'],
                    'created_at' => $createdate,
                    'is_reply' => $row['is_reply'],
                    'reply_message' => $row['reply_message'],
                    'replied_at' => date("d-M-Y H:i:s",strtotime($replied_at)),
                    'replied_by' => $username,
                    'r_show' => "false"
                    
                    
                ];
        }
        
        $response = [
            'Code' => 200,
            'msg' => 'Report Notification Details fetched successfully',
            'data' => $details,
            
        ];
        
    }else{
        $response = [
            'Code' => 250,
            'msg' => "Unable to fetch Details!!",
            ];
    }
    


echo json_encode($response);
?>