<?php 

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/db_connection.php');
include(__DIR__ . '/config/db_connection.php');

// include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/aesCryptodecrypt.php');
include(__DIR__ . '/api/aesCryptodecrypt.php');



// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');

$con = OpenCon();
$date = date('Y-m-d H:i:s');

$ems_login_sql = mysqli_query($con,"select email,password,access_token,org_id,refresh_token from eazyinfra_login_access where id=1");
$ems_login_access = mysqli_fetch_assoc($ems_login_sql);
$access_token = $ems_login_access['access_token'];
$org_id = $ems_login_access['org_id'];
$refresh_token = $ems_login_access['refresh_token'];

$email=$ems_login_access['email'];
$password=$ems_login_access['password'];

function headersToArray( $str )
{
    $headers = array();
    $headersTmpArray = explode( "\r\n" , $str );
    for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
    {
        // we dont care about the two \r\n lines at the end of the headers
        if ( strlen( $headersTmpArray[$i] ) > 0 )
        {
            // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
            if ( strpos( $headersTmpArray[$i] , ":" ) )
            {
                $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}

function getOTP($org_id,$panel_id,$access_duration,$access_token,$con){
	$curl = curl_init();
	//$panel_id_arr = array();
	//array_push($panel_id_arr,$panel_id);
	//$panel_id_array = array();
	//array_push($panel_id_array ,$panel_id_arr);
	$foo = new StdClass();
	$foo->org_id = (int)$org_id;
	$foo->panel_id = $panel_id;
	$foo->access_duration = $access_duration;
	$json = json_encode($foo);
   // echo '<pre>';print_r($json);echo '</pre>';
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://eazyinfra.utopiatech.in/offlineotp',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_POSTFIELDS => $json,
	  /*CURLOPT_POSTFIELDS =>'{
		"org_id": 1004, 
		"group_id": "29", 
		"panel_id": ["565656"], 
		"config_type": "ess_panel"
		}', */
	  CURLOPT_HTTPHEADER => array(
		'access_token: '.$access_token,
		'Content-Type: application/json'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

//if(isset($_POST)){
	//$org_id = 0;
// 	$org_id = 390;
// 	if(isset($_POST['org_id'])){
//         $org_id = $_POST['org_id'];
// 	}
	//$mac_id = "";
	$user_id = 1;
	if(isset($_POST['user_id'])){
        $user_id = $_POST['user_id'];
	}
	$panel_id = "111111";
	if(isset($_POST['panel_id'])){
        $panel_id = $_POST['panel_id'];
	}
	$access_duration = 5;
	if(isset($_POST['access_duration'])){
        $access_duration = $_POST['access_duration'];
	}
		
	if($panel_id!='' && $org_id!=''){	
		
		function getAccessToken($refresh_token){
				$curl = curl_init();
             //   $refresh_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZWQ1MjU1MTk0YTRlMGU1ZDE0MjRjYSIsImVtYWlsIjoiYXBpdXNlcmlkQHV0b3BpYXRlY2guaW4iLCJvcmdfaWQiOjI5OCwiZ3JvdXBfaWRzIjpbIjAiLCIxIiwiMiIsIjMiLCI0Il0sInJlYWQiOjgxODUsIndyaXRlIjo4MTg1LCJyb2xlX2lkIjoxMSwiYWxsb3dlZF9vcmdfaWRzIjpbXSwiaWF0IjoxNzQ0NjEzNTQ3LCJleHAiOjE3NzA0MDI1OTl9.szzvdSFMNb0r5uh34GA6nsaSbYkujig_bKBvfIweCd4";
				curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://eazyinfra.utopiatech.in/user/accesstoken',
				CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_HEADER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HTTPHEADER => array(
					'refresh_token: '.$refresh_token
				),
				));

				$response = curl_exec($curl);
				
				$headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
				//echo $headerSize;die;
				$headerStr = substr( $response , 0 , $headerSize );
				$bodyStr = substr( $response , $headerSize );
			
				// convert headers to array
				$headers = headersToArray( $headerStr );
				// echo '<pre>';print_r($headers);echo '</pre>';die;
				$new_access_token = $headers['access_token'];
				
				// echo '<pre>';print_r($new_access_token);echo '</pre>';die;
				
				curl_close($curl);
				
				if($new_access_token!=''){
					return $new_access_token;
				}else{
					return '';
				}
			}
			
			function getLogin(){
				$curl = curl_init();
				$postData = [
					'email' => 'api@cts.in',
					'password' => 'api@123'
				];
				curl_setopt_array($curl, array(
				CURLOPT_URL => 'http://103.141.218.138:4510/user/login',
				CURLOPT_RETURNTRANSFER => true,  
				CURLOPT_HEADER => 1,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_POSTFIELDS => json_encode($postData),   
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				), 
				));

				$response = curl_exec($curl);
				if(curl_error($curl)) {  
					print_r( curl_error($curl));  
				} 
				
				//echo $response;die;
				$headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
				$headerStr = substr( $response , 0 , $headerSize );
				$bodyStr = substr( $response , $headerSize );

				// convert headers to array
				$headers = headersToArray( $headerStr );
				$new_access_token = $headers['access_token'];
				curl_close($curl);
				if($new_access_token!=''){
					return $new_access_token;
				}else{
					return '';
				}
			}
			
			
	//		echo $org_id."--".$panel_id."--".$access_duration."--".$access_token;



			$get_details = json_decode(getOTP($org_id,$panel_id,$access_duration,$access_token,$con),true);
		//	echo '<pre>';print_r($get_details);echo '</pre>';die;
			if($get_details['statusCode']==200){
				// echo '<pre>';print_r($get_details);echo '</pre>';
				
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
			else if($get_details['statusCode']==401){
				//$login_data = user_login($email,$password,$con);
				$new_access_token  = getAccessToken($refresh_token);
				$access_token =  $new_access_token;		
				if($new_access_token!=''){
					mysqli_query($con,"update eazyinfra_login_access set access_token='".$new_access_token."',updated_at='$date' where id=1");
					
				    $get_details = json_decode(getOTP($org_id,$panel_id,$access_duration,$access_token,$con),true);
					
					$array = $get_details;
				}else{
				//	echo 'Something Wrong check login credentials!';
					$array = array(['statusCode'=>203,'status'=>'Fail','statusMessage'=>'Something Wrong check login credentials!']);
				}
			}else{
				//echo '<pre>';print_r($get_details);echo '</pre>';
				$array = $get_details;
				//$array = array(['statusCode'=>203,'status'=>'Fail','statusMessage'=>'No mac id exists for given IPAddress']);
			}
		
	}else{
         $array = array(['statusCode'=>204,'status'=>'Fail','statusMessage'=>'must required ip address and set type','post'=> json_encode($_POST)]);
         
	}

//}
/*
else{
    $array = array(['statusCode'=>203,'status'=>'Fail','statusMessage'=>'Send post request']);
}
*/
// echo '<pre>';print_r($array);echo '</pre>';
echo json_encode($array);

?>
