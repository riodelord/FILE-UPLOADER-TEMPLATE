<?php

/***************** You can edit the 4 lines below *****************/
$dbhost = 'YOUR MYSQL HOST HERE';
$dbuser = 'YOUR MYSQL USER HERE';
$dbpass = 'YOUR MYSQL PASS HERE';
$dbname = 'YOUR MYSQL DATABASE NAME HERE';






/***************** From this line below, DO NOT EDIT ANYTHING! *****************/
session_start();
if($dbhost == 'YOUR MYSQL HOST HERE' || $dbuser == 'YOUR MYSQL USER HERE' || $dbpass == 'YOUR MYSQL PASS HERE' || $dbname == 'YOUR MYSQL DATABASE NAME HERE')
	die('Please edit the config.php file with your MySQL Details and run the install.php file to install Filex. For further information, read the Documentation');

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if($mysqli->connect_errno)
	die('Something went wrong while trying to connect to your MySQL Database. Error No. ' . $mysql->connect_errno);