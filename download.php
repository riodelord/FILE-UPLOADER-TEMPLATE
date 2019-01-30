<?php
require 'config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

$site = $mysql->get_setting('site_url');

// Check file existance in DB		ok
// Check file existance in host		ok
// Add one visit					ok
// Check days/downloads expiration	
// Check password protection		
// Check password in POST data. If doesn't exist redirect

// BEFORE download, add one to the downloads
// If couldn't add, avoid download
// BEFORE download, Expire file if necessary
// If coulnd't expire, avoid download
// Offer download

if(!isset($_GET['filecode'])) {
	header("Location: {$site}index.php");
	die();
}
$filecode = $_GET['filecode'];


// Check file existance in DB
if($mysql->check_existing_file($filecode) === false) {
	header("Location: {$site}error/1/"); // File doesn't exist in our Database
	die();
}
$filedata = $mysql->get_file($filecode);
if(!is_object($filedata) && $filedata == 0)
	die('Something is wrong while trying to retrieve your file.');


// Check file existance in host
if(!file_exists("uploads/{$filedata->downloadcode}.{$filedata->fileextension}")) {
	header("Location: {$site}error/2/"); // File doesn't exist in our servers
	die();
}

	
// Add visit (only if we have not post data)
if(!isset($_POST['password']) && !isset($_POST['download']))
	$mysql->add_visit($filecode, $filedata->id);


// Check if file is expired already
if($filedata->status == '2') {
	header("Location: {$site}error/3/"); // File expired
	die();
}
	


// Check days expiration
if($filedata->days_expiration != '0') {
	if(date('Y-m-d') != date('Y-m-d', strtotime($filedata->date_uploaded))) {
		$date = strtotime(date('Y-m-d H:i:s'));
		$uploaded = strtotime($filedata->date_uploaded);
		$days_expiration = $filedata->days_expiration;
		$days_since_up = (int)date('j', $date - $uploaded);
		
		// Should be expired
		if($days_since_up >= $days_expiration) {
			$mysql->expire_file($filecode, $filedata->id);
			header("Location: {$site}error/3/"); // File expired
			die();
		}
	}
}


// Check if it has password protection
if($filedata->password_protected != '0') {
	// If we don't have post data
	if(!isset($_POST['password']) && !isset($_POST['download'])) {
		require 'password.php';
		die();
	}else{
		// We have post data
		// Empty or invalid data? Reinclude but with error
		if($_POST['password'] == '') {
			$error = 1;
			require 'password.php';
			die();
		}
		
		if($mysql->validate_password(md5($_POST['password']), $filecode) === false) {
			$mysql->add_wrong_pass_attempt($filedata->id);
			$error = 2;
			require 'password.php';
			die();
		}else{
			// All good, add correct pass attempt and keep going
			$mysql->add_correct_pass_attempt($filedata->id);
		}
	}
}

// All good. Add one download to the counter
$mysql->add_download($filecode, $filedata->id);

// If it has download expiration, check if it should expire by now
if($filedata->downloads_expiration != '0') {
	if(($filedata->downloads+1) >= $filedata->downloads_expiration) {
		$mysql->expire_file($filecode, $filedata->id);
	}
}


header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$filedata->filename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize('uploads/'.$filedata->downloadcode.'.'.$filedata->fileextension));

readfile("uploads/{$filedata->downloadcode}.{$filedata->fileextension}");