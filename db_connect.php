<?php
date_default_timezone_set("Asia/Manila");


$host = "localhost";
$user = "root"; // default XAMPP user
$pass = "";     // default XAMPP password
$db   = "bahashield";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
