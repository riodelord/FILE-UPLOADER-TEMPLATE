<?php
require 'config.php';
require 'inc/mysql.class.php';

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

$_pageheader = 1;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<!-- First Container -->
	<section class="container first-container">
		<div class="row">
			<label for="fileinput" class="over-container"></label>
			<div class="col-xs-4 left-icon"> 
				<i class="fa fa-cloud-upload"></i>
			</div>
			<div class="col-xs-8 right-text">
				<p>Click or drop here to upload a new file</p>
			</div>
		</div>
		
		<input type="file" name="fileinput" class="hide" id="fileinput" />
	</section>
	
	<!-- Second Container -->
	<section class="container-fluid second-container">
		<div class="container">
			<form method="post" action="index.php" name="upload">
				<div class="row">
					<div class="col-xs-4 column">
						<div class="head text-center">
							<p class="form-inline">Delete files after <input type="text" name="days" class="form-control" style="width:40px;" /> days</p>
						</div>
					</div>
					
					<div class="col-xs-4 column">
						<div class="head text-center">
							<p class="form-inline">Delete files after <input type="text" name="downloads" class="form-control" style="width:40px;" /> downloads</p>
						</div>
					</div>
					
					<div class="col-xs-4 column">
						<div class="head text-center">
							<p>Protect files with the following password:</p>
							<input type="text" name="password" class="form-control big" />
						</div>
					</div>
				</div>
				
				<button type="submit" class="btn btn-success pull-right upload">UPLOAD</button>
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
	<script>
		<?php
		$extensions = $mysql->get_setting('allowed_extensions');
		if($extensions == '')
			echo 'var all_extensions_allowed = true;';
		else
			echo 'var all_extensions_allowed = false;';
			
		echo "\r\n		";
		
		$extensions = implode("','", explode(',', $extensions));
		$extensions = "['$extensions'];";
		echo 'var allowed_extensions = '.$extensions;
		?>
	
	</script>
	<script src="media/js/filex.init.js"></script>
</body>
</html>