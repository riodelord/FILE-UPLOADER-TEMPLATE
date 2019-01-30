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

$_page = 1;

// Delete file
if(isset($_POST['act']) && isset($_POST['filecode'])) {
	if($_POST['act'] == '1') {
		$filecode = $_POST['filecode'];
		
		// Get file info
		$fileinfo = $mysql->get_file($filecode);
		$file = "../uploads/{$fileinfo->downloadcode}.{$fileinfo->fileextension}";
		
		if($mysql->delete_file($filecode) == true) {
			if(@unlink($file) === true)
				die('y');
			die('no');
		}
	}
}

if(!isset($_GET['p']) || $_GET['p'] == 0 || $_GET['p'] == 1)
	$ppage = 1;
else
	$ppage = $_GET['p'];
	
$record_start = (($ppage-1)*15);
$records = 15;
$total_records = $mysql->count_uploaded_files();
?>
<!DOCTYPE html>
<html>
<?php require 'inc/head.php'; ?>
<body>
	<?php require 'inc/header.php'; ?>
	
	<div class="general-wrapper">
		<div class="content">
			<h2 class="page-title">Uploaded Files</h2>
			
			<div class="row">
				<div class="col col-xs-12" id="uploaded-files">
					<div class="cont clearfix">
						<?php
						if($total_records == 0)
							echo '<div style="width:100%;text-align:center;"><h4>No files uploaded yet.</h4></div>';
						else{
						?>
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th width="5%">ID</th>
									<th width="20%">File Name</th>
									<th width="11%">Date Uploaded</th>
									<th width="8%">IP</th>
									<th width="11%">Downloads</th>
									<th width="11%">Password Protected</th>
									<th width="10%">Days Expiration</th>
									<th width="11%">Downloads Expiration</th>
									<th width="13%">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$files_list = $mysql->get_files_list($record_start, $records);
								while($obj = $files_list->fetch_object()) {
									echo '<tr data-filecode="'.$obj->filecode.'">';
									echo '<td>'.$obj->id.'</td>';
									echo '<td>'.$obj->filename.'</td>';
									echo '<td>'.date('M/d/Y', strtotime($obj->date_uploaded)).'</td>';
									echo '<td>'.$obj->uploader_ip.'</td>';
									echo '<td>'.$obj->downloads.'</td>';
									echo ($obj->password_protected == 1) ? '<td>Yes</td>' : '<td>No</td>';
									echo ($obj->days_expiration == 0) ? '<td>N/A</td>' : '<td>'.$obj->days_expiration.'</td>';
									echo ($obj->downloads_expiration == 0) ? '<td>N/A</td>' : '<td>'.$obj->downloads_expiration.'</td>';
									echo '<td>';
									echo '	<a href="download.php?filecode='.$obj->filecode.'" class="btn btn-info btn-small"><span>DOWNLOAD</span><i class="fa fa-download"></i></a>';
									echo '	<i class="fa fa-close"></i>';
									echo '</td>';
									echo '</tr>';
								}
								?>
							</tbody>
						</table>
						
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
						
						<?php
						}
						?>
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
			
			$('tr i.fa-close').click(function(evt) {
				evt.preventDefault();
				evt.stopPropagation();
				
				var filecode = $(this).parent().parent().data('filecode');
				var elem = $(this).parent().parent();
				$.post('uploaded_files.php', {
					'act':1,
					'filecode':filecode
				}, function(data) {
					if(data == 'y')
						$(elem).fadeOut(500);
					else
						alert('Something failed, try again');
				});
			});
			
			$('tr').click(function(evt) {
				evt.preventDefault();
				var filecode = $(this).data('filecode');
				location.href = 'file.php?filecode='+filecode;
			});
			
			$('#pagination .element').click(function() {
				var to = $(this).data('to');
				location.href = 'uploaded_files.php?p='+to;
			});
		});
	</script>
</body>
</html>