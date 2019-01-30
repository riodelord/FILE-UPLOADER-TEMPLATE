<?php
require 'config.php';
$status = 1;

if($dbhost == 'YOUR MYSQL HOST HERE' && $dbuser == 'YOUR MYSQL USER HERE' && $dbpass == 'YOUR MYSQL PASS HERE' && $dbname == 'YOUR MYSQL DATABASE NAME HERE')
	$status = 2;

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if(mysqli_connect_errno())
	$status = 3;

// First step
if(!isset($_POST['act']) && !isset($_GET['p']) && $_GET['p'] != 2) {
	$queries = "CREATE TABLE `filex_files`(`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`),
	`filename` TEXT NOT NULL, `filecode` VARCHAR(25) NOT NULL, `statscode` VARCHAR(25) NOT NULL,
	`downloadcode` VARCHAR(25) NOT NULL, `fileextension` TEXT NOT NULL,
	`date_uploaded` DATETIME NOT NULL, `uploader_ip` VARCHAR(45) NOT NULL,
	`downloads` INT NOT NULL DEFAULT 0, `link_visits` INT NOT NULL DEFAULT 0,
	`days_expiration` INT NOT NULL DEFAULT 0, `downloads_expiration` INT NOT NULL DEFAULT 0,
	`password_protected` INT NOT NULL DEFAULT 0, `password_protection` VARCHAR(32) NOT NULL DEFAULT 0,
	`wrong_password_attempts` INT NOT NULL DEFAULT 0, `correct_password_attempts` INT NOT NULL DEFAULT 0,
	`status` INT NOT NULL DEFAULT 1);";
	$queries .= "CREATE TABLE `filex_visits`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `fileid` INT NOT NULL, `date` DATETIME NOT NULL);";
	$queries .= "CREATE TABLE `filex_password_attempts`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `fileid` INT NOT NULL, `date` DATETIME NOT NULL, `type` INT(1) NOT NULL);";
	$queries .= "CREATE TABLE `filex_downloads`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `fileid` INT NOT NULL, `date` DATETIME NOT NULL, `ip` VARCHAR(45) NOT NULL);";
	$queries .= "CREATE TABLE `filex_expiration`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `fileid` INT NOT NULL, `date` DATETIME NOT NULL);";
	$queries .= "CREATE TABLE `filex_blocked`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `date` DATETIME NOT NULL, `ip` VARCHAR(45) NOT NULL, `enter_attempts` INT DEFAULT 0);";
	$queries .= "ALTER TABLE `filex_blocked` ADD UNIQUE KEY `ip`(`ip`);";
	$queries .= "CREATE TABLE `filex_settings`(`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(`id`), `setting` VARCHAR(40) NOT NULL, `val` TEXT NOT NULL);";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('site_logo','media/img/filex@3x.png');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('allow_stats','1');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('allow_ads','0');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('ads_code','');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('allowed_extensions','jpeg,jpg,png,gif,psd,txt,mp3,mp4,zip,ico,pdf,wma,flv,avi,xls,docx,apk');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('file_size_limit','0');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('site_color','style-blue.css');";
	$queries .= "INSERT INTO `filex_settings`(`setting`,`val`) VALUES('footer_info','<p>Filex - All rights reserved</p>');";
	
	if(!$mysqli->multi_query($queries))
		$status = 4;
	else {
		header('Location: install.php?p=2');
	}
}

if(isset($_GET['p']) && $_GET['p'] == 3) {
	$status = 5;
	
	$query = "SELECT `val` FROM filex_settings WHERE `setting`='site_url'";
	$res = $mysqli->query($query);
	$obj = $res->fetch_object();
	
	$site_url = $obj->val;
	$admin_panel_url = $site_url.'admin/';
}

if(isset($_POST['act']) && $_POST['act'] == '1') {
	if(!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['site_title']) || !isset($_POST['site_url']))
		die('n');
		
	$user = $_POST['username'];
	$pass = $_POST['password'];
	$site_title = $_POST['site_title'];
	$site_url = $_POST['site_url'];
	
	if(strlen($user) < 5 || strlen($pass) < 5 || $site_title == '' || $site_url == '')
		die('n');
	
	$pass = md5($pass);
	
	$prep1 = $mysqli->prepare("INSERT INTO `filex_settings`(`setting`,`val`) VALUES('admin_user',?)");
	$prep2 = $mysqli->prepare("INSERT INTO `filex_settings`(`setting`,`val`) VALUES('admin_pass',?)");
	$prep3 = $mysqli->prepare("INSERT INTO `filex_settings`(`setting`,`val`) VALUES('site_title',?)");
	$prep4 = $mysqli->prepare("INSERT INTO `filex_settings`(`setting`,`val`) VALUES('site_url',?)");
	
	$prep1->bind_param('s', $user);
	$prep2->bind_param('s', $pass);
	$prep3->bind_param('s', $site_title);
	$prep4->bind_param('s', $site_url);
	
	if(!$prep1->execute() || !$prep2->execute() || !$prep3->execute() || !$prep4->execute())
		die('n');
	
	die('1');
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" />
	
	<title>Filex - Installation</title>
	
	<!-- Load Bootstrap -->
	<link rel="stylesheet" href="media/bootstrap/css/bootstrap.min.css" />
	
	<!-- Load Custom Style -->
	<link rel="stylesheet" href="admin/media/css/style.css" />
	<link rel="stylesheet" href="media/css/install.css" />
	
	<!-- Google Fonts -->
	<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700|Roboto:400,300,100' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Quicksand:300,400,700' rel='stylesheet' type='text/css'>
	
	<!-- FontAwesome Icons -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	
	<!-- HTML5 Shiv and Respond.js for IE8 -just in case- -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand"><img src="admin/media/img/filex@1x.png" srcset="admin/media/img/filex@1x.png 1x, admin/media/img/filex@2x.png 2x, admin/media/img/filex@3x.png 3x" alt="Filex - Admin Panel" /></a>
			</div>
		</div>
	</nav>
	
	<div class="general-wrapper">
		<div class="content">
		
			<div class="row" id="header">
				<div class="col col-md-12">
					<div class="text">Filex Installation</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-12" id="file-settings">
					<div class="cont">
						<?php
						if($status == 2) {
							echo '<p class="bg-danger margin-top">Please open the config.php file and edit it using your MySQL Information. Read the documentation for
							details.</p>';
						}elseif($status == 3) {
							echo '<p class="bg-danger margin-top">Filex couldn\'t connect to your MySQL Server. Please make sure
							you modified the config.php file and that you used the correct details.</p>';
						}elseif($status == 4) {
							echo '<p class="bg-danger margin-top">Filex couldn\'t create your MySQL Tables. To do a manual installation,
							please contact me at support@sglancer.com</p>';
						}elseif($status == 5) {
							echo '<p class="bg-success margin-top">Filex has been successfully installed! Below are the links you\'ll need. Please
							save them and delete this file:<br /><br />';
							echo '<strong>Site URL:</strong> <a href="'.$site_url.'">'.$site_url.'</a><br />';
							echo '<strong>Admin Panel URL:</strong> <a href="'.$admin_panel_url.'">'.$admin_panel_url.'</a>';
							echo '</p>';
						}else{
						?>
						<p class="info">
							Hey! The MySQL Tables have just been installed, we are very close to have Filex fully installed!
						</p>
						<p class="info">
							For this last step, please fill the following boxes:
						</p>
						<p class="bg-danger margin-top" style="display:none"></p>
						
						<div class="row clearfix">
							<form name="install">
								<div class="col-md-12">
									<div class="form-group">
										<label for="username">Username <span class="small">(you will use this to log into your admin panel)</span></label>
										<input type="text" class="form-control" id="username" name="username" />
									</div>
									<div class="form-group">
										<label for="password">Password</label>
										<input type="password" class="form-control" id="password" name="password" />
									</div>
									<div class="form-group">
										<label for="site-title">Site Title <span class="small">(Title you want for your site)</span></label>
										<input type="text" class="form-control" id="site-title" name="site-title" placeholder="Filex" />
									</div>
									<div class="form-group">
										<label for="site-url">Site URL <span class="small">(URL where Filex is going to be installed.)</span></label>
										<input type="text" class="form-control" id="site-url" name="site-url" placeholder="http://yoursite.com/" />
									</div>
									
									<button type="submit" class="btn btn-primary pull-right">Install</button>
								</div>
							</form>
						</div>
						<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
	<!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="media/bootstrap/js/bootstrap.min.js"></script>
	<script src="media/js/flot/jquery.flot.min.js"></script>
	<script>
		$('document').ready(function() {
			$('form[name=install]').submit(function(evt) {
				evt.preventDefault();
				
				// Reset border colors
				$('input[name=username]').css('border-color','#ccc');
				$('input[name=password]').css('border-color','#ccc');
				$('input[name=site-title]').css('border-color','#ccc');
				$('input[name=site-url]').css('border-color','#ccc');
				$('p.bg-danger').slideUp(250);
				
				var username = $('input[name=username]').val();
				var password = $('input[name=password]').val();
				var site_title = $('input[name=site-title]').val();
				var site_url = $('input[name=site-url]').val();
				
				if(username == '') {
					$('input[name=username]').css('border-color','#FF0000');
					return false;
				}
				if(password == '') {
					$('input[name=password]').css('border-color','#FF0000');
					return false;
				}
				if(site_title == '') {
					$('input[name=site-title]').css('border-color','#FF0000');
					return false;
				}
				if(site_url == '') {
					$('input[name=site-url]').css('border-color','#FF0000');
					return false;
				}
				if(username.length < 5) {
					$('input[name=username]').css('border-color','#FF0000');
					$('p.bg-danger').html('Username should be at least 5 characters long').slideDown(400);
					scrollToElement($('.col#file-settings'), 250);
					return false;
				}
				if(password.length < 5) {
					$('input[name=password]').css('border-color','#FF0000');
					$('p.bg-danger').html('Password should be at least 5 characters long').slideDown(400);
					scrollToElement($('.col#file-settings'), 250);
					return false;
				}
				
				var pattern = new RegExp('^(https?:\\/\\/)?((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|((\\d{1,3}\\.){3}\\d{1,3}))(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*(\\?[;&a-z\\d%_.~+=-]*)?(\\#[-a-z\\d_]*)?/$','i');
				if(!pattern.test(site_url)) {
					$('input[name=site-url]').css('border-color','#FF0000');
					$('p.bg-danger').html('Please enter a valid site URL. Example: http://yoursite.com/<br />(Make sure you start with <strong>http://</strong> and end with a slash (/)').slideDown(400);
					scrollToElement($('.col#file-settings'), 250);
					return false;
				}
  
				
				$.post('install.php', {
					'act':1,
					'username':username,
					'password':password,
					'site_title':site_title,
					'site_url':site_url
				},function(data) {
					if(data == '1') {
						location.href = 'install.php?p=3';
					}else{
						$('p.bg-danger').html('Filex couldn\'t be successfully installed. Please contact me at support@sglancer.com for support.').slideDown(400);
						scrollToElement($('.col#file-settings'), 250);
					}
				});
			});
		});
	</script>
</body>
</html>