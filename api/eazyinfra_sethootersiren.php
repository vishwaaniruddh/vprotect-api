<?php date_default_timezone_set('Asia/Kolkata');
include('eazyinfra_functions.php'); 

if(isset($_POST)){
	$onoff_type = 0;
	if(isset($_POST['onoff_type'])){
        $onoff_type = $_POST['onoff_type'];
	}
	$_ipaddress = "";
	if(isset($_POST['ip_address'])){
        $_ipaddress = $_POST['ip_address'];
	}
	$set_type = "";
	if(isset($_POST['set_type'])){
        $set_type = $_POST['set_type'];
	}
		
	if($_ipaddress!='' && $set_type!=''){	
		$is_macid = 0;
		$get_atmid_sql = mysqli_query($con,"select ATMID from all_dvr_live where IPAddress='".$_ipaddress."'");
		if(mysqli_num_rows($get_atmid_sql)>0){
		$get_atmid_data = mysqli_fetch_assoc($get_atmid_sql);
		$_atmid = $get_atmid_data['ATMID'];
		$get_macid_sql = mysqli_query($con,"select mac_id from panel_health_api_response where atmid='".$_atmid."'");
		if(mysqli_num_rows($get_macid_sql)>0){
			$get_macid_data = mysqli_fetch_assoc($get_macid_sql);
			$mac_id = $get_macid_data['mac_id'];
			$is_macid = 1;
		}
		}

		//$mac_id = $_POST['mac_id'];


		function getAccessToken($refresh_token){
				$curl = curl_init();

				curl_setopt_array($curl, array(
				CURLOPT_URL => 'http://103.141.218.138:4510/user/accesstoken',
				CURLOPT_RETURNTRANSFER => true,  
				CURLOPT_HEADER => 0,
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
				if(curl_error($curl)) {  
					print_r( curl_error($curl));  
				} 
				//echo '<pre>';print_r($response->statusCode);echo '</pre>';die;
				$_check_res = json_decode($response,true);
				//echo '<pre>';print_r($_check_res);echo '</pre>';
				//echo $_check_res['statusCode'];die;
				//die;
				//echo $response;
				
				if($_check_res['statusCode']=='403'){
					$new_access_token  = getLogin();
				}else{
					$curl = curl_init();

					curl_setopt_array($curl, array(
					CURLOPT_URL => 'http://103.141.218.138:4510/user/accesstoken',
					CURLOPT_RETURNTRANSFER => true,  
					CURLOPT_HEADER => 1,
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
					if(curl_error($curl)) {  
						print_r( curl_error($curl));  
					} 
					
					$headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
					$headerStr = substr( $response , 0 , $headerSize );
					$bodyStr = substr( $response , $headerSize );
				
					// convert headers to array
					$headers = headersToArray( $headerStr );
					$new_access_token = $headers['access_token'];
					curl_close($curl);
				}
				
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



		//$onoff_type = 1;
		//$mac_id = "30000328";
		//$set_type = 234;

		if($is_macid==1){
			if($set_type=='Hooter'){
				$set_type = 234;
			}
			if($set_type=='Siren'){
				$set_type = 235;
			}

			$get_details = json_decode(setHooterSiren($org_id,$mac_id,$onoff_type,$set_type,$access_token,$con),true);
			//echo '<pre>';print_r($get_details);echo '</pre>';die;
			if($get_details['statusCode']==200){
				echo '<pre>';print_r($get_details);echo '</pre>';
			}
			else if($get_details['statusCode']==401){
				$login_data = user_login($email,$password,$con);
				$new_access_token  = getAccessToken($refresh_token);
				$access_token =  $new_access_token;		
				if($new_access_token!=''){
					mysqli_query($con,"update ems_login_access_panel_health set access_token='".$new_access_token."' where id=1");
					
				//if($login_data==1){
				
				/*	$ems_login_sql = mysqli_query($con,"select email,password,access_token,org_id,refresh_token from ems_login_access_panel_health where id=1");
					$ems_login_access = mysqli_fetch_assoc($ems_login_sql);
					$access_token = $ems_login_access['access_token'];
					$org_id = $ems_login_access['org_id'];
					$refresh_token = $ems_login_access['refresh_token'];

					$email=$ems_login_access['email'];
					$password=$ems_login_access['password'];  */
					//$get_details = json_decode(getHooterSirenStatus($org_id,$panel_id,$group_id,$access_token,$con),true);
					$get_details = json_decode(setHooterSiren($org_id,$mac_id,$onoff_type,$set_type,$access_token,$con),true);
					//echo '<pre>';print_r($get_details);echo '</pre>';
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
			$array = array(['statusCode'=>202,'status'=>'Fail','statusMessage'=>'No mac id exists for given IPAddress']);
		}
	}else{
         $array = array(['statusCode'=>204,'status'=>'Fail','statusMessage'=>'must required ip address and set type']);
         
	}

}else{
    $array = array(['statusCode'=>203,'status'=>'Fail','statusMessage'=>'Send post request']);
}

echo json_encode($array);

?>
