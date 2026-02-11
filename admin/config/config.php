<?php
// Set timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');

$dbuser = "root";
$dbpass = "";
$host = "localhost:3306";
$db = "hotel";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);
