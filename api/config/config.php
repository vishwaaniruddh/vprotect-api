<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set("Asia/Kolkata");

/* Project Name */
define('PROJECT_NAME', 'vprotectFR');

$username = "u444388293_vprotectFR";
$db = "u444388293_vprotectFR";
$password = "Sar@2026";
$host = "localhost";


// $username = "u444388293_vprotect";
// $db = "u444388293_vprotect";
// $password = "Vprotect@2026#";
// $host = "localhost";

$con = new mysqli($host, $username, $password, $db);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
} else {
	mysqli_query($con,"SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
	//echo "Connected succesfull";
}


?>
