<?php
require 'config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if(!isset($_GET['error'])) {
	$title = 'Oooops!';
	$cont = "Apparently something went wrong... We can't figure the issue right now, but you should go back and try again :)";
}else{
	$e = $_GET['error'];
	if($e == 1) {
		$title = "File doesn't exist";
		$cont = "Unfortunately, this file doesn't exist in our database";
	}elseif($e == 2) {
		$title = "File doesn't exist";
		$cont = "Unfortunately, this file doesn't exist in our server";
	}elseif($e == 3) {
		$title = "File has expired";
		$cont = "Unfortunately, this file has expired";
	}elseif($e == 404) {
		$title = "404";
		$cont = "Hey! What you're trying to find isn't here";
	}else{
		$title = 'Oooops!';
		$cont = "Apparently something went wrong... We can't figure the issue right now, but you should go back and try again :)";
	}
}
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<!-- Seventh Container -->
	<section class="container eighth-container">
		<div class="row">
			<p class="big"><?php echo $title; ?></p>
			<p class="medium"><?php echo $cont; ?></p>
		</div>
	</section>
	
	<!-- Third Container / Information -->
	<section class="container-fluid information">
		<div class="container">
			<div class="row">
				<div class="col-xs-4 column text-center">
					<?php
					$uploaded = $mysql->get_uploaded_files();
					if($uploaded == 0)
						echo 'No';
					else
						echo $uploaded;
					?> Uploaded Files
				</div>
				<div class="col-xs-4 column text-center">
					<?php
					$downloads = $mysql->get_downloads();
					if($downloads == 0)
						echo 'No';
					else
						echo $downloads;
					?> Downloads
				</div>
				<div class="col-xs-4 column text-center">
					<?php
					$expired = $mysql->get_expired_files();
					if($expired == 0)
						echo 'No';
					else
						echo $expired;
					?> Expired Files
				</div>
			</div>
		</div>
	</section>
	
<?php
	if($mysql->get_setting('allow_ads') == '1') {
		$adscode = $mysql->get_setting('ads_code');
?>
	<section class="ads text-center">
		<?php echo $adscode; ?>
	</section>
<?php
	}
?>
	
	<!-- Footer -->
	<footer class="container-fluid text-center">
		<?php echo $mysql->get_setting('footer_info'); ?>
	</footer>
	
	
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="media/bootstrap/js/bootstrap.min.js"></script>
	<script src="media/js/filex.init.js"></script>
</body>
</html>