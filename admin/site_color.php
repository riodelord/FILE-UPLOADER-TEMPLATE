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

// Change color
if(isset($_POST['act']) && $_POST['act'] == '1') {
	if(isset($_POST['color'])) {
		$mysql->update_setting('site_color', $_POST['color']);
		die('1');
	}
	die('n');
}

$_page = 3;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Site Color</h2>
			<span class="page-desc">Select an image to change your site's color...</span>
			
			<div class="row">
				<div class="col col-xs-12" id="site-color">
					<div class="cont clearfix">
						<div class="row">
							<p class="bg-success margin-top" style="font-size:0.95em; margin:-10px 0 30px 0; display:none;"></p>
							<?php
							$colors = array('style-blue.css','style-orange.css','style-orange-blue.css','style-pink.css','style-pink-blue.css');
							$current_color = $mysql->get_setting('site_color');
							$c = 1;
							foreach($colors as $color) {
								if($c == 4 || $c == 5)
									$fix = ' col-md-20fix';
								else
									$fix = '';
									
								if($color == $current_color)
									echo '<div class="col col-xs-6 col-sm-4 col-md-20 selected'.$fix.'" data-color="'.$color.'">';
								else
									echo '<div class="col col-xs-6 col-sm-4 col-md-20'.$fix.'" data-color="'.$color.'">';
									
								echo '<div class="container-box">';
								echo '<img src="media/img/site-color-'.$c.'.png" />';
								echo '</div>';
								echo '</div>';
								
								$c += 1;
							}
							?>
							
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
			
			
			$('.col.col-xs-6').click(function() {
				var color = $(this).data('color');
				
				$.post('site_color.php', {
					act:1,
					color:color
				},function(data) {
					if(data == '1') {
						$('p.bg-success').html('Color successfully changed!').slideDown(300);
						$('.col.col-xs-6').removeClass('selected');
						$('div[data-color="'+color+'"]').addClass('selected');
					}else{
						alert("Color couldn't be changed, please try again later");
					}
				});
			});
		});
	</script>
</body>
</html>