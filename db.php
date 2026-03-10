<?php
// Replace these with the actual values from your InfinityFree MySQL page
$hostname = "sqlXXX.epizy.com"; 
$username = "epiz_XXXXXXXX";
$password = "your_password";
$dbname   = "epiz_XXXXXXXX_messenger_db";

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["error" => "DB Connection Failed"]));
}
?>