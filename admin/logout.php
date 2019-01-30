<?php
require '../config.php';
require 'inc/filex.class.php';

$filex->logout();

header('Location: login.php');
