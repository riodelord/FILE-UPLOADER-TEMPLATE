<?php
require '../config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

if($filex->isLogged($mysql) == false) {
	header('Location:login.php');
	die();
}

if(!isset($_GET['act']))
	die();

$act = $_GET['act'];

// Ban IP by Download ID
if($act == 1) {
	if(isset($_GET['record']))
		$mysql->ban_ip_by_downloadid($_GET['record']);
	die('1');
	
// Unban IP by Download ID
}elseif($act == 2) {
	if(isset($_GET['record']))
		$mysql->unban_ip_by_downloadid($_GET['record']);
	die('1');

// Ban IP by IP
}elseif($act == 3) {
	if(isset($_GET['ip']))
		$mysql->ban_ip($_GET['ip']);
	die('1');

// Unban IP by IP
}elseif($act == 4) {
	if(isset($_GET['ip']))
		$mysql->unban_ip($_GET['ip']);
	die('1');

// Delete file
}elseif($act == 5) {
	if(isset($_GET['filecode'])) {
		$filecode = $_GET['filecode'];
		
		// Get file info
		$fileinfo = $mysql->get_file($filecode);
		$file = "../uploads/{$fileinfo->downloadcode}.{$fileinfo->fileextension}";
		
		if($mysql->delete_file($filecode) == true) {
			if(@unlink($file) === true)
				die('1');
			die('n');
		}
	}
	die('1');
}

die('n');