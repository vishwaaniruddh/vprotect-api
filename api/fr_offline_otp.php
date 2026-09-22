<?php 

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
include(__DIR__ . '/config/config.php');

//include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/aesCryptodecrypt.php');
include(__DIR__ . '/aesCryptodecrypt.php');

 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');

$date = date('Y-m-d H:i:s');


function getOTP($panel_id,$access_duration,$con){
	$curl = curl_init();
	
// 	$foo = new StdClass();
// 	$foo->org_id = (int)$org_id;
// 	$foo->panel_id = $panel_id;
// 	$foo->access_duration = $access_duration;
// 	$json = json_encode($foo);
   // echo '<pre>';print_r($json);echo '</pre>';
   
   $curl_url = 'http://195.35.7.83:6008/offlineotp?panel_id='.$panel_id.'&access_duration='.$access_duration;
   
    curl_setopt_array($curl, array(
      CURLOPT_URL => $curl_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
   

	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

	$org_id = 289;
	$mac_id=0;
	//$user_id = 1;
	if(isset($_POST['user_id'])){
        $user_id = $_POST['user_id'];
	}
	//$panel_id = "111111";
	if(isset($_POST['panel_id'])){
        $panel_id = $_POST['panel_id'];
	}
	$access_duration = 5;
	if(isset($_POST['access_duration'])){
        $access_duration = $_POST['access_duration'];
	}
		
	if($panel_id!='' && $user_id!=''){	
	
			$_get_details = json_decode(getOTP($panel_id,$access_duration,$con),true);
			$get_details = $_get_details['data'];
		//	echo '<pre>';print_r($get_details);echo '</pre>';die;
		
			if($get_details['statusCode']==200){
				
				// add descrypt code here 
				// $encrypted_otp = "U2FsdGVkX1+efkwq9UyHtCq7a+C2kJWBG4OArNrElPI=";
				$encrypted_otp = $get_details['result']['otp'];
                $key = "xx8921AHFFpojaspkeATustb25b698emnruaqjhasgKGHSasfabAjfsakkaw289";
                
                $generation_time = $get_details['result']['generation_time'];
                $expiration_time = $get_details['result']['expiration_time'];
                
                $formatted_generation_time = date("Y-m-d H:i:s", $generation_time);;
                $formatted_expiration_time = date("Y-m-d H:i:s", $expiration_time);;
                
                
                // echo "encrypted otp : ". $encrypted_otp;
                // echo "<br>";
                // $otp = cryptoJsAesDecrypt($key, $encrypted_otp);
                // echo "OTP: ".$otp;
                
                try {
                    $otp = cryptoJsAesDecrypt($key, $encrypted_otp);
                    $get_details['result']['decrypted_otp'] = $otp;
                    $get_details['result']['formatted_generation_time'] = $formatted_generation_time;
                    $get_details['result']['formatted_expiration_time'] = $formatted_expiration_time;
                    
                    $encryptiondetailsQuery = mysqli_query($con,"insert into aes_encrypted_data (`user_id`, `panel_id`,`org_id`, `mac_id`, `encrypted_otp`, `decrypted_otp`, `encrypted_generation_time`, `decrypted_generation_time`, `encrypted_expiration_time`, `decrypted_expiration_time`, `created_at`) values ('$user_id','$panel_id','$org_id','$mac_id','$encrypted_otp','$otp','$generation_time','$formatted_generation_time','$expiration_time','$formatted_expiration_time','$date') ");
                    
                    if($encryptiondetailsQuery){
                        $lastid = mysqli_insert_id($con);
                        $get_details['result']['lastid'] = $lastid;
                    }
                } catch (Exception $e) {
                    $get_details['result']['decrypted_otp_error'] = $e->getMessage();
                }
                
                // $encryptiondetailsQuery = mysqli_query($con,"insert into aes_encrypted_data (`org_id`, `mac_id`, `encrypted_otp`, `decrypted_otp`, `encrypted_generation_time`, `decrypted_generation_time`, `encrypted_expiration_time`, `decrypted_expiration_time`, `created_at`) values ('$org_id','$mac_id','$encrypted_otp','$otp','$generation_time','$formatted_generation_time','$expiration_time','$formatted_expiration_time','$date') ");
        
                $array = $get_details;
				
				
			}
			else{
				//echo '<pre>';print_r($get_details);echo '</pre>';
				$array = $get_details;
				//$array = array(['statusCode'=>203,'status'=>'Fail','statusMessage'=>'No mac id exists for given IPAddress']);
			}
		
	}else{
         $array = array(['statusCode'=>204,'status'=>'Fail','statusMessage'=>'must required userID and panelID','post'=> json_encode($_POST)]);
         
	}


echo json_encode($array);

?>
