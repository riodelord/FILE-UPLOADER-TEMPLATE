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

// Check MySQL Existance of the file
if(!isset($_GET['filecode']))
	header("Location: {$site}index.php");

$filecode = $_GET['filecode'];
if($mysql->check_existing_file($filecode) === false)
	header("Location: {$site}error/1/"); // Error 1 = File doesn't exist in our databases

// File exists in MySQL, get data and check file existance in server
$filedata = $mysql->get_file($filecode);
if(!is_object($filedata) && $filedata == 0)
	die('Something is wrong while trying to retrieve your file.');
	
if(!file_exists('uploads/'.$filedata->downloadcode.'.'.$filedata->fileextension))
	header("Location: {$site}error/2/"); // Error 2 = File doesn't exist in our servers

$siteurl = $mysql->get_setting('site_url');
$filecode = $filedata->filecode;
$statscode = $filedata->statscode;

$download_url = $siteurl.'download/'.$filecode;
$stats_url = $siteurl.'stats/'.$statscode;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<!-- Fourth Container -->
	<section class="container fourth-container">
		<div class="success text-center">
			<i class="fa fa-check"></i>
			<p>File successfully uploaded!</p>
		</div>
		
		<div class="download animate-first">
			<span>DOWNLOAD URL</span>
			<div class="input">
				<input type="text" name="link" class="link" value="<?php echo $download_url; ?>" />
				<button type="submit" name="select-all"><i class="fa fa-link"></i>SELECT LINK</button>
			</div>
		</div>
		
		<div class="download animate-second">
			<span>STATS URL</span>
			<div class="input">
				<input type="text" name="link" class="link" value="<?php echo $stats_url; ?>" />
				<button type="submit" name="select-all"><i class="fa fa-link"></i>SELECT LINK</button>
			</div>
			<p class="small">
				Use this link to take a look at your file stats.
				Save this link in a safe place, as it cannot be retrieved again :)<br />
				<strong>Stats Code: <?php echo $statscode; ?></strong>
			</p>
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
	<script type="text/javascript">
		$('document').ready(function() {
			$('button[name=select-all]').click(function(evt) {
				evt.preventDefault();
				$(this).parent().children('input[name=link]').select();
			});
		});
	</script>
</body>
</html>