<?php
class Filex {
	/***
	  *  Get size of a file
	  *
	***/
	public function get_file_size_mb($file) {
		$fs = filesize($file);
		$fs = number_format($fs / 1048576, 2);
		return $fs;
	}
	
	/***
	  *  Check if the user is logged
	  *
	***/
	public function isLogged($mysql) {
		if(!isset($_SESSION['filex'][0]) || !isset($_SESSION['filex'][1]))
			return false;
		
		$user = $mysql->get_setting('admin_user');
		$pass = $mysql->get_setting('admin_pass');
		
		if($_SESSION['filex'][0] == $user && $_SESSION['filex'][1] == $pass)
			return true;
		return false;
	}
	
	/***
	  *  Logout user
	  *
	***/
	public function logout() {
		unset($_SESSION['filex']);
		session_destroy();
		return true;
	}
	
	/***
	  *  Update session password (when changed)
	  *
	***/
	public function update_session_pass($pass) {
		$_SESSION['filex'][1] = $pass;
		return true;
	}
	
	/***
	  *  Update session username (when changed)
	  *
	***/
	public function update_session_user($user) {
		$_SESSION['filex'][0] = $user;
		return true;
	}
	
	/***
	  *  Login user
	  *
	***/
	public function login($mysql, $u, $p) {
		$user = $mysql->get_setting('admin_user');
		$pass = $mysql->get_setting('admin_pass');
		
		if($u == $user && $p == $pass) {
			$_SESSION['filex'][0] = $u;
			$_SESSION['filex'][1] = $p;
			return true;
		}
		return false;
	}
	
	/***
	  *  Parse dates
	  *
	***/
	public function parse_date($date) {
		$str = strtotime($date);
		return date('F jS, Y \a\t H:i:s', $str);
	}
}

$filex = new Filex;