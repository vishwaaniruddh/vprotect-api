<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("Asia/Kolkata");

function OpenCon()
 {
 $dbhost = '89.116.138.57';
 $dbuser = "u444388293_frutopia";
 $dbpass = "SarSoft@2025#";
 $db = "u444388293_frutopia";
//  $db_port ='3308';
// $port = "3308";
 $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);

//   $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

//     if ($conn->connect_error) {
//         echo "not connected";
//         die();
//     } else {
//         echo "connected";
//     }

 return $conn;
 }

//  OpenCon();
?>