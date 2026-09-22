<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = $_POST;

$response = array();

$userid = isset($data['userid']) ? $data['userid'] : '';
$datetime = isset($data['datetime']) ? $data['datetime'] : '';
$latitude = isset($data['latitude']) ? $data['latitude'] : '';
$longitude = isset($data['longitude']) ? $data['longitude'] : '';
$type = $data['type'];
$punchin_location = isset($data['punchin_location']) ? $data['punchin_location'] : '';
$punchout_location = isset($data['punchout_location']) ? $data['punchout_location'] : '';
$status = 1;

$date = date("Y-m-d", strtotime($datetime));
$time = date("H:i:s", strtotime($datetime));
$hour = date("H", strtotime($datetime));
$minute = date("i", strtotime($datetime));

// $prevdate = $date -1;

$latecount = 0;
$wfh_status = 0;


// Function to calculate distance
function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // in meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
}


if ($type == "punch_in") {
    
    // $checkattstatus = mysqli_query($con,"select punchedin_status,punchedout_status,attendance_status from punch_in_out where userid = '$userid' and    date='' ");

    //check login late or not
    if ($hour > 10 || ($hour == 10 && $minute > 45)) {
        $remark = "Late Login";
        $latecount = 1;
    } else {
        $remark = "On-Time";
    }

    
    $attendance_status = "";
    $punchedin_status = 1;
    
    $checkwfh = mysqli_query($con,"select * from apply_work_from_home where userid = '$userid' and from_date='$todays_date' ");
    if(mysqli_num_rows($checkwfh)>0){
        $wfh_status = 1;
    } else {
        $wfh_status = 0;
    }
    
    
    if($wfh_status!= 1){
        $checklocationareasql = mysqli_query($con,"select * from office_location where status = 1 ");
        $matched_office = null;
        if (mysqli_num_rows($checklocationareasql) > 0) {
            while ($row = mysqli_fetch_assoc($checklocationareasql)) {
                $distance = getDistance($latitude, $longitude, $row['latitude'], $row['longitude']);
                
                if ($distance <= 100) {
                    $matched_office = $row;
                    break;
                }
            }
            
            // If no office matched after checking all
            if ($matched_office === null) {
                $response['Code'] = 403;
                $response['msg'] = "You are not within 100 meters of any office location.";
                echo json_encode($response);
                exit;
            }
            
        } else {
            $response['Code'] = 500;
            $response['msg'] = "No office locations configured.";
            echo json_encode($response);
            exit;
        }
        
        if ($matched_office) {
            // echo "Login location is within 100 meters of office: " . $matched_office['location'];
            // Proceed with login and record this office as the login location
            $insertsql = mysqli_query($con, "insert into punch_in_out(userid,date,punch_in,punch_in_latitude,punch_in_longitude,login_remark,created_at,punchedin_status,punchin_location,latecount,wfh_status) values ('$userid','$date','$time','$latitude','$longitude','$remark','$created_at','$punchedin_status','$punchin_location','$latecount','$wfh_status')  ");
    
            if ($insertsql) {
                $response['Code'] = 200;
                $response['msg'] = "PunchedIn Successfully!!";
            } else {
                $response['Code'] = 250;
                $response['msg'] = "Error Logging In";
            }
        } else {
            $response['Code'] = 406;
            $response['msg'] = "Login location is NOT within 100 meters.";
            // exit;
        }
        
    } else{
        $insertsql = mysqli_query($con, "insert into punch_in_out(userid,date,punch_in,punch_in_latitude,punch_in_longitude,login_remark,created_at,punchedin_status,punchin_location,latecount,wfh_status) values ('$userid','$date','$time','$latitude','$longitude','$remark','$created_at','$punchedin_status','$punchin_location','$latecount','$wfh_status')  ");

        if ($insertsql) {
            $response['Code'] = 200;
            $response['msg'] = "PunchedIn Successfully!!";
        } else {
            $response['Code'] = 250;
            $response['msg'] = "Error Logging In";
        }
    }
    

    
    // if ($matched_office) {
    //     // echo "Login location is within 100 meters of office: " . $matched_office['location'];
    //     // Proceed with login and record this office as the login location
    //     $insertsql = mysqli_query($con, "insert into punch_in_out(userid,date,punch_in,punch_in_latitude,punch_in_longitude,login_remark,created_at,punchedin_status,punchin_location,latecount,wfh_status) values ('$userid','$date','$time','$latitude','$longitude','$remark','$created_at','$punchedin_status','$punchin_location','$latecount','$wfh_status')  ");

    //     if ($insertsql) {
    //         $response['Code'] = 200;
    //         $response['msg'] = "PunchedIn Successfully!!";
    //     } else {
    //         $response['Code'] = 250;
    //         $response['msg'] = "Error Logging In";
    //     }
    // } else {
    //     $response['Code'] = 406;
    //     $response['msg'] = "Login location is NOT within 100 meters.";
    //     // exit;
    // }
    


} elseif ($type == "punch_out") {

    // Fetch punch-in details
    $punchintimesql = mysqli_query($con, "SELECT punch_in, punch_in_latitude, punch_in_longitude FROM punch_in_out WHERE userid = '$userid' AND date = '$date'");
    $fetch_time = mysqli_fetch_assoc($punchintimesql);

    if ($fetch_time) {
        $punch_in_time = $fetch_time['punch_in'];
        $punch_in_latitude = $fetch_time['punch_in_latitude'];
        $punch_in_longitude = $fetch_time['punch_in_longitude'];

        $punchInTime = new DateTime($punch_in_time);
        $punchOutTime = new DateTime($time);
        $interval = $punchInTime->diff($punchOutTime);

        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;

        $totalHours = $hours . ':' . $minutes . ':' . $seconds;
        $totalMinutes = ($hours * 60) + $minutes;

        // Function to calculate distance
        // function getDistance($lat1, $lon1, $lat2, $lon2) {
        //     $earth_radius = 6371000; // in meters
        //     $dLat = deg2rad($lat2 - $lat1);
        //     $dLon = deg2rad($lon2 - $lon1);
        //     $a = sin($dLat / 2) * sin($dLat / 2) +
        //         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        //         sin($dLon / 2) * sin($dLon / 2);
        //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        //     return $earth_radius * $c;
        // }

        $distance = getDistance($punch_in_latitude, $punch_in_longitude, $latitude, $longitude);

        // Attendance status logic
        if ($hours < 2 || ($hours == 2 && $minutes < 0)) {
            $att_status = "Absent";
            $halfday = "yes";
        } elseif ($hours < 7) {
            $att_status = "Half Day";
            $halfday = "yes";
        } else {
            $att_status = "Present";
            $halfday = "no";
        }

        $per_day_full_day_count = ($hours < 8 || ($hours == 8 && $minutes < 30)) ? 1 : 0;
        $punchedout_status = 1;

        if ($distance > 100) {
            $response['Code'] = 450;
            $response['msg'] = 'Login/Logout Distance is above 100 metres.';
            $response['distance'] = $distance;
        } else {
            $sql = "UPDATE punch_in_out 
                    SET punch_out = '$time', 
                        punch_out_latitude = '$latitude', 
                        punch_out_longitude = '$longitude', 
                        hrs_diff = '$totalHours', 
                        half_day = '$halfday', 
                        attendance_status = '$att_status', 
                        updated_at = '$created_at', 
                        punchedout_status = '$punchedout_status', 
                        punchout_location = '$punchout_location', 
                        per_day_full_day_count = '$per_day_full_day_count' 
                    WHERE date = '$todays_date' AND userid = '$userid'";

            $updatesql = mysqli_query($con, $sql);

            if ($updatesql) {
                $response['Code'] = 200;
                $response['msg'] = "Successfully PunchedOut";
                $response['diff'] = $totalHours;
                // $response['sql'] = $sql; // For debugging
            } else {
                $response['Code'] = 250;
                $response['msg'] = "Error Logging Out!!";
                $response['distance'] = $distance;
                $response['sql'] = $sql; // For debugging
            }
        }
    } else {
        $response['Code'] = 404;
        $response['msg'] = "Punch In Time not found!";
    }

} else {
    $response['Code'] = 400;
    $response['msg'] = "Invalid Request Type!";
}


echo json_encode($response);
?>