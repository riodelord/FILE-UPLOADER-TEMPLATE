<!-- Navigation Bar -->
	<nav class="navbar navbar-static-top">
		<!-- Container -->
		<div class="container">
			<div class="navbar-header">
				<!-- Toggle Button first -->
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navigation">
					<!-- For Screen Readers -->
					<span class="sr-only">Toggle Navigation</span>
					
					<!-- Icon bars -->
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				
				<a class="navbar-brand" href="<?php echo $mysql->get_setting('site_url'); ?>"><img src="media/img/filex@3x.png" height="170%" /></a>
			</div>
			
			<!-- Navigation Links -->
			<div class="collapse navbar-collapse" id="navigation">
				<ul class="nav navbar-nav navbar-right">
					<?php
					if(isset($_pageheader) && $_pageheader == 1)
						echo '<li class="active"><a href="'.$mysql->get_setting('site_url').'index.php">HOME</a></li>';
					else
						echo '<li><a href="'.$mysql->get_setting('site_url').'index.php">HOME</a></li>';
						
					if($mysql->get_setting('allow_stats') == '1') {
						if(isset($_pageheader) && $_pageheader == 2)
							echo '<li class="active"><a href="'.$mysql->get_setting('site_url').'check-stats/">CHECK STATS</a></li>';
						else
							echo '<li><a href="'.$mysql->get_setting('site_url').'check-stats/">CHECK STATS</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
	</nav><!-- End of navigation -->