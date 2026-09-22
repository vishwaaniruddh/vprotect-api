<?php
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
 
 return $conn;
 }

?>