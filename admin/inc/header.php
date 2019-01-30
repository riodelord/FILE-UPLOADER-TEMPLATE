<?php
if(@$filex->isLogged($mysql) == false)
	die();
?>
<!-- Navigation Bar -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				
				<a class="navbar-brand"><img src="media/img/filex@1x.png" srcset="media/img/filex@1x.png 1x, media/img/filex@2x.png 2x, media/img/filex@3x.png 3x" alt="Filex - Admin Panel" /></a>
			</div>
			
			<div class="navbar-collapse pull-right">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="logout.php">LOGOUT</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="sidebar-left" id="sidebar">
		<span class="top">NAVIGATION</span>
		<ul class="navigation">
			<?php
			$_pages = array(
				'index.php' => array('fa-home','Dashboard'),
				'uploaded_files.php' => array('fa-list-ul','Uploaded Files'),
				'blocked_users.php' => array('fa-close', 'Blocked Users'),
				'site_color.php' => array('fa-eyedropper', 'Site Color'),
				'settings.php' => array('fa-gears','Settings')
			);
			
			$_counter = 0;
			$_siteurl = $mysql->get_setting('site_url').'admin/';
			foreach($_pages as $page => $info) {
				$icon = 'fa '.$info[0];
				$title = $info[1];
				
				echo ($_page == $_counter) ? '<li class="active">' : '<li>';
				
				echo '<a href="'.$_siteurl.$page.'">';
				echo '<i class="'.$icon.'"></i>';
				echo ' <span>'.$title.'</span>';
				echo '<img src="media/img/active.png" class="active pull-right" />';
				echo '</a>';
				echo '</li>';
				
				$_counter += 1;
			}
			?>
		</ul>
	</div>