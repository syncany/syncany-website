<?php

$dbhost = 'localhost'; 
$dbuser = 'XXXXXXXX';
$dbpass = 'XXXXXXXX';
$dbname = 'XXXXXXXx';

$databaseConnection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
 
if ($databaseConnection->connect_error) {
	die('Database connection failed: ' . $databaseConnection->connect_error);
}

?>
