<?php
if(!isset($_FILES['file']))
	die('-');

require 'config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

$days = $_POST['days'];
$downloads = $_POST['downloads'];
$password = $_POST['password'];

if($days != '' && !is_numeric($days))
	die('no');
if($downloads != '' && !is_numeric($downloads))
	die('no');

if($days == '')
	$days = 0;
if($downloads == '')
	$downloads = 0;
if($password == ''){
	$password_protected = 0;
	$password_protection = 0;
}else{
	$password_protected = 1;
	$password_protection = md5($password);
}

// Check extension
$extension = basename($_FILES['file']['name']);
if($mysql->get_setting('allowed_extensions') != '') {
	$allowed_extensions = explode(',', $mysql->get_setting('allowed_extensions'));
	$allowed_detected = false;
	foreach($allowed_extensions as $allowed) {
		$ext_length = strlen($allowed);
		if(substr($extension, ($ext_length - ($ext_length*2))) == $allowed) {
			$allowed_detected = true;
			break;
		}
	}
	if($allowed_detected == false) {
		die('e|'.implode(', ',$allowed_extensions));
	}
}


// Check file size
$max_size = $mysql->get_setting('file_size_limit');
if($max_size != '0') {
	$filesize = filesize($_FILES['file']['tmp_name']) / 1048576;
	if($filesize > $max_size)
		die("m|$max_size");
}


// Fake filecode
$file_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);
while($mysql->check_existing_filecode($file_code) === true)
	$file_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);

// Fake stats code
$stats_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);
while($mysql->check_existing_filecode($stats_code) === true)
	$stats_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);

// Fake download code
$ext = pathinfo(basename($_FILES['file']['name']), PATHINFO_EXTENSION);
$download_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);
$fake_name = $download_code.".$ext";
while($mysql->check_existing_filecode($download_code) === true || file_exists("uploads/$fake_name") === true)
	$download_code = $filex->create_fake_name('abcdefghijklmnopqrstuvwxyz0123456789', 25);
$fake_name = $download_code.".$ext";


// Get file size
$file_name = basename($_FILES['file']['name']);
$target_dir = 'uploads/';
$target_file = "uploads/$fake_name";

$date_uploaded = date('Y-m-d H:i:s');
$uploader_ip = $_SERVER['REMOTE_ADDR'];


// Try to save file on database
$save_try = $mysql->save_file(array(
	$file_name,
	$file_code,
	$stats_code,
	$download_code,
	$ext,
	$date_uploaded,
	$uploader_ip,
	0,
	0,
	$days,
	$downloads,
	$password_protected,
	$password_protection,
	0,
	0,
	1
));
if($save_try == false)
	die('no');

// All good, move file to its new location
$move = @move_uploaded_file($_FILES['file']['tmp_name'], $target_file);

// Something's wrong.. Remove MySQL entry and return error
if($move == false) {
	$mysql->delete_file_entry($file_code, $ext);
	die('no');
}

// Everything's good. Return file code to redirect
die("1|$file_code");

?>