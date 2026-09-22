<?php

date_default_timezone_set("Asia/Kolkata");

$username = "u444388293_vprotectFR";
$db = "u444388293_vprotectFR";
$password = "Sar@2026";
$host = "localhost";
//$host = '89.116.138.57';

$con = new mysqli($host, $username, $password, $db);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
} else {
    
    
//mysqli_query($con, "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"); this is imporatnt for aditya vps server uncomment this code when it is used in their server
    // echo "Connected succesfull";
}


?>