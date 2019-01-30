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

$_page = 0;

?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Dashboard</h2>
			<span class="page-desc">TODAY'S STATS</span>
			
			<div class="row main-stats">
				<div class="col no-border col-sm-6 col-md-6 col-lg-3">
					<div class="cont border-blue">
						<div class="widget">
							<div class="left-icon"><i class="fa fa-group"></i></div>
							<div class="info">
								<span class="num"><?php echo $mysql->today_total_file_visits(); ?></span>
								<span class="desc">TOTAL FILE VISITS</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col no-border col-sm-6 col-md-6 col-lg-3">
					<div class="cont border-cyan">
						<div class="widget">
							<div class="left-icon"><i class="fa fa-upload"></i></div>
							<div class="info">
								<span class="num"><?php echo $mysql->today_uploaded_files(); ?></span>
								<span class="desc">UPLOADED FILES</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col no-border col-sm-6 col-md-6 col-lg-3">
					<div class="cont border-purple">
						<div class="widget">
							<div class="left-icon"><i class="fa fa-download"></i></div>
							<div class="info">
								<span class="num"><?php echo $mysql->today_downloaded_files(); ?></span>
								<span class="desc">DOWNLOADED FILES</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col no-border col-sm-6 col-md-6 col-lg-3">
					<div class="cont border-red">
						<div class="widget">
							<div class="left-icon"><i class="fa fa-clock-o"></i></div>
							<div class="info">
								<span class="num"><?php echo $mysql->today_expired_files(); ?></span>
								<span class="desc">EXPIRED FILES</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-xs-12" id="site-visitors">
					<div class="cont multigraph-height">
						<div class="top clearfix">
							<span class="title">Files Visitors</span>
							
							<div class="dropdown pull-right">
								<button class="btn btn-default dropdown-toggle" type="button" id="dropdown_1" data-toggle="dropdown">
									<span class="option" id="this-week">THIS WEEK</span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu">
									<li id="this-week"><a href="#">THIS WEEK</a></li>
									<li id="this-month"><a href="#">THIS MONTH</a></li>
									<li id="this-year"><a href="#">THIS YEAR</a></li>
								</ul>
							</div>
						</div>
						
						<div id="site-visitors-week">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="site-visitors-month">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
						<div id="site-visitors-year">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-6" id="updown-files">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">Uploaded/Downloaded Files</span>
						</div>
						
						<div id="updown-graph">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="col col-md-6" id="expired-files">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">Expired Files</span>
						</div>
						
						<div id="expired-graph">
							<div class="graph-container">
								<div class="graph-lines"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col col-md-12" id="all-time-stats">
					<div class="cont">
						<div class="top clearfix">
							<span class="title">All Time Stats</span>
						</div>
						<div class="row">
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">File Visits</span>
								<span class="one-line bold">Uploaded Files</span>
								<span class="one-line bold">Downloaded Files</span>
								<span class="one-line bold">Expired Files</span>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<span class="one-line"><?php echo $mysql->get_general_stat(1); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(2); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(3); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(4); ?></span>
							</div>
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">No-Pass files</span>
								<span class="one-line bold">Password-protected files</span>
								<span class="one-line bold">Days-expiring files</span>
								<span class="one-line bold">Downloads-expiring files</span>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<span class="one-line"><?php echo $mysql->get_general_stat(5); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(6); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(7); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(8); ?></span>
							</div>
							<div class="first-col col-lessp col-xs-6 col-md-3 col-lg-2 text-right">
								<span class="one-line bold">Days-expired files</span>
								<span class="one-line bold">Downloads-expired files</span>
								<span class="one-line bold">Disk usage</span>
							</div>
							<div class="second-col col-lessp col-xs-6 col-md-3 col-lg-2">
								<span class="one-line"><?php echo $mysql->get_general_stat(9); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(10); ?></span>
								<span class="one-line"><?php echo $mysql->get_general_stat(11); ?> MB</span>
							</div>
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
	<script src="media/js/filex.main.js"></script>
	<script src="media/js/filex.plot.js"></script>
	<script>
		$('document').ready(function() {			
			create_tooltip();
			
			<?php
			// This week (visitors)
			$this_week_info = $mysql->get_files_visitors('THIS_WEEK');
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
			
			
			// This month (visitors)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$this_month_info = $mysql->get_files_visitors('THIS_MONTH');
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
			
			
			// This year (visitors)
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$this_year_info = $mysql->get_files_visitors('THIS_YEAR');
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
			?>
			
			
			var first_graph = build_graphdata(first_graph_data[0], '#518eba');
			var first_graph2 = build_graphdata(first_graph_data2[0], '#518eba');
			var first_graph3 = build_graphdata(first_graph_data3[0], '#518eba');
			
			var first_plot = build_graph('#site-visitors-week .graph-container .graph-lines', first_graph, first_graph_data);
			var second_plot = build_graph('#site-visitors-month .graph-container .graph-lines', first_graph2, first_graph_data2);
			var third_plot = build_graph('#site-visitors-year .graph-container .graph-lines', first_graph3, first_graph_data3);
			
			
			<?php
			// Uploads
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$files_uploads_info = $mysql->get_files_uploads();
			foreach($files_uploads_info as $date => $value) {
				$d1[] = "[".($c+1).",$value]";
				$d2[] = $date;
				$d3[] = date('m/d', strtotime($date));
				$d4[] = date('F jS, Y', strtotime($date));
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			$graphdata[1] = "[0,'".implode($d2, "','")."']";
			$graphdata[2] = "[0,'".implode($d3, "','")."']";
			$graphdata[3] = "[0,'".implode($d4, "','")."']";
			echo "\r\n\r\n";
			echo "var second_graph_data1 = [".implode($graphdata,",\r\n")."];";
			
			
			// Downloads
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array(); $graphdata = array();
			$files_downloads_info = $mysql->get_files_downloads();
			foreach($files_downloads_info as $date => $value) {
				$d1[] = "[".($c+1).",$value]";
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			echo "\r\n\r\n";
			echo "var second_graph_data2 = ".implode($graphdata,",\r\n").";";
			?>
			
			// Build data manually
			var second_graph = [
				{
					data: second_graph_data1[0], color: '#29cbc2', points: { radius:4, fillColor: '#29cbc2' }
				},{
					data: second_graph_data2, color: '#af6cda', points: { radius:4, fillColor: '#af6cda' }
				}
			];
			
			var second_plot = build_graph('#updown-graph .graph-container .graph-lines', second_graph, second_graph_data1);
			attach_xaxisLabel('#updown-graph .graph-container .graph-lines', '<span class="labelit"><i class="fa fa-circle cyan"></i>Uploaded Files</span><span class="labelit"><i class="fa fa-circle purple"></i>Downloaded Files</span>');
			$('#updown-graph .graph-container .graph-lines').bind('plothover', function(evt, position, item) {
				if(item) {
					if(last != item.dataIndex || last_series != item.seriesIndex) {
						last = item.dataIndex;
						last_series = item.seriesIndex;
						var x = item.datapoint[0]; var y = item.datapoint[1];
						var pagex = item.pageX+10; var pagey = item.pageY-30;
						
						
						if(item.seriesIndex == 0)
							var txt = y+' uploads on '+second_graph_data1[3][x];
						else
							var txt = y+' downloads on '+second_graph_data1[3][x];
						
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
			// Expired files
			$c = 0; $d1 = array(); $d2 = array(); $d3 = array(); $d4 = array();
			$files_expired_info = $mysql->get_files_expired();
			foreach($files_expired_info as $date => $value) {
				$d1[] = "[".($c+1).",$value]";
				$d2[] = $date;
				$d3[] = date('m/d', strtotime($date));
				$d4[] = date('F jS, Y', strtotime($date));
				$c+=1;
			}
			$graphdata[0] = '['.implode($d1, ',').']';
			$graphdata[1] = "[0,'".implode($d2, "','")."']";
			$graphdata[2] = "[0,'".implode($d3, "','")."']";
			$graphdata[3] = "[0,'".implode($d4, "','")."']";
			echo "\r\n\r\n";
			echo "var third_graph_data = [".implode($graphdata,",\r\n")."];";
			?>
			
			var third_graph = build_graphdata(third_graph_data[0], '#dd6e6e');
			var third_plot = build_graph('#expired-graph .graph-container .graph-lines', third_graph, third_graph_data);
			attach_xaxisLabel('#expired-graph .graph-container .graph-lines', '<span class="labelit"><i class="fa fa-circle red"></i>Expired Files</span>');
			graph_bind_hover('#expired-graph .graph-container .graph-lines', '%y% expired files on %values%', third_graph_data[3]);
			
			graph_bind_hover('#site-visitors-week .graph-lines', '%y% visits on %values%', first_graph_data[3]);
			graph_bind_hover('#site-visitors-month .graph-lines', '%y% visits %values%', first_graph_data2[3]);
			graph_bind_hover('#site-visitors-year .graph-lines', '%y% visits on %values%', first_graph_data3[3]);
			
			// Dropdown
			$('.dropdown-menu li a').click(function(evt) {
				evt.preventDefault();
				var id = $(this).parent().attr('id');
				var val = $(this).html();
				
				$(this).parent().parent().parent().children('button').children('span.option').attr('id',id).html(val);
				
				if(id == 'this-month') {
					$('#site-visitors-week').fadeOut(400, function() {
						$('#site-visitors-month').fadeIn(400);
					});
				}else if(id == 'this-week') {
					$('#site-visitors-month, #site-visitors-year').fadeOut(400, function() {
						$('#site-visitors-week').fadeIn(400);
					});
				}else if(id == 'this-year') {
					$('#site-visitors-week, #site-visitors-month').fadeOut(400, function() {
						$('#site-visitors-year').fadeIn(400);
					});
				}
			});
		});
	</script>
</body>
</html>