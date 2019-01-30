<?php
if(@$filex->isLogged($mysql) == false)
	die();
?>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" />
	
	<title>Filex - Admin Panel</title>
	
	<!-- Load Bootstrap -->
	<link rel="stylesheet" href="media/bootstrap/css/bootstrap.min.css" />
	
	<!-- Load Custom Style -->
	<link rel="stylesheet" href="media/css/style.css" />
	
	<!-- Google Fonts -->
	<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700|Roboto:400,300,100' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Quicksand:300,400,700' rel='stylesheet' type='text/css'>
	
	<!-- FontAwesome Icons -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	
	<!-- HTML5 Shiv and Respond.js for IE8 -just in case- -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>