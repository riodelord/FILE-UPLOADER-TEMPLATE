<?php
if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<!-- Seventh Container -->
	<section class="container seventh-container">
		<div class="row">
			<p class="big">Password Protection</p>
			<p class="medium">This file is password protected. To download, please write the password</p>
			<?php
			if(isset($error)) {
				if($error == 1)
					echo '<p class="bg-danger">Please insert password</p>';
				elseif($error == 2)
					echo '<p class="bg-danger">Invalid password. Please try again</p>';
			}
			?>
			<form method="post" action="download/<?php echo $filecode ?>">
				<div class="input">
					<input type="password" name="password" class="password" placeholder="Password..." />
					<button type="submit" name="download">DOWNLOAD</button>
				</div>
			</form>
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