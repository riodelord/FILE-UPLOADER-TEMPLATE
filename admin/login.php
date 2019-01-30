<?php
require '../config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

if($filex->isLogged($mysql) == true) {
	header('Location:index.php');
	die();
}

if(isset($_POST['act']) && $_POST['act'] == '1') {
	if(!isset($_POST['user']) || !isset($_POST['pass']))
		die('3');
	$user = $_POST['user'];
	$pass = md5($_POST['pass']);
	
	if($filex->login($mysql, $user, $pass) == true)
		die('1');
	else
		die('2');
	die('3');
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" />
	
	<title>Filex - Login</title>
	
	<!-- Load Bootstrap -->
	<link rel="stylesheet" href="media/bootstrap/css/bootstrap.min.css" />
	
	<!-- Load Custom Style -->
	<link rel="stylesheet" href="media/css/style.css" />
	<link rel="stylesheet" href="media/css/login.css" />
	
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
				<a class="navbar-brand"><img scr="media/img/filex@1x.png" srcset="media/img/filex@1x.png 1x, media/img/filex@2x.png 2x, media/img/filex@3x.png 3x" alt="Filex - Admin Panel" /></a>
			</div>
		</div>
	</nav>
	
	<div class="general-wrapper">
		<div class="content">
		
			<div class="row" id="header">
				<div class="col col-md-12">
					<div class="text">Login</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-12" id="file-settings">
					<div class="cont">
						
						<p class="bg-danger margin-top" style="display:none"></p>
						
						<div class="row clearfix">
							<form name="login">
								<div class="col-md-12">
									<div class="form-group">
										<label for="username">Username</label>
										<input type="text" class="form-control" id="username" name="username" />
									</div>
									<div class="form-group">
										<label for="password">Password</label>
										<input type="password" class="form-control" id="password" name="password" />
									</div>
									
									<button type="submit" class="btn btn-primary pull-right">Login</button>
								</div>
								</div>
							</form>
						</div>
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
			$('form[name=login]').submit(function(evt) {
				evt.preventDefault();
				
				// Reset border colors
				$('input[name=username]').css('border-color','#ccc');
				$('input[name=password]').css('border-color','#ccc');
				$('p.bg-danger').slideUp(250);
				
				var user = $('input[name=username]').val();
				var pass = $('input[name=password]').val();
				
				if(user == '') {
					$('input[name=username]').css('border-color','#FF0000');
					return false;
				}
				
				if(pass == '') {
					$('input[name=password]').css('border-color','#FF0000');
					return false;
				}
				
				$.post('login.php', {
					'act':1,
					'user':user,
					'pass':pass
				}, function(data) {
					if(data == '1')
						location.href = 'index.php';
					else if(data == '2')
						$('p.bg-danger').html('Wrong username or password').slideDown(250);
					else
						$('p.bg-danger').html('Couldn\'t log in. Please try again later').slideDown(250);
				});
			});
		});
	</script>
</body>
</html>