<?php
require 'config.php';
require 'inc/mysql.class.php';
require 'inc/filex.class.php';

if($mysql->get_setting('allow_stats') == '0') {
	header('Location: index.php');
	die();
}

if($mysql->is_banned($_SERVER['REMOTE_ADDR'])) {
	$mysql->add_enter_attempt($_SERVER['REMOTE_ADDR']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('<h2><center>Oops! You have been banned from this site.</center></h2>');
}

$site = $mysql->get_setting('site_url');

$e = 0;
if(isset($_POST['stats-code-submit'])) {
	if(!isset($_POST['stats-code']))
		$e = 1;
	else{
		$statscode = $_POST['stats-code'];
		if($statscode == '')
			$e = 1;
		else{
			if($mysql->check_existing_statscode($statscode) === true)
				header("Location: {$site}stats/{$statscode}/");
			else
				$e = 2;
		}
	}
}

$_pageheader = 2;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<!-- Sixth Container -->
	<!-- Check Stats Container -->
	<section class="container check-stats">
		<p class="big">Check Stats</p>
		<p class="medium">Enter your stats code to check your file statistics</p>
		<?php
		if($e == 1)
			echo '<p class="bg-danger">Please insert your stats code</p>';
		elseif($e == 2)
			echo '<p class="bg-danger">Submitted stats code doesn\'t exist</p>';
		?>
		<form method="post" action="">
			<input type="text" name="stats-code" class="form-control" />
			<button type="submit" name="stats-code-submit" class="btn btn-success pull-right">Check Stats</button>
		</form>
	</section>
	
	<!-- Third Container / Information -->
	<section class="container-fluid information">
		<div class="container">
			<div class="row">
				<div class="col-xs-4 column text-center">
					1250 Uploaded Files
				</div>
				<div class="col-xs-4 column text-center">
					300 Downloads
				</div>
				<div class="col-xs-4 column text-center">
					245 Expired Files
				</div>
			</div>
		</div>
	</section>
	
	<section class="ads text-center">
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Filex Example (Responsive) -->
		<ins class="adsbygoogle"
		style="display:block; max-width:800px; margin:auto;"
		data-ad-client="ca-pub-7831419112869734"
		data-ad-slot="5874515355"
		data-ad-format="auto"></ins>
		<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	</section>
	
	<!-- Footer -->
	<footer class="container-fluid text-center">
		<p>RANDOM FOOTER INFORMATION GOES HERE</p>
	</footer>
	
	
	<!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="media/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>