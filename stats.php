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

if(!isset($_GET['statscode']))
	header("Location: {$site}error/1/");
	
// Check if file exists in Database
$statscode = $_GET['statscode'];
if($mysql->check_existing_statscode($statscode) === false)
	header("Location: {$site}error/1/"); // Error 1 = File doesn't exist in our database

$filedata = $mysql->get_file_by_statscode($statscode);
$fileroute = 'uploads/'.$filedata->downloadcode.'.'.$filedata->fileextension;

// Status
if($filedata->status == '1')
	$status = 1;
if(!file_exists($fileroute))
	$status = 3;
if($filedata->status == '2')
	$status = 2;

if($status != 3)
	$filesize = $filex->get_file_size_mb($fileroute).'MB';
else
	$filesize = 'N/A';
$dateuploaded = $filex->parse_date($filedata->date_uploaded);
$downloads = $filedata->downloads;
if($filedata->downloads_expiration != '0')
	$downloads .= "/{$filedata->downloads_expiration}";

$_pageheader = 2;
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php';?>
	
	<!-- Sixth Container -->
	<section class="container sixth-container">
		<h2>STATS</h2>
		<?php
		if($status == 1)
			echo '<span class="active">ACTIVE</span>';
		elseif($status == 3)
			echo '<span class="danger">DELETED</span>';
		else
			echo '<span class="warning">EXPIRED</span>';
		?>
		
		<div class="row first">
			<div class="col-md-4">
				<strong>File: </strong><?php echo $filedata->filename; ?><br />
				<strong>Size: </strong><?php echo $filesize; ?><br />
				<strong>File Code: </strong><?php echo $filedata->filecode; ?><br />
				<strong>Stats Code: </strong><?php echo $filedata->statscode; ?><br />
			</div>
			<div class="col-md-4">
				<strong>Date Uploaded: </strong><?php echo $dateuploaded; ?><br />
				<strong>Uploader IP: </strong><?php echo $filedata->uploader_ip; ?><br />
				<strong>Downloads: </strong><?php echo $downloads; ?><br />
				<strong>Link Visits: </strong><?php echo $filedata->link_visits; ?><br />
			</div>
			<div class="col-md-4">
				<?php
				if($filedata->password_protected == '1') {
					echo '<strong>Wrong password attempts: </strong>'.$filedata->wrong_password_attempts.'<br />';
					echo '<strong>Correct password attemtps: </strong>'.$filedata->correct_password_attempts.'<br />';
				}
				if($filedata->days_expiration != '0') {
					if($status == 2) {
						$expired = $filex->parse_date($filedata->date_uploaded.' +'.$filedata->days_expiration.' days');
						echo '<strong>Expired on: </strong>'.$expired.'<br />';
					}else{
						$expired = $filex->parse_date($filedata->date_uploaded.' +'.$filedata->days_expiration.' days');
						echo '<strong>Will expire on: </strong>'.$expired.'<br />';
					}
				}
				if($filedata->downloads_expiration != '0') {
					if($status == 2) {
						echo '<strong>Expired on download no.: </strong>'.($filedata->downloads_expiration).'<br />';
					}else{
						echo '<strong>Downloads left to expire: </strong>'.($filedata->downloads_expiration - $filedata->downloads).'<br />';
					}
				}
				
				if($filedata->password_protected != '1' &&
				$filedata->days_expiration == '0' &&
				$filedata->downloads_expiration == '0') {
					echo '<strong>No password/days/downloads expiration.</strong>';
				}
				?>
			</div>
		</div>
		
		<div class="graph first-graph">
			<h3>Last 7 day stats</h3>
			<div id="tooltip"></div>
			
			<div class="graph-head clearfix">
				<span class="head blue">VISITS</span>
				<span class="head green">DOWNLOADS</span>
			</div>
			
			<div class="graph-container" style="width:99%; height:300px; overflow:hidden;">
				<div class="graph-lines" style="height:300px; width:100%;"></div>
			</div>
		</div>
		
		<?php
		if($filedata->password_protected != '0') {
		?>
		<div class="graph second-graph">
			<h3>Password Attempts</h3>
			<div id="tooltip"></div>
			
			<div class="graph-head clearfix">
				<span class="head green">CORRECT PASSWORD ATTEMPTS</span>
				<span class="head red">WRONG PASSWORD ATTEMPTS</span>
			</div>
			
			<div class="graph-container" style="width:99%; height:300px; overflow:hidden;">
				<div class="graph-lines" style="height:300px; width:100%;"></div>
			</div>
		</div>
		<?php
		}
		?>
		
<?php
		/* ONLY DOWNLOADS EXPIRATION */
		if($filedata->days_expiration != '0' && $filedata->days_expiration == '0') {
			// Expired?
			if($status == 2) {
				$date1 = strtotime(date('Y-m-d H:i:s'));
				$date2 = strtotime($filedata->date_uploaded);
				$days_since_up = (int)date('j', $date1 - $date2);
				if(date('Y-m-d') == date('Y-m-d', strtotime($filedata->date_uploaded)))
					$days_since_up = 0;
				
				if($days_since_up == 0)
					$days_since_up = 'Uploaded today';
				elseif($days_since_up == 1)
					$days_since_up = $days_since_up.' day since upload';
				else
					$days_since_up = $days_since_up.' days since upload';
				if($filedata->days_expiration == 1)
					$days_left = 'Expired '.$filedata->days_expiration.' day before upload';
				else
					$days_left = 'Expired '.$filedata->days_expiration.' days before upload';
				$fill = 100;
			}else{
				$date1 = strtotime(date('Y-m-d H:i:s'));
				$date2 = strtotime($filedata->date_uploaded);
				$days_since_up = (int)date('j', $date1 - $date2);
				if(date('Y-m-d') == date('Y-m-d', strtotime($filedata->date_uploaded)))
					$days_since_up = 0;
				$days_left = $filedata->days_expiration - $days_since_up;
				
				$total = $filedata->days_expiration;
				$fill = ($days_since_up * 100) / $total;
				
				if($days_since_up == 0)
					$days_since_up = 'Uploaded today';
				elseif($days_since_up == 1)
					$days_since_up = $days_since_up.' day since upload';
				else
					$days_since_up = $days_since_up.' days since upload';
					
				if($days_left == 1)
					$days_left = $days_left.' day left before expiration';
				else
					$days_left = $days_left.' days left before expiration';
			}
		?>
		<div class="row expiration" style="padding:0;">
			<div class="col-xs-4 col text-right">
				<?php echo $days_since_up ?>
			</div>
			<div class="col-xs-4 middle-col">
				<div class="bar">
					<div class="fill" style="width:<?php echo $fill ?>%"></div>
				</div>
			</div>
			<div class="col-xs-4 col text-left">
				<?php echo $days_left ?>
			</div>
		</div>
<?php } ?>
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
	<script src="media/js/flot/jquery.flot.min.js"></script>
	<script src="media/js/filex.plot.js"></script>
	<script>
		$('document').ready(function() {
			<?php
			/***
			**** FIRST GRAPH DATA 1 ****
			***/
			$result = $mysql->file_graph_visits($filedata->id);
			if($mysql->is_mysqlnd()) {
				while($res = $result->fetch_object())
					$d[$res->date] = $res->c;
			}else{
				if($result != 0) {
					foreach($result as $res)
						$d[$res->date] = $res->c;
				}
			}
			
			// values, m/d/Y date, m/d date and long date
			// reverse counter for the days
			$days = 6;
			for($i = 0; $i <= 6; $i++) {
				$minus_days = strtotime('-'.$days.' days', strtotime(date('m/d/Y')));
				$date_mdy = date('m/d/Y', $minus_days);
				$date_md = date('m/d', $minus_days);
				$date_long = date('F jS, Y', $minus_days);
				
				$val = (isset($d[$date_mdy])) ? $d[$date_mdy] : 0;
				
				$d2[$i+1] = $val;
				$d3[$i] = $date_mdy;
				$d4[$i] = $date_md;
				$d5[$i] = $date_long;
				$days -= 1;
			}
			
			$d2['7.5'] = '';
			$first_graph_data1[0] = '[';
			foreach($d2 as $n => $v)
				$first_graph_data1[0] .= "[$n,$v],";
			$first_graph_data1[0] = substr($first_graph_data1[0], 0,strlen($first_graph_data1[0]) - 1).']';
			$first_graph_data1[1] = "[0,'" . implode($d3, "','") . "','']";
			$first_graph_data1[2] = "[0,'" . implode($d4, "','") . "','']";
			$first_graph_data1[3] = "[0,'" . implode($d5, "','") . "','']";
			$first_graph_data1 = implode($first_graph_data1, ',');
			
			// Reset vars
			$d = array(); $d2 = array(); $d3 = array(); $d4 = array(); $d5 = array();
			
			
			
			/***
			**** FIRST GRAPH DATA 2 ****
			***/
			$result = $mysql->file_graph_downloads($filedata->id);
			if($mysql->is_mysqlnd()) {
				while($res = $result->fetch_object())
					$d[$res->date] = $res->c;
			}else{
				if($result != 0) {
					foreach($result as $res)
						$d[$res->date] = $res->c;
				}
			}
			
			// reverse counter for the days
			$days = 6;
			for($i = 0; $i <= 6; $i++) {
				$minus_days = strtotime('-'.$days.' days', strtotime(date('m/d/Y')));
				$date_mdy = date('m/d/Y', $minus_days);
				$d2[$i+1] = (isset($d[$date_mdy])) ? $d[$date_mdy] : 0;
				$days -= 1;
			}
			$d2['7.5'] = '';
			$first_graph_data2 = '[';
			foreach($d2 as $n => $v)
				$first_graph_data2 .= "[$n,$v],";
			$first_graph_data2 = substr($first_graph_data2, 0, strlen($first_graph_data2) - 1).']';
			
			// Reset vars
			$d = array(); $d2 = array();
			?>
			
			var first_graph_data1 = [<?php echo $first_graph_data1; ?>];
			var first_graph_data2 = [<?php echo $first_graph_data2; ?>];
			
			var first_graph = [
				{
					data: first_graph_data1[0], color: '#357cb4', points: { radius:4, fillColor: '#357cb4' }
				},{
					data: first_graph_data2[0], color: '#35b459', points: { radius:4, fillColor: '#35b459' }
				}
			];
			
			var first_plot = build_graph('.first-graph .graph-lines', first_graph, first_graph_data1);
			
			
			
			
			
			
			<?php
			if($filedata->password_protected != '0') {
			/***
			**** SECOND GRAPH DATA 1 ****
			***/
			$result = $mysql->file_graph_password_attempts($filedata->id, 2);
			if($mysql->is_mysqlnd()) {
				while($res = $result->fetch_object())
					$d[$res->date] = $res->c;
			}else{
				if($result != 0) {
					foreach($result as $res)
						$d[$res->date] = $res->c;
				}
			}
			
			// values, m/d/Y date, m/d date and long date
			// reverse counter for the days
			$days = 6;
			for($i = 0; $i <= 6; $i++) {
				$minus_days = strtotime('-'.$days.' days', strtotime(date('m/d/Y')));
				$date_mdy = date('m/d/Y', $minus_days);
				$date_md = date('m/d', $minus_days);
				$date_long = date('F jS, Y', $minus_days);
				
				$val = (isset($d[$date_mdy])) ? $d[$date_mdy] : 0;
				
				$d2[$i+1] = $val;
				$d3[$i] = $date_mdy;
				$d4[$i] = $date_md;
				$d5[$i] = $date_long;
				
				$days -= 1;
			}
			
			$d2['7.5'] = '';
			$second_graph_data1[0] = '[';
			foreach($d2 as $n => $v)
				$second_graph_data1[0] .= "[$n,$v],";
			$second_graph_data1[0] = substr($second_graph_data1[0], 0,strlen($second_graph_data1[0]) - 1).']';
			$second_graph_data1[1] = "[0,'" . implode($d3, "','") . "','']";
			$second_graph_data1[2] = "[0,'" . implode($d4, "','") . "','']";
			$second_graph_data1[3] = "[0,'" . implode($d5, "','") . "','']";
			$second_graph_data1 = implode($second_graph_data1, ',');
			
			// Reset vars
			$d = array(); $d2 = array(); $d3 = array(); $d4 = array(); $d5 = array();
			
			
			/***
			**** SECOND GRAPH DATA 2 ****
			***/
			$result = $mysql->file_graph_password_attempts($filedata->id, 1);
			if($mysql->is_mysqlnd()) {
				while($res = $result->fetch_object())
					$ddd[$res->date] = $res->c;
			}else{
				if($result != 0) {
					foreach($result as $res)
						$ddd[$res->date] = $res->c;
				}
			}
			
			// reverse counter for the days
			$days = 6;
			for($i = 0; $i <= 6; $i++) {
				$minus_days = strtotime('-'.$days.' days', strtotime(date('m/d/Y')));
				$date_mdy = date('m/d/Y', $minus_days);
				$d2[$i+1] = (isset($ddd[$date_mdy])) ? $ddd[$date_mdy] : 0;
				
				$days -= 1;
			}
			$d2['7.5'] = '';
			$second_graph_data2 = '[';
			foreach($d2 as $n => $v)
				$second_graph_data2 .= "[$n,$v],";
			$second_graph_data2 = substr($second_graph_data2, 0, strlen($second_graph_data2) - 1).']';
			
			// Reset vars
			$d = array(); $d2 = array();
			?>
			
			var second_graph_data1 = [<?php echo $second_graph_data1; ?>];
			var second_graph_data2 = [<?php echo $second_graph_data2; ?>];
			
			var second_graph = [
				{
					data: second_graph_data1[0], color: '#35b459', points: { radius:4, fillColor: '#35b459' }
				},{
					data: second_graph_data2[0], color: '#bc3737', points: { radius:4, fillColor: '#bc3737' }
				}
			];
			
			var second_plot = build_graph('.second-graph .graph-lines', second_graph, second_graph_data1);
			<?php } ?>
			
			
			var last = false;
			var last_series = false;
			$('.first-graph .graph-lines').bind('plothover', function(evt, position, item) {
				if(item) {
					if(last != item.dataIndex || last_series != item.seriesIndex) {
						last = item.dataIndex;
						last_series = item.seriesIndex;
						var x = item.datapoint[0]; var y = item.datapoint[1];
						var pagex = item.pageX+10; var pagey = item.pageY-30;
						
						
						if(item.seriesIndex == 0)
							var txt = y+' visits on '+first_graph_data1[3][x];
						else
							var txt = y+' downloads on '+first_graph_data1[3][x];
						
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
			
			
			<?php
			if($filedata->password_protected != '0') {?>
			$('.second-graph .graph-lines').bind('plothover', function(evt, position, item) {
				if(item) {
					if(last != item.dataIndex || last_series != item.seriesIndex) {
						last = item.dataIndex;
						last_series = item.seriesIndex;
						var x = item.datapoint[0]; var y = item.datapoint[1];
						var pagex = item.pageX+10; var pagey = item.pageY-30;
						
						
						if(item.seriesIndex == 0)
							var txt = y+' correct attempts on '+second_graph_data1[3][x];
						else
							var txt = y+' wrong attempts on '+second_graph_data1[3][x];
						
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
			<?php
			}
			?>
		});
	</script>
</body>
</html>