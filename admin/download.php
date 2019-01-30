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

if(!isset($_GET['filecode']))
	header('Location: uploaded_files.php');

$error = false;
$filecode = $_GET['filecode'];

// Check if file exists in MySQL
if($mysql->file_mysql_exist($filecode) == true) {
	// Check if file exists in server
	$fileinfo = $mysql->get_file($filecode);
	
	$file = "../uploads/{$fileinfo->downloadcode}.{$fileinfo->fileextension}";
	$filename = "{$fileinfo->downloadcode}.{$fileinfo->fileextension}";
	if(file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$fileinfo->filename);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));

		readfile($file);
	}else{
		$error = "This file doesn't exist in the server";
	}
}else{
	$error = "This file doesn't exist in the database";
}

if($error != false) {
	$_page = 1;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Download File</h2>
			
			<div class="row">
				<div class="col col-xs-12" id="uploaded-files">
					<div class="cont clearfix" style="text-align:center">
						<h4><?php echo $error; ?></h4>
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
			$('button.navbar-toggle').click(function(evt) {
				evt.preventDefault();
				evt.stopPropagation();
				
				if($('.sidebar-left').hasClass('shown'))
					$('.sidebar-left').removeClass('shown');
				else
					$('.sidebar-left').addClass('shown');
			});
			
			$('.content').click(function() {
				if($('.sidebar-left').hasClass('shown'))
					$('.sidebar-left').removeClass('shown');
			});
		});
	</script>
</body>
</html>
<?php
}
?>