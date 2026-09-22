<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;
$response = [];

// $org_id = isset($data['org_id']) ? $data['org_id'] : '' ;
// $mac_id = isset($data['mac_id']) ? $data['mac_id'] : '' ;
// $access_duration = isset($data['access_duration']) ? $data['access_duration'] : '' ;
// $otp = isset($data['otp']) ? $data['otp'] : '' ;
$remark = isset($data['remark']) ? $data['remark'] : '' ;
$status = isset($data['status']) ? $data['status'] : '' ;
$userid = isset($data['userid']) ? $data['userid'] : '' ;
$id = isset($data['id']) ? $data['id'] : '' ;

// $status = 1;

// if($org_id !='' && $mac_id != ''){
if($id !=''){
    
    // $checkdetails = mysqli_query($con,"select * from aes_encrypted_data where org_id = '$org_id' and mac_id = '$mac_id' and decrypted_otp = '$otp' ");
     $checkdetails = mysqli_query($con,"select * from alert_otp_request where id = '$id' ");
    
    if(mysqli_num_rows($checkdetails)>0){
        
        // $updateQry = mysqli_query($con,"update alert_otp_request set requested_status = '$status' , updated_at = '$created_at', updated_by = '$userid' where id='$id' ");
        
        $updateQuery = "UPDATE alert_otp_request 
                        SET requested_status = '$status',
                            updated_at = '$created_at',
                            updated_by = '$userid'";

        // ⭐ If rejected (status = 2) → add remark to query
        if ($status == 2) {
            $updateQuery .= ", remark = '$remark'";
        }

        // WHERE clause
        $updateQuery .= " WHERE id='$id'";

        $updateQry = mysqli_query($con, $updateQuery);
        
        
        
        if($updateQry){
            
            $response =[
                'Code' => 200,
                'status' => 'Success',
                'message' => 'Response Updated Successfully'
            ];
            
        }else {
            
            $response =[
                'Code' => 250,
                'status' => 'Error',
                'message' => 'Error Updating Response'
            ];
        }
        
    } else{
        
        $response =[
            'Code' => 400,
            'status' => 'Error',
            'message' => 'Data Not Found'
        ];
        
    }
} else{
    
    $response =[
        'Code' => 410,
        'status' => 'Error',
        'message' => 'ID Required'
    ];

}

echo json_encode($response);

?>