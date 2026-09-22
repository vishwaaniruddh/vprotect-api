<?php

echo $_SERVER['DOCUMENT_ROOT'];

$con = new sqli("localhot","fruuser","frupass","frutopia");
if($con -> connect_error){
die("Connection Field:" . $con->connect_error);
}else{
echo "Connection SuccessFull";
}

?>
