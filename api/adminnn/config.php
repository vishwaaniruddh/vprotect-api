<?php

date_default_timezone_set("Asia/Kolkata");

$username = "u444388293_frutopia";
$db = "u444388293_frutopia";
$password = "SarSoft@2025#";
// $host = "localhost";
$host = '89.116.138.57';

$con = new mysqli($host, $username, $password, $db);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
} else {
    //echo "Connected succesfull";
}


?>