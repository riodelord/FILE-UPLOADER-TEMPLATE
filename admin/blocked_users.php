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

$_page = 2;

if(!isset($_GET['p']) || $_GET['p'] == 0 || $_GET['p'] == 1)
	$ppage = 1;
else
	$ppage = $_GET['p'];
	
$record_start = (($ppage-1)*15);
$records = 15;
$total_records = $mysql->count_blocked_ips();
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Blocked Users</h2>
			
			<div class="row">
				<div class="col col-xs-12" id="head-buttons">
					<div class="cont clearfix">
						<div class="clearfix">
							<div class="pull-right">
								<button type="button" name="toggle" data-toggle="#ban-ip" class="btn btn-danger-light"><i class="fa fa-ban"></i>BAN NEW IP ADDRESS</button>
							</div>
						</div>

						<div id="ban-ip" class="pull-right margin-top" style="display:none; margin-top:20px;">
							<form name="ban-ip">
								<div class="form-inline">
									<div class="form-group">
										<input type="text" class="form-control" id="ip" placeholder="IP to ban" />
									</div>
									<button type="submit" class="btn btn-default btn-normal">Ban IP</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-xs-12" id="blocked-users">
					<div class="cont clearfix">
						<?php
						if($total_records == 0)
							echo '<div style="width:100%;text-align:center;"><h4>No banned users.</h4></div>';
						else{
						?>
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th width="12%">ID</th>
									<th width="22%">IP Address</th>
									<th width="22%">Banned since</th>
									<th width="22%">Enter attempts</th>
									<th width="22%">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$banned_users = $mysql->get_banned_list($record_start, $records);
								while($obj = $banned_users->fetch_object()) {
									echo '<tr data-id="'.$obj->id.'" data-ip="'.$obj->ip.'">';
									echo '<td>'.$obj->id.'</td>';
									echo '<td>'.$obj->ip.'</td>';
									echo '<td>'.date('M/d/Y', strtotime($obj->date)).'</td>';
									echo '<td>'.$obj->enter_attempts.'</td>';
									echo '<td>';
									echo '<a href="blocked_users.php" name="unban-ip" class="btn btn-success btn-small"><span>UNBAN IP</span><i class="fa fa-check"></i></a>';
									echo '</td>';
								}
								?>
							</tbody>
						</table>
						<?php
						}
						?>
						
						<?php
						if(($total_pages = ceil($total_records / $records)) == 0)
							$total_pages = 1;
						?>
						<div id="pagination" class="pull-right clearfix">
							<?php
							if($ppage != 1)
								echo '<div class="element" data-to="'.($ppage-1).'"><i class="fa fa-angle-left"></i></div>';
							else
								echo '<div class="element disable"><i class="fa fa-angle-left"></i></div>';
							
							if($total_pages <= 7) {
								$from = 1;
								$to = $total_pages;
							}else{
								if(($total_pages) == $ppage || ($total_pages-1) == $ppage || ($total_pages-2) == $ppage || ($total_pages-3) == $ppage) {
									$from = $total_pages - 6;
									$to = $total_pages;
								}elseif($ppage == 1 || $ppage == 2 || $ppage == 3 || $ppage == 4) {
									$from = 1;
									$to = 7;
								}else{
									$from = $ppage-3;
									$to = $ppage+4;
								}
							}
							for($i = $from; $i <= $to; $i++) {
								if($i == $ppage)
									echo '<div class="element select" data-to="'.$i.'">'.$i.'</div>';
								else
									echo '<div class="element" data-to="'.$i.'">'.$i.'</div>';
							}
							
							
							if($ppage < $total_pages)
								echo '<div class="element" data-to="'.($ppage+1).'"><i class="fa fa-angle-right"></i></div>';
							else
								echo '<div class="element disable"><i class="fa fa-angle-right"></i></div>';
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
			
			$('a[name=unban-ip]').click(function(evt) {
				evt.preventDefault();
				var t = $(this);
				var ip = $(this).parent().parent().data('ip');
				
				$.get('actions.php', {
					act:4,
					ip:ip
				}, function(data) {
					if(data == '1') {
						t.parent().parent().fadeOut(500);
					}else{
						alert("IP couldn't be unbanned, please try again later");
					}
				});
			});
			
			$('button[name=toggle]').click(function(evt) {
				evt.preventDefault();
				var t = $(this).data('toggle');
				
				$(t).slideToggle(400);
			});
			
			$('#pagination .element').click(function() {
				var to = $(this).data('to');
				location.href = 'blocked_users.php?p='+to;
			});
			
			$('form[name=ban-ip]').submit(function(evt) {
				evt.preventDefault();
				var ip = $(this).children('.form-inline').children('.form-group').children('input#ip').val();
				var reg = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
				
				if(reg.test(ip) == false){
					alert('Please enter a valid IP address');
					return false;
				}
				
				$.get('actions.php', {
					act:3,
					ip:ip
				}, function(data) {
					if(data == '1') {
						location.reload();
					}else{
						alert("IP couldn't be banned, please try again later");
					}
				});
			});
		});
	</script>
</body>
</html>