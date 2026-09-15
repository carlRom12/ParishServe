<?php
// The parish is in the Philippines; PHP's own default here is Europe/Berlin.
date_default_timezone_set('Asia/Manila');

$host = "localhost";
$user = "root";
$password = "";
$database = "parish_serve";

$conn = new mysqli($host, $user, $password, $database);
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);

}
$conn->set_charset('utf8mb4');
?>