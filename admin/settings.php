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

if(isset($_POST['act'])) {
	// Update general settings
	if($_POST['act'] == '1') {
		if(!isset($_POST['site_title']) || !isset($_POST['site_logo']) || !isset($_POST['allow_stats']) || !isset($_POST['enable_ads']) || !isset($_POST['ads_code']) || !isset($_POST['footer_info']))
			die('n');
		
		$settings = array(
			'site_title' => $_POST['site_title'],
			'site_logo' => $_POST['site_logo'],
			'allow_stats' => $_POST['allow_stats'],
			'allow_ads' => $_POST['enable_ads'],
			'ads_code' => $_POST['ads_code'],
			'footer_info' => $_POST['footer_info']
		);

		foreach($settings as $setting => $val)
			$mysql->update_setting($setting, $val);
		
		die('1');
	}
	
	// Update file settings
	if($_POST['act'] == '2') {
		if(!isset($_POST['allowed_extensions']) || !isset($_POST['file_size_limit']))
			die('n');
		
		if(!is_numeric($_POST['file_size_limit']))
			die('n');
		
		$allowed_extensions = str_replace(' ', '', $_POST['allowed_extensions']);
		$file_size_limit = $_POST['file_size_limit'];
		
		$mysql->update_setting('allowed_extensions', $allowed_extensions);
		$mysql->update_setting('file_size_limit', $file_size_limit);
		
		die('1');
	}
	
	// Update account settings
	if($_POST['act'] == '3') {
		if(!isset($_POST['user']) || !isset($_POST['change_pass']) || !isset($_POST['pass1']) || !isset($_POST['pass2']))
			die('n');
		
		if(strlen($_POST['user']) < 5)
			die('n');
		
		if($_POST['change_pass'] == '1') {
			$p1 = $_POST['pass1'];
			$p2 = $_POST['pass2'];
			if($p1 == '' || $p2 == '' || $p1 != $p2 || strlen($p1) < 5)
				die('n');
			
			$mysql->update_setting('admin_pass', md5($p1));
			$filex->update_session_pass(md5($p1));
		}
		
		$mysql->update_setting('admin_user', $_POST['user']);
		$filex->update_session_user($_POST['user']);
		
		die('1');
	}
}

$_page = 4;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Settings</h2>
			
			<div class="row">
				<div class="col col-md-12" id="settings">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">General Settings</span>
						</div>
						
						<p class="bg-danger margin-top" style="display:none"></p>
						<p class="bg-success margin-top" style="display:none"></p>
						
						<div class="row clearfix">
							<form name="general-settings">
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="site-title">Site Title</label>
										<input type="text" class="form-control" id="site-title" name="site-title" value="<?php echo $mysql->get_setting('site_title'); ?>" placeholder="Site Title" />
									</div>
									
									<div class="form-group">
										<label for="site-logo-url">Site Logo URL</label>
										<input type="text" class="form-control" id="site-logo-url" name="site-logo-url" value="<?php echo $mysql->get_setting('site_logo'); ?>" placeholder="http://example.com/logo.png" />
									</div>
									
									<div class="form-group">
										<label for="allow-stats">Allow Stats</label>
										<?php
										$allow_stats = $mysql->get_setting('allow_stats');
										if($allow_stats == '0') {
											$asa = ' checked';
											$asb = '';
										}else{
											$asa = '';
											$asb = ' checked';
										}
										?>
										<div class="radio">
											<label>
												<input type="radio" name="allow-stats" id="allow-stats" value="0"<?php echo $asa; ?> />Disable
											</label>
										</div>
										<div class="radio">
											<label>
												<input type="radio" name="allow-stats" id="allow-stats" value="1"<?php echo $asb; ?>/>Enable
											</label>
										</div>
									</div>
								</div>
								
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="ads-code">Ads Code</label>
										<div class="checkbox">
											<label>
												<?php
												$ads = $mysql->get_setting('ads_code');
												if($ads != '') {
													$adsextra = ' checked';
													$adsextratexta = '';
												}else{
													$adsextra = '';
													$adsextratexta = ' disabled';
												}
												?>
												<input type="checkbox" name="enable-ads-code"<?php echo $adsextra; ?> />Enable Ads
											</label>
										</div>
										
										<textarea class="form-control" rows="8" name="ads-code" placeholder="Your ad code goes here..."<?php echo $adsextratexta; ?>><?php echo $ads ?></textarea>
									</div>

									<div class="form-group">
										<label for="footer-info">Footer Information</label>
										<textarea class="form-control" rows="5" id="footer-info" name="footer-info" placeholder="Write footer information here..."><?php echo $mysql->get_setting('footer_info'); ?></textarea>
									</div>
									
									<button type="submit" class="btn btn-primary pull-right">Submit</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-12" id="file-settings">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">Files Settings</span>
						</div>
						
						<p class="bg-danger margin-top" style="display:none"></p>
						<p class="bg-success margin-top" style="display:none"></p>
						
						<div class="row clearfix">
							<form name="file-settings">
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="allowed-extensions">Allowed Extensions (leave empty to allow any extension)</label>
										<input type="text" class="form-control" name="allowed-extensions" id="allowed-extensions" value="<?php echo $mysql->get_setting('allowed_extensions'); ?>" placeholder="Extensions separated by comma. E.g. jpg,png,txt,exe" />
									</div>
								</div>
								
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="file-size-limit">File Size Limit (in MB - leave empty to delete limit)</label>
										<input type="text" class="form-control" name="file-size-limit" id="file-size-limit" value="<?php echo $mysql->get_setting('file_size_limit'); ?>" placeholder="File size limit in MB" />
									</div>
									
									<button type="submit" class="btn btn-primary pull-right">Submit</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-12" id="account-settings">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">Account Settings</span>
						</div>
						
						<p class="bg-danger margin-top" style="display:none"></p>
						<p class="bg-success margin-top" style="display:none"></p>
						
						<div class="row clearfix">
							<form name="account-settings">
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="username">Username</label>
										<input type="text" class="form-control" name="username" value="<?php echo $mysql->get_setting('admin_user'); ?>" id="username" placeholder="Username to log in" />
									</div>
								</div>
								
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="password1">Change Password (leave empty to keep current password)</label>
										<input type="password" class="form-control" name="password1" id="password1" />
									</div>
									
									<div class="form-group">
										<label for="password2">Repeat Password</label>
										<input type="password" class="form-control" name="password2" id="password2" />
									</div>
									
									<button type="submit" class="btn btn-primary pull-right">Submit</button>
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
			
			$('input[type=checkbox][name=enable-ads-code]').on('change', function() {
				if($(this).is(':checked')) {
					$('textarea[name=ads-code]').prop('disabled',false);
				}else{
					$('textarea[name=ads-code]').prop('disabled',true);
				}
			});
			
			function scrollToElement(element, time) {
				$('html,body').animate({
					scrollTop: element.position().top
				}, time);
			}
			
			$('form[name=account-settings]').submit(function(evt) {
				evt.preventDefault();
				$('p.bg-success, p.bg-danger').slideUp(250);
				// Reset border colors
				$('input[name=username]').css('border-color','#ccc');
				$('input[name=password1]').css('border-color','#ccc');
				$('input[name=password2]').css('border-color','#ccc');
				
				var user = $('input[name=username]').val();
				var p1 = $('input[name=password1]').val();
				var p2 = $('input[name=password2]').val();
				var change_pass = 0;
				
				if(user == '') {
					$('input[name=username]').css('border-color','#FF0000');
					scrollToElement($('.col#account-settings'), 250);
					return false;
				}
				
				if(user.length < 5) {
					$('input[name=username]').css('border-color','#FF0000');
					$('.col#account-settings').children('.cont').children('p.bg-danger').html('Username must be at least 5 characters long').slideDown(400);
					scrollToElement($('.col#account-settings'), 250);
					return false;
				}
				
				if(p1 != '' || p2 != '') {
					if(p1 == '') {
						$('input[name=password1]').css('border-color','#FF0000');
						scrollToElement($('.col#account-settings'), 250);
						return false;
					}
					if(p2 == '') {
						$('input[name=password2]').css('border-color','#FF0000');
						scrollToElement($('.col#account-settings'), 250);
						return false;
					}
					if(p1 != p2) {
						$('input[name=password1]').css('border-color','#FF0000');
						$('input[name=password2]').css('border-color','#FF0000');
						$('.col#account-settings').children('.cont').children('p.bg-danger').html('Both passwords must match').slideDown(400);
						scrollToElement($('.col#account-settings'), 250);
						return false;
					}
					if(p1.length < 5) {
						$('input[name=password1]').css('border-color','#FF0000');
						$('input[name=password2]').css('border-color','#FF0000');
						$('.col#account-settings').children('.cont').children('p.bg-danger').html('Password must be at least 5 characters long').slideDown(400);
						scrollToElement($('.col#account-settings'), 250);
						return false;
					}
					change_pass = 1;
				}
				
				$.post('settings.php', {
					act:3,
					user: user,
					change_pass: change_pass,
					pass1: p1,
					pass2: p2
				}, function(data) {
					if(data == '1') {
						$('p.bg-success, p.bg-danger').slideUp(250);
						$('.col#account-settings').children('.cont').children('p.bg-success').html('Changes successfully made!').slideDown(400);
						scrollToElement($('.col#account-settings'), 250);
					}else{
						$('p.bg-success, p.bg-danger').slideUp(250);
						$('.col#account-settings').children('.cont').children('p.bg-danger').html('Changes couldn\'t be made, please try again.').slideDown(400);
						scrollToElement($('.col#account-settings'), 250);
					}
				});
			});

			$('form[name=file-settings]').submit(function(evt) {
				evt.preventDefault();
				$('p.bg-success, p.bg-danger').slideUp(250);
				// Reset border colors
				$('input[name=allowed-extensions]').css('border-color','#ccc');
				$('input[name=file-size-limit]').css('border-color','#ccc');
				
				var allowed_extensions = $('input[name=allowed-extensions]').val();
				var file_size_limit = $('input[name=file-size-limit]').val()
				
				if(file_size_limit != '') {
					var reg = /^\d+$/;
					if(reg.test(file_size_limit) == false) {
						$('input[name=file-size-limit]').css('border-color','#FF0000');
						$('.col#file-settings').children('.cont').children('p.bg-danger').html('Only numeric (integer) values are allowed.').slideDown(400);
						scrollToElement($('.col#file-settings'), 250);
						return false;
					}
				}
				
				$.post('settings.php', {
					act:2,
					allowed_extensions: allowed_extensions,
					file_size_limit: file_size_limit
				}, function(data) {
					if(data == '1') {
						$('.col#file-settings').children('.cont').children('p.bg-success').html('Changes successfully made!').slideDown(400);
						scrollToElement($('.col#file-settings'), 250);
					}else{
						$('.col#file-settings').children('.cont').children('p.bg-danger').html('Changes couldn\'t be made, please try again.').slideDown(400);
						scrollToElement($('.col#file-settings'), 250);
					}
				});
			});
			
			$('form[name=general-settings]').submit(function(evt) {
				evt.preventDefault();
				$('p.bg-success, p.bg-danger').slideUp(250);
				// Reset border colors
				$('input[name=site-title]').css('border-color','#ccc');
				$('input[name=site-logo-url]').css('border-color','#ccc');
				
				
				var site_title = $('input[name=site-title]').val();
				var site_logo =$('input[name=site-logo-url]').val();
				var allow_stats = $('input[name=allow-stats]:checked').val();
				var enable_ads = ($('input[name=enable-ads-code]').is(':checked')) ? 1 : 0;
				var ads_code = $('textarea[name=ads-code]').val();
				var footer_info = $('textarea[name=footer-info]').val();
				
				if(site_title == '') {
					$('input[name=site-title]').css('border-color','#FF0000');
					scrollToElement($('input[name=site-title]'), 300);
					return false;
				}
				
				if(site_logo == '') {
					$('input[name=site-logo-url]').css('border-color','#FF0000');
					scrollToElement($('input[name=site-logo-url]'), 300);
					return false;
				}
				
				$.post('settings.php', {
					act:1,
					site_title:site_title,
					site_logo:site_logo,
					allow_stats:allow_stats,
					enable_ads:enable_ads,
					ads_code:ads_code,
					footer_info:footer_info
				}, function(data) {
					if(data == '1') {
						$('.col#settings').children('.cont').children('p.bg-success').html('Changes successfully made!').slideDown(400);
						scrollToElement($('.col#settings'), 250);
					}else{
						$('.col#settings').children('.cont').children('p.bg-danger').html('Changes couldn\'t be made, please try again.').slideDown(400);
						scrollToElement($('.col#settings'), 250);
					}
				});
			});
		});
	</script>
</body>
</html>