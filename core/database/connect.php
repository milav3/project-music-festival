<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "muzickifestival";

$conn = mysqli_connect($host, $user, $password, $db);
if($conn === false){
    die("Connection error");
}
?>