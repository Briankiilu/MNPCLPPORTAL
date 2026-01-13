<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "mnpclp";


$connection = new mysqli($servername, $username, $password, $dbname);


if ($connection->connect_error) {
    
    error_log("Database Connection Failed: " . $connection->connect_error);
    die("ERROR: Could not connect to the database. Please try again later.");
}

?>