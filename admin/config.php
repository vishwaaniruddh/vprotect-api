<?php

date_default_timezone_set("Asia/Kolkata");

$db = "u444388293_frutopia";

// $username = "u444388293_frutopia";
// $password = "SarSoft@2025#";

$username = "root";
$password = "";
// $host = "localhost";
$host = '89.116.138.57';

$con = new mysqli($host, $username, $password, $db);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
else {
//echo "Connected succesfull";
}


?>