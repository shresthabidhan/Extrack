<?php

$servername = "SERVER"; // MySQL Hostname
$username = "USER";     // MySQL Username
$password = "PASS";     // VPanel Password
$dbname = "extrackdb";  // Database Name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
