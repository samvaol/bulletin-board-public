<?php
$host = "localhost";
$username = "xxx";
$password = "xxx";
$db_name = "bulletin_board";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
