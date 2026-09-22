<?php
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

$lastid = isset($data['lastid']) ? $data['lastid'] : '' ;

// if($org_id !='' && $mac_id != ''){
if($lastid !=''){
    
    // $checkdetails = mysqli_query($con,"select * from aes_encrypted_data where org_id = '$org_id' and mac_id = '$mac_id' and decrypted_otp = '$otp' ");
     $checkdetails = mysqli_query($con,"select * from aes_encrypted_data where id = '$lastid' ");
    
    if(mysqli_num_rows($checkdetails)>0){
        
        $updateQry = mysqli_query($con,"update aes_encrypted_data set response = '$remark' , updated_at = '$created_at' where id='$lastid' ");
        
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
        'message' => 'Mac ID or Org ID not Found'
    ];

}

echo json_encode($response);

?>