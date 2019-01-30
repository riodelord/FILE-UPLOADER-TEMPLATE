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
	$fileinfo = $mysql->get_file($filecode);
	
	$file = "../uploads/{$fileinfo->downloadcode}.{$fileinfo->fileextension}";
	if(!file_exists($file))
		$error = 2; // File doesn't exist in the server
}else{
	$error = 1; // File doesn't exist in the database
}
$_page = 1;

// No error. Check if the file should expire (if it isn't by now)
if($error != 1) {
	if($fileinfo->status != '2')
		$mysql->check_file_expiration($fileinfo->id);
}
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">File Details</h2>
			
			<div class="row">
				<div class="col col-xs-12" id="head-buttons">
					<?php
					// File doesn't exist
					if($error == 1) {
					?>
					<div class="col col-xs-12" id="uploaded-files">
						<div class="cont clearfix" style="text-align:center">
							<h4>This file doesn't exist in the database!</h4>
						</div>
					</div>
					<?php
					}else{
					?>
					<div class="cont clearfix">
						<?php if ($error == 2) { ?>
						<div class="pull-left" style="font-weight:600; margin:3px auto 5px auto; color:#FF0000;">
							File doesn't exist in the server!
						</div>
						<?php } ?>
						<div class="pull-right">
							<?php if ($error != 2) { ?>
							<button type="button" name="head-1" data-fcode="<?php echo $fileinfo->filecode; ?>" class="btn btn-success"><i class="fa fa-download"></i>DOWNLOAD</button>
							<?php } ?>
							<button type="button" name="head-2" data-fcode="<?php echo $fileinfo->filecode; ?>" class="btn btn-danger-light"><i class="fa fa-close"></i>DELETE FILE</button>
							
							<?php
							if($mysql->is_banned($fileinfo->uploader_ip))
								echo '<button type="button" name="head-3" data-action="unban-ip" data-ip="'.$fileinfo->uploader_ip.'" class="btn btn-success"><i class="fa fa-ban"></i>UNBAN UPLOADER IP</button>';
							else
								echo '<button type="button" name="head-3" data-action="ban-ip" data-ip="'.$fileinfo->uploader_ip.'" class="btn btn-danger"><i class="fa fa-ban"></i>BAN UPLOADER IP</button>';
							?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
			
			<?php
			if($error != 1) {
			?>
			<div class="row">
				<div class="col col-md-12" id="file-details">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">Details</span>
						</div>
						
						<div class="row">
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">File</span>
								<span class="one-line bold">Size</span>
								<span class="one-line bold">Date Uploaded</span>
								<span class="one-line bold">File Code</span>
								<span class="one-line bold">Download Code</span>
								<span class="one-line bold">Stats Code</span>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<span class="one-line"><?php echo $fileinfo->filename; ?></span>
								<?php
								if($error == 2)
									echo '<span class="one-line">N/A</span>';
								else
									echo '<span class="one-line">'.$filex->get_file_size_mb($file).'MB'.'</span>';
								?>
								<span class="one-line"><?php echo $filex->parse_date($fileinfo->date_uploaded); ?></span>
								<span class="one-line"><?php echo $fileinfo->filecode; ?></span>
								<span class="one-line"><?php echo $fileinfo->downloadcode; ?></span>
								<span class="one-line"><?php echo $fileinfo->statscode; ?></span>
							</div>
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">Uploader IP</span>
								<span class="one-line bold">Downloads</span>
								<span class="one-line bold">Link Visits</span>
								<span class="one-line bold">Link visits after expired</span>
								<?php
								if($fileinfo->status == '2')
									echo '<span class="one-line bold">Expired on</span>';
								?>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<span class="one-line"><?php echo $fileinfo->uploader_ip; ?></span>
								<?php
								if($fileinfo->downloads_expiration != '0')
									echo '<span class="one-line">'.$fileinfo->downloads.'/'.$fileinfo->downloads_expiration.'</span>';
								else
									echo '<span class="one-line">'.$fileinfo->downloads.'</span>';
								?>
								<span class="one-line"><?php echo $mysql->get_file_visits($fileinfo->id); ?></span>
								
								<?php
								if($fileinfo->status == '2') {
									echo '<span class="one-line">'.$mysql->get_ae_visits($fileinfo->id).'</span>';
									echo '<span class="one-line">'.date('m/d/Y \a\t H:i:s', strtotime($mysql->get_file_expiration_date($fileinfo->id))).'</span>';
								}else
									echo '<span class="one-line">N/A</span>';
								?>
							</div>
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">Wrong password attempts</span>
								<span class="one-line bold">Correct password attempts</span>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<?php
								if($fileinfo->password_protection == '0') {
									echo '<span class="one-line">N/A</span>';
									echo '<span class="one-line">N/A</span>';
								}else{
									echo '<span class="one-line">'.$fileinfo->wrong_password_attempts.'</span>';
									echo '<span class="one-line">'.$fileinfo->correct_password_attempts.'</span>';
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-xs-12 mg" id="file-visits">
					<div class="cont multigraph-height">
						<div class="top clearfix">
							<span class="title">Visits</span>
							
							<div class="dropdown pull-right">
								<button class="btn btn-default dropdown-toggle" type="button" id="dropdown_1" data-toggle="dropdown">
									<span class="option" id="this-week">THIS WEEK</span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" data-to="file-visits">
									<li id="this-week"><a href="#">THIS WEEK</a></li>
									<li id="this-month"><a href="#">THIS MONTH</a></li>
									<li id="this-year"><a href="#">THIS YEAR</a></li>
								</ul>
							</div>
						</div>
						
						<div id="file-visits-week">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="file-visits-month">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="file-visits-year">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<?php
			if($fileinfo->password_protected == '1') {
			?>
			<div class="row">
				<div class="col col-xs-12 mg" id="password-attempts">
					<div class="cont multigraph-height">
						<div class="top clearfix">
							<span class="title title-small">Password Attempts</span>
							
							<div class="dropdown pull-right dropdown-small">
								<button class="btn btn-default dropdown-toggle" type="button" id="dropdown_1" data-toggle="dropdown">
									<span class="option" id="this-week">THIS WEEK</span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" data-to="password-attempts">
									<li id="this-week"><a href="#">THIS WEEK</a></li>
									<li id="this-month"><a href="#">THIS MONTH</a></li>
									<li id="this-year"><a href="#">THIS YEAR</a></li>
								</ul>
							</div>
						</div>
						
						<div id="password-attempts-week">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="password-attempts-month">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="password-attempts-year">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			}?>

			<div class="row">
				<div class="col col-md-12 col-lg-6" id="downloads">
					<div class="cont clearfix">
						<div class="top clearfix">
							<span class="title title-small">Downloads</span>
						</div>
						
						<?php
						if($mysql->count_file_downloads($fileinfo->id) == '0') {
							echo 'This file hasn\'t been downloaded yet.';
						}else{
						?>
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th width="30%">IP</th>
									<th width="50%">Date</th>
									<th width="20%">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$res = $mysql->get_file_downloadslist($fileinfo->id);
								while($obj = $res->fetch_object()) {
									echo '<tr data-id="'.$obj->id.'">';
									echo '<td>'.$obj->ip.'</td>';
									echo '<td>'.$filex->parse_date($obj->date).'</td>';
									echo '<td>';
									
									if($mysql->is_banned($obj->ip))
										echo '<a href="file.php" data-action="unban-ip" name="ban" class="btn btn-success btn-small"><span>UNBAN IP</span></a>';
									else
										echo '<a href="file.php" data-action="ban-ip" name="ban" class="btn btn-danger btn-small"><span>BAN IP</span></a>';
									echo '</td>';
									echo '</tr>';
								}
								?>
							</tbody>
						</table>
						<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	
	
	<!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="media/bootstrap/js/bootstrap.min.js"></script>
	<script src="media/js/flot/jquery.flot.min.js"></script>
	<script src="media/js/filex.main.js"></script>
	<script src="media/js/filex.plot.js"></script>
	<script>
		$('document').ready(function() {
			create_tooltip();
			
			$('a[name=ban]').on('click', function(evt) {
				evt.preventDefault();
				var t = $(this);
				var record_id = $(this).parent().parent().data('id');
				
				if(t.data('action') == 'ban-ip')
					var ac = 1;
				else
					var ac = 2;
				
				$.get('actions.php', {
					act:ac,
					record:record_id
				}, function(data) {
					alert(data);
					if(data == '1') {
						if(ac == 1) {
							alert('IP successfully banned!');
							t.removeClass('btn-danger').addClass('btn-success');
							t.data('action', 'unban-ip');
							t.children('span').html('UNBAN IP');
						}else{
							alert('IP successfully unbanned!');
							t.removeClass('btn-success').addClass('btn-danger');
							t.data('action', 'ban-ip');
							t.children('span').html('BAN IP');
						}
					}else{
						alert('Something went wrong, please try again later.');
					}
				});
			});
			
			// Download file
			$('button[name=head-1]').click(function(evt) {
				evt.preventDefault();
				var fcode = $(this).data('fcode');
				
				location.href = 'download.php?filecode='+fcode;
			});
			
			// Delete file
			$('button[name=head-2]').click(function(evt) {
				evt.preventDefault();
				var fcode = $(this).data('fcode');
				
				var conf = confirm("Are you sure you want to delete this file?");
				if(conf == true) {
					$.get('actions.php', {
						act:5,
						filecode:fcode
					}, function(data) {
						if(data == '1')
							location.href = 'uploaded_files.php';
						else
							alert('File couldn\'t be deleted, please try again later.');
					});
				}
			});
			
			// Ban IP
			$('button[name=head-3]').click(function(evt) {
				evt.preventDefault();
				var t = $(this);
				var ip = $(this).data('ip');
				
				if(t.data('action') == 'ban-ip') {
					var ac = 3;
					var conf = confirm("Are you sure you want to ban this IP?");
				}else{
					var ac = 4;
					var conf = confirm("Are you sure you want to unban this IP?");
				}
				
				if(conf == true) {
					$.get('actions.php', {
						act:ac,
						ip:ip
					}, function(data) {
						if(data == '1') {
							if(ac == 3) {
								alert('IP successfully banned!');
								t.data('action','unban-ip');
								t.removeClass('btn-danger').addClass('btn-success');
								t.html('<i class="fa fa-ban"></i>UNBAN UPLOADER IP');
							}else{
								alert('IP successfully unbanned!');
								t.data('action','ban-ip');
								t.removeClass('btn-success').addClass('btn-danger');
								t.html('<i class="fa fa-ban"></i>BAN UPLOADER IP');
							}
						}else{
							if(ac == 3)
								alert("IP couldn't be banned, please try again later");
							else
								alert("IP couldn't be unbanned, please try again later");
						}
					});
				}
			});
			
			
			// First graph
			// This week, this month, this year
			<?php
			// This week (Visitors)
			$this_week_info = $mysql->get_file_visitors('THIS_WEEK', $fileinfo->id);
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			foreach($this_week_info as $date => $visits) {
				$d1[] = "[".($c+1).",$visits]";
				$d2[] = $date;
				$d3[] = date('m/d', strtotime($date));
				$d4[] = date('F jS, Y', strtotime($date));
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			$graphdata[1] = "[0,'".implode($d2, "','")."']";
			$graphdata[2] = "[0,'".implode($d3, "','")."']";
			$graphdata[3] = "[0,'".implode($d4, "','")."']";
			echo "var first_graph_data = [".implode($graphdata,",\r\n")."];";
			
			
			// This week (downloads)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
			$this_week_info = $mysql->get_file_downloads('THIS_WEEK', $fileinfo->id);
			foreach($this_week_info as $info) {
				$d1[] = "[".($c+1).",{$info}]";
				$c+=1;
			}
			$graphdata[0] = implode($d1, ',');
			echo "var second_graph_data = [".implode($graphdata,",\r\n")."];";
			
			
			// This month (visitors)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$this_month_info = $mysql->get_file_visitors('THIS_MONTH', $fileinfo->id);
			foreach($this_month_info as $info) {
				$d1[] = "[".($c+1).",{$info[3]}]";
				$d2[] = $info[0];
				if($info[2] != 0)
					$d3[] = date('m/d', strtotime($info[1])).' to '.date('m/d', strtotime($info[2]));
				else
					$d3[] = $info[0];
				$d4[] = strtolower($info[0]);
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			$graphdata[1] = "[0,'".implode($d2, "','")."']";
			$graphdata[2] = "[0,'".implode($d3, "','")."']";
			$graphdata[3] = "[0,'".implode($d4, "','")."']";
			echo "\r\n\r\n";
			echo "var first_graph_data2 = [".implode($graphdata,",\r\n")."];";
			
			
			// This month (downloads)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
			$this_week_info = $mysql->get_file_downloads('THIS_MONTH', $fileinfo->id);
			foreach($this_week_info as $info) {
				$d1[] = "[".($c+1).",{$info[3]}]";
				$c+=1;
			}
			$graphdata[0] = implode($d1, ',');
			echo "var second_graph_data2 = [".implode($graphdata,",\r\n")."];";
			
			
			// This year (visitors)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$this_year_info = $mysql->get_file_visitors('THIS_YEAR', $fileinfo->id);
			$months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
			foreach($this_year_info as $month => $visits) {				
				$d1[] = "[".($c+1).",$visits]";
				$d2[] = substr($months[$month], 0, 3);
				$d3[] = substr($months[$month], 0, 3);
				$d4[] = $months[$month];
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			$graphdata[1] = "[0,'".implode($d2, "','")."']";
			$graphdata[2] = "[0,'".implode($d3, "','")."']";
			$graphdata[3] = "[0,'".implode($d4, "','")."']";
			echo "\r\n\r\n";
			echo "var first_graph_data3 = [".implode($graphdata,",\r\n")."];";
			
			
			// This year (downloads)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
			$this_week_info = $mysql->get_file_downloads('THIS_YEAR', $fileinfo->id);
			foreach($this_week_info as $info) {
				$d1[] = "[".($c+1).",{$info}]";
				$c+=1;
			}
			$graphdata[0] = implode($d1, ',');
			echo "var second_graph_data3 = [".implode($graphdata,",\r\n")."];";
			?>

			// Build graphs data manually
			var first_graph = [
				{
					data: first_graph_data[0], color: '#29cac1', points: { radius:4, fillColor: '#29cac1' }
				},{
					data: second_graph_data, color:'#8a55ac', points: { radius:4, fillColor:'#8a55ac' }
				}
			];
			var second_graph = [
				{
					data: first_graph_data2[0], color: '#29cac1', points: { radius:4, fillColor: '#29cac1' }
				},{
					data: second_graph_data2, color:'#8a55ac', points: { radius:4, fillColor:'#8a55ac' }
				}
			];
			var third_graph = [
				{
					data: first_graph_data3[0], color: '#29cac1', points: { radius:4, fillColor: '#29cac1' }
				},{
					data: second_graph_data3, color:'#8a55ac', points: { radius:4, fillColor:'#8a55ac' }
				}
			];			
			var first_plot = build_graph('#file-visits-week .graph-container .graph-lines', first_graph, first_graph_data);
			var second_plot = build_graph('#file-visits-month .graph-container .graph-lines', second_graph, first_graph_data2);
			var third_plot = build_graph('#file-visits-year .graph-container .graph-lines', third_graph, first_graph_data3);
			attach_xaxisLabel('.col#file-visits .graph-container .graph-lines', '<span class="labelit"><i class="fa fa-circle cyan"></i>Visits</span><span class="labelit"><i class="fa fa-circle purple"></i>Downloads</span>');


			<?php
			if($fileinfo->password_protected == '1') {
				// This week (correct pass attempts)
				$this_week_info = $mysql->get_correct_password_attempts('THIS_WEEK', $fileinfo->id);
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
				foreach($this_week_info as $date => $visits) {
					$d1[] = "[".($c+1).",$visits]";
					$d2[] = $date;
					$d3[] = date('m/d', strtotime($date));
					$d4[] = date('F jS, Y', strtotime($date));
					$c+=1;
				}
				$graphdata[0] = '['.implode($d1, ',').']';
				$graphdata[1] = "[0,'".implode($d2, "','")."']";
				$graphdata[2] = "[0,'".implode($d3, "','")."']";
				$graphdata[3] = "[0,'".implode($d4, "','")."']";
				echo "var third_graph_data = [".implode($graphdata,",\r\n")."];";
				
				
				// This week (wrong pass attempts)
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
				$this_week_info = $mysql->get_wrong_password_attempts('THIS_WEEK', $fileinfo->id);
				foreach($this_week_info as $info) {
					$d1[] = "[".($c+1).",{$info}]";
					$c+=1;
				}
				$graphdata[0] = implode($d1, ',');
				echo "var fourth_graph_data = [".implode($graphdata,",\r\n")."];";
				
				
				// This month (correct pass attempts)
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
				$this_month_info = $mysql->get_correct_password_attempts('THIS_MONTH', $fileinfo->id);
				foreach($this_month_info as $info) {
					$d1[] = "[".($c+1).",{$info[3]}]";
					$d2[] = $info[0];
					if($info[2] != 0)
						$d3[] = date('m/d', strtotime($info[1])).' to '.date('m/d', strtotime($info[2]));
					else
						$d3[] = $info[0];
					$d4[] = strtolower($info[0]);
					$c+=1;
				}
				$graphdata[0] = '['.implode($d1, ',').']';
				$graphdata[1] = "[0,'".implode($d2, "','")."']";
				$graphdata[2] = "[0,'".implode($d3, "','")."']";
				$graphdata[3] = "[0,'".implode($d4, "','")."']";
				echo "\r\n\r\n";
				echo "var third_graph_data2 = [".implode($graphdata,",\r\n")."];";
				
				
				// This month (wrong pass attempts)
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
				$this_week_info = $mysql->get_wrong_password_attempts('THIS_MONTH', $fileinfo->id);
				foreach($this_week_info as $info) {
					$d1[] = "[".($c+1).",{$info[3]}]";
					$c+=1;
				}
				$graphdata[0] = implode($d1, ',');
				echo "var fourth_graph_data2 = [".implode($graphdata,",\r\n")."];";
				
				
				// This year (correct pass attempts)
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
				$this_year_info = $mysql->get_correct_password_attempts('THIS_YEAR', $fileinfo->id);
				$months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
				foreach($this_year_info as $month => $visits) {				
					$d1[] = "[".($c+1).",$visits]";
					$d2[] = substr($months[$month], 0, 3);
					$d3[] = substr($months[$month], 0, 3);
					$d4[] = $months[$month];
					$c+=1;
				}
				$graphdata[0] = '['.implode($d1, ',').']';
				$graphdata[1] = "[0,'".implode($d2, "','")."']";
				$graphdata[2] = "[0,'".implode($d3, "','")."']";
				$graphdata[3] = "[0,'".implode($d4, "','")."']";
				echo "\r\n\r\n";
				echo "var third_graph_data3 = [".implode($graphdata,",\r\n")."];";
				
				
				// This year (wrong pass attempts)
				$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
				$this_week_info = $mysql->get_wrong_password_attempts('THIS_YEAR', $fileinfo->id);
				foreach($this_week_info as $info) {
					$d1[] = "[".($c+1).",{$info}]";
					$c+=1;
				}
				$graphdata[0] = implode($d1, ',');
				echo "var fourth_graph_data3 = [".implode($graphdata,",\r\n")."];";
			?>
			
			
			// Build graphs data manually
			var first_graph = [
				{
					data: third_graph_data[0], color: '#5cb7cd', points: { radius:4, fillColor: '#5cb7cd' }
				},{
					data: fourth_graph_data, color:'#d36868', points: { radius:4, fillColor:'#d36868' }
				}
			];
			var second_graph = [
				{
					data: third_graph_data2[0], color: '#5cb7cd', points: { radius:4, fillColor: '#5cb7cd' }
				},{
					data: fourth_graph_data2, color:'#d36868', points: { radius:4, fillColor:'#d36868' }
				}
			];
			var third_graph = [
				{
					data: third_graph_data3[0], color: '#5cb7cd', points: { radius:4, fillColor: '#5cb7cd' }
				},{
					data: fourth_graph_data3, color:'#d36868', points: { radius:4, fillColor:'#d36868' }
				}
			];			
			var first_plot = build_graph('#password-attempts-week .graph-container .graph-lines', first_graph, first_graph_data);
			var second_plot = build_graph('#password-attempts-month .graph-container .graph-lines', second_graph, first_graph_data2);
			var third_plot = build_graph('#password-attempts-year .graph-container .graph-lines', third_graph, first_graph_data3);
			attach_xaxisLabel('.col#password-attempts .graph-container .graph-lines', '<span class="labelit"><i class="fa fa-circle blue"></i>Correct password attempts</span><span class="labelit"><i class="fa fa-circle red"></i>Wrong password attempts</span>');
			<?php
			}
			?>
			
			$('.graph-container .graph-lines').bind('plothover', function(evt, position, item) {
				if(item) {
					if(last != item.dataIndex || last_series != item.seriesIndex) {
						last = item.dataIndex;
						last_series = item.seriesIndex;
						var x = item.datapoint[0]; var y = item.datapoint[1];
						var pagex = item.pageX+10; var pagey = item.pageY-30;
						
						var target = $(evt.currentTarget).parent().parent().attr('id');
						if(target == 'file-visits-week')
							var on = first_graph_data[3][x];
						else if(target == 'file-visits-month')
							var on = first_graph_data2[3][x];
						else if(target == 'file-visits-year')
							var on = first_graph_data3[3][x];
						else if(target == 'password-attempts-week')
							var on = third_graph_data[3][x];
						else if(target == 'password-attempts-month')
							var on = third_graph_data2[3][x];
						else if(target == 'password-attempts-year')
							var on = third_graph_data3[3][x];
						
						if(item.seriesIndex == 0) {
							if(target == 'file-visits-week' || target == 'file-visits-month' || target == 'file-visits-year')
								var txt = y+' visits on '+on;
							else
								var txt = y+' correct password attempts on '+on;
						}else{
							if(target == 'file-visits-week' || target == 'file-visits-month' || target == 'file-visits-year')
								var txt = y+' downloads on '+on;
							else
								var txt = y+' wrong password attempts on '+on;
						}
						
						$('#tooltip').html(txt).css({
							top:pagey,
							left:pagex
						});
						
						if(!$('#tooltip').is('visible'))
							$('#tooltip').fadeIn(30);
					}
				}else{
					$('#tooltip').fadeOut(30);
					last = false;
					last_series = false;
				}
			});
			
			$('#file-visits-month, #file-visits-year, #password-attempts-month, #password-attempts-year').css('display','none');
			
			// Dropdown
			$('.dropdown-menu li a').click(function(evt) {
				evt.preventDefault();
				var id = $(this).parent().attr('id');
				var val = $(this).html();
				var to = $(this).parent().parent().data('to');
				
				$(this).parent().parent().parent().children('button').children('span.option').attr('id',id).html(val);
				
				if(to == 'file-visits') {
					if(id == 'this-month') {
						$('#file-visits-week, #file-visits-year').fadeOut(200, function() {
							$('#file-visits-week, #file-visits-year').css('display','none');
							$('#file-visits-month').fadeIn(400);
						});
					}else if(id == 'this-week') {
						$('#file-visits-month, #file-visits-year').fadeOut(200, function() {
							$('#file-visits-month, #file-visits-year').css('display','none');
							$('#file-visits-week').fadeIn(400);
						});
					}else if(id == 'this-year') {
						$('#file-visits-week, #file-visits-month').fadeOut(200, function() {
							$('#file-visits-week, #file-visits-month').css('display','none');
							$('#file-visits-year').fadeIn(400);
						});
					}
				}else if(to == 'password-attempts') {
					if(id == 'this-month') {
						$('#password-attempts-week, #password-attempts-year').fadeOut(200, function() {
							$('#password-attempts-week, #password-attempts-year').css('display','none');
							$('#password-attempts-month').fadeIn(400);
						});
					}else if(id == 'this-week') {
						$('#password-attempts-month, #password-attempts-year').fadeOut(200, function() {
							$('#password-attempts-month, #password-attempts-year').css('display','none');
							$('#password-attempts-week').fadeIn(400);
						});
					}else if(id == 'this-year') {
						$('#password-attempts-week, #password-attempts-month').fadeOut(200, function() {
							$('#password-attempts-week, #password-attempts-month').css('display','none');
							$('#password-attempts-year').fadeIn(400);
						});
					}
				}
			});
		});
	</script>
</body>
</html>