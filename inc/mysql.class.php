<?php

class Filex_Mysql {
	/***
	  *  Private var that stores the MySQLi Object
	  *
	***/
	private $mysqli = false;
	
	/***
	  *  Class constructor
	  *
	***/
	public function __construct($m) {
		$this->mysqli = $m;
		
		// Check files that should expire
		$this->check_expired_files();
	}
	
	/***
	  *  Returns Filex setting
	  *
	***/
	public function get_setting($setting) {
		// Prepate statement
		$prepared = $this->prepare("SELECT `val` FROM `filex_settings` WHERE `setting`=?", 'get_setting');
		$this->bind_param($prepared->bind_param('s', $setting), 'get_setting()');
		$this->execute($prepared, 'get_setting()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$row = $result->fetch_object();
			return $row->val;
		}else{
			$prepared->bind_result($val);
			$prepared->fetch();
			return $val;
		}
	}
	
	/***
	  *  Returns uploaded files
	  *
	***/
	public function get_uploaded_files() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_files`", 'get_uploaded_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Check if an IP is banned
	  *
	***/
	public function is_banned($ip) {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_blocked` WHERE `ip`='$ip'", 'is_banned()');
		$obj = $res->fetch_object();
		if($obj->c == 0)
			return false;
		return true;
	}
	
	/***
	  *  Add enter attempt to banned IP
	  *
	***/
	public function add_enter_attempt($ip) {
		$this->query("UPDATE `filex_blocked` SET `enter_attempts`=`enter_attempts`+1 WHERE `ip`='$ip'", 'add_enter_attempt()');
		return true;
	}
	
	/***
	  *  Returns downloads
	  *
	***/
	public function get_downloads() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_downloads`", 'get_downloads()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Returns expired files
	  *
	***/
	public function get_expired_files() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_expiration`", 'get_expired_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Saves new file
	  *
	***/
	public function save_file($data) {
		$file_name = $data[0];
		$file_code = $data[1];
		$stats_code = $data[2];
		$download_code = $data[3];
		$ext = $data[4];
		$date_uploaded = $data[5];
		$uploader_ip = $data[6];
		$downloads = $data[7];
		$link_visits = $data[8];
		$days_expiration = $data[9];
		$downloads_expiration = $data[10];
		$password_protected = $data[11];
		$password_protection = $data[12];
		$wrong_password = $data[13];
		$correct_password = $data[14];
		$status = $data[15];
		
		$prepared = $this->prepare("INSERT INTO `filex_files`(`filename`,`filecode`,`statscode`,`downloadcode`,`fileextension`,`date_uploaded`,`uploader_ip`,`downloads`,`link_visits`,`days_expiration`,`downloads_expiration`,`password_protected`,`password_protection`,`wrong_password_attempts`,`correct_password_attempts`,`status`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'save_file()');
		$this->bind_param($prepared->bind_param('sssssssiiiiisiii', $file_name, $file_code, $stats_code, $download_code, $ext, $date_uploaded, $uploader_ip, $downloads, $link_visits, $days_expiration, $downloads_expiration, $password_protected, $password_protection, $wrong_password, $correct_password, $status), 'save_file()');
		$this->execute($prepared, 'save_file()');
		
		return true;
	}
	
	/***
	  *  Checks existing file by filecode
	  *
	***/
	public function check_existing_filecode($file_code) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `filecode`=?", 'check_existing_filecode()');
		$this->bind_param($prepared->bind_param('s', $file_code), 'check_existing_filecode()');
		$this->execute($prepared, 'check_existing_filecode()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == '1')
			return true;
		return false;
	}
	
	/***
	  *  Checks existing file by statscode
	  *
	***/
	public function check_existing_statscode($stats_code) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `statscode`=?", 'check_existing_statscode()');
		$this->bind_param($prepared->bind_param('s', $stats_code), 'check_existing_statscode()');
		$this->execute($prepared, 'check_existing_statscode()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == '1')
			return true;
		return false;
	}
	
	/***
	  *  Checks existing file by downloadcode
	  *
	***/
	public function check_existing_downloadcode($download_code) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `downloadcode`=?", 'check_existing_downloadcode()');
		$this->bind_param($prepared->bind_param('s', $download_code), 'check_existing_downloadcode()');
		$this->execute($prepared, 'check_existing_downloadcode()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == '1')
			return true;
		return false;
	}
	
	/***
	  *  Checks existing file by filecode
	  *
	***/
	public function check_existing_file($filecode) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `filecode`=?", 'check_existing_file()');
		$this->bind_param($prepared->bind_param('s', $filecode), 'check_existing_file()');
		$this->execute($prepared, 'check_existing_file()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == '1')
			return true;
		return false;
	}
	
	/***
	  *  Returns file
	  *
	***/
	public function get_file($filecode) {
		$prepared = $this->prepare("SELECT * FROM `filex_files` WHERE `filecode`=?", 'get_file()');
		$this->bind_param($prepared->bind_param('s', $filecode), 'get_file()');
		$this->execute($prepared, 'get_file()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			return $result->fetch_object();
		}else{
			return $this->prepared_to_sobject($prepared);
		}
	}
	
	/***
	  *  Returns file by statscode
	  *
	***/
	public function get_file_by_statscode($statscode) {
		$prepared = $this->prepare("SELECT * FROM `filex_files` WHERE `statscode`=?", 'get_file_by_statscode()');
		$this->bind_param($prepared->bind_param('s', $statscode), 'get_file_by_statscode()');
		$this->execute($prepared, 'get_file_by_statscode()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			return $result->fetch_object();
		}else{
			return $this->prepared_to_sobject($prepared);
		}
	}
	
	/***
	  *  Checks and expires file that should expire
	  *
	***/
	public function check_expired_files() {
		$res = $this->query("SELECT `id` FROM `filex_files` WHERE `days_expiration` != 0 AND DATE_ADD(`date_uploaded`, INTERVAL `days_expiration` DAY) <= NOW() && `status`!=2", 'check_expired_files()');
		while($obj = $res->fetch_object()) {
			$date = date('Y-m-d H:i:s');
			$this->query("UPDATE `filex_files` SET `status`=2 WHERE `id`={$obj->id}", 'check_expired_files()');
			$this->query("INSERT INTO `filex_expiration`(`fileid`,`date`) VALUES({$obj->id}, '{$date}')", 'check_expired_files()');
		}
		
		return true;
	}
	
	/***
	  *  Returns file visits for graph
	  *
	***/
	public function file_graph_visits($fileid) {
		$q = "SELECT DATE_FORMAT(`date`,'%m/%d/%Y') as date, COUNT(*) as c FROM `filex_visits` WHERE `fileid`=? AND `date` >= DATE_SUB(DATE(NOW()), INTERVAL 7 DAY) GROUP BY DAY(`date`)";
		$prepared = $this->prepare($q, 'file_graph_visits()');
		$this->bind_param($prepared->bind_param('i', $fileid), 'file_graph_visits()');
		$this->execute($prepared, 'file_graph_visits()');
		
		if($this->is_mysqlnd())
			return $prepared->get_result();
		else
			return $this->prepared_to_object($prepared);
	}
	
	/***
	  *  Returns file downloads for graph
	  *
	***/
	public function file_graph_downloads($fileid) {
		$q = "SELECT DATE_FORMAT(`date`,'%m/%d/%Y') as date, COUNT(*) as c FROM `filex_downloads` WHERE `fileid`=? AND `date` >= DATE_SUB(DATE(NOW()), INTERVAL 7 DAY) GROUP BY DAY(`date`)";
		$prepared = $this->prepare($q, 'file_graph_downloads()');
		$this->bind_param($prepared->bind_param('i', $fileid), 'file_graph_downloads()');
		$this->execute($prepared, 'file_graph_downloads()');
		
		if($this->is_mysqlnd())
			return $prepared->get_result();
		else
			return $this->prepared_to_object($prepared);
	}
	
	/***
	  *  Returns file password attempts for graph
	  *
	***/
	public function file_graph_password_attempts($fileid, $type) {
		$q = "SELECT DATE_FORMAT(`date`,'%m/%d/%Y') as date, COUNT(*) as c FROM `filex_password_attempts` WHERE `fileid`=? AND `type`=? AND `date` >= DATE_SUB(DATE(NOW()), INTERVAL 7 DAY) GROUP BY DAY(`date`)";
		$prepared = $this->prepare($q, 'file_graph_password_attempts()');
		$this->bind_param($prepared->bind_param('ii', $fileid, $type), 'file_graph_password_attempts()');
		$this->execute($prepared, 'file_graph_password_attempts()');
		
		if($this->is_mysqlnd())
			return $prepared->get_result();
		else
			return $this->prepared_to_object($prepared);
	}
	
	/***
	  *  Expires file by filecode and fileid
	  *
	***/
	public function expire_file($filecode, $fileid) {
		$prepared1 = $this->prepare("UPDATE `filex_files` SET `status`=2 WHERE filecode=?", 'expire_file()');
		$this->bind_param($prepared1->bind_param('s', $filecode), 'expire_file()');
		$this->execute($prepared1, 'expire_file()');
		
		$date = date('Y-m-d H:i:s');
		$prepared2 = $this->prepare("INSERT INTO `filex_expiration`(`fileid`,`date`) VALUES (?,?)", 'expire_file()');
		$this->bind_param($prepared2->bind_param('ss', $fileid, $date), 'expire_file()');
		$this->execute($prepared2, 'expire_file()');
		
		return true;
	}
	
	/***
	  *  Validates file password
	  *
	***/
	public function validate_password($password, $filecode) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `filecode`=? AND `password_protection`=?", 'validate_password()');
		$this->bind_param($prepared->bind_param('ss', $filecode, $password), 'validate_password()');
		$this->execute($prepared, 'validate_password()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == '1')
			return true;
		return false;
	}
	
	/***
	  *  Adds new wrong password attempt
	  *
	***/
	public function add_wrong_pass_attempt($fileid) {
		$date = date('Y-m-d H:i:s');
		$prepared = $this->prepare("INSERT INTO `filex_password_attempts`(`fileid`,`date`,`type`) VALUES(?,?,1)", 'add_wrong_pass_attempt()');
		$this->bind_param($prepared->bind_param('is', $fileid, $date), 'add_wrong_pass_attempt()');
		$this->execute($prepared, 'add_wrong_pass_attempt()');
		
		// Second query
		$prepared = $this->prepare("UPDATE `filex_files` SET `wrong_password_attempts` = `wrong_password_attempts`+1 WHERE `id`=?", 'add_wrong_pass_attempt()');
		$this->bind_param($prepared->bind_param('i', $fileid), 'add_wrong_pass_attempt()');
		$this->execute($prepared, 'add_wrong_pass_attempt()');
		
		return true;
	}
	
	/***
	  *  Adds new correct password attempt
	  *
	***/
	public function add_correct_pass_attempt($fileid) {
		$date = date('Y-m-d H:i:s');
		$prepared = $this->prepare("INSERT INTO `filex_password_attempts`(`fileid`,`date`,`type`) VALUES(?,?,2)", 'add_correct_pass_attempt()');
		$this->bind_param($prepared->bind_param('is', $fileid, $date), 'add_correct_pass_attempt()');
		$this->execute($prepared, 'add_correct_pass_attempt()');
		
		// Second query
		$prepared = $this->prepare("UPDATE `filex_files` SET `correct_password_attempts` = `correct_password_attempts`+1 WHERE `id`=?", 'add_correct_pass_attempt()');
		$this->bind_param($prepared->bind_param('i', $fileid), 'add_correct_pass_attempt()');
		$this->execute($prepared, 'add_correct_pass_attempt()');
		
		return true;
	}
	
	/***
	  *  Adds new visit
	  *
	***/
	public function add_visit($filecode, $fileid) {
		$date = date('Y-m-d H:i:s');
		
		$prepared1 = $this->prepare("UPDATE `filex_files` SET `link_visits`=`link_visits`+1 WHERE filecode=?", 'add_visit()');
		$this->bind_param($prepared1->bind_param('s', $filecode), 'add_visit()');
		$this->execute($prepared1, 'add_visit()');
		
		$prepared2 = $this->prepare("INSERT INTO `filex_visits`(`fileid`,`date`) VALUES(?,?)", 'add_visit()');
		$this->bind_param($prepared2->bind_param('is', $fileid, $date), 'add_visit()');
		$this->execute($prepared2, 'add_visit()');
		
		return true;
	}
	
	/***
	  *  Adds new download
	  *
	***/
	public function add_download($filecode, $fileid) {
		$date = date('Y-m-d H:i:s');
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$prepared1 = $this->prepare("UPDATE `filex_files` SET `downloads`=`downloads`+1 WHERE `filecode`=?", 'add_download()');
		$this->bind_param($prepared1->bind_param('s', $filecode), 'add_download()');
		$this->execute($prepared1, 'add_download()');
		
		$prepared2 = $this->prepare("INSERT INTO `filex_downloads`(`fileid`,`date`,`ip`) VALUES(?,?,?)", 'add_download()');
		$this->bind_param($prepared2->bind_param('iss', $fileid, $date, $ip), 'add_download()');
		$this->execute($prepared2, 'add_download()');
		
		return true;
	}
	
	
	/***
	  *  Private functions
	  *
	***/
	private function prepare($query, $func) {
		$prepared = $this->mysqli->prepare($query);
		if(!$prepared)
			die("Couldn't prepare query. inc/mysql.class.php - $func");
		return $prepared;
	}
	private function bind_param($param, $func) {
		if(!$param)
			die("Couldn't bind parameters. inc/mysql.class.php - $func");
		return $param;
	}
	private function execute($prepared, $func) {
		$exec = $prepared->execute();
		if(!$exec)
			die("Couldn't execute query. inc/mysql.class.php - $func");
		return $exec;
	}
	private function query($query, $func) {
		$q = $this->mysqli->query($query);
		if(!$q)
			die("Couldn't run query. inc/mysql.class.php - $func");
		return $q;
	}
	
	/****
	 * Alternative to fetch_object for users who doesn't have MySQL Native Driver
	 * (Single row)
	*****/
	private function prepared_to_sobject($prepared) {
		$parameters = array();
		$metadata = $prepared->result_metadata();
		
		while($field = $metadata->fetch_field())
			$parameters[] = &$row[$field->name];
		call_user_func_array(array($prepared, 'bind_result'), $parameters);
		
		$nrs = 0;
		while($prepared->fetch()) {
			$cls = new stdClass;
			foreach($row as $key => $val)
				$cls->$key = $val;
			$nrs++;
		}
		
		return ($nrs == 0) ? 0 : $cls;
	}
	
	/****
	 * Alternative to fetch_object for users who doesn't have MySQL Native Driver
	 * (Multiple rows)
	*****/
	private function prepared_to_object($prepared) {
		$parameters = array();
		$metadata = $prepared->result_metadata();
		
		while($field = $metadata->fetch_field())
			$parameters[] = &$row[$field->name];
		call_user_func_array(array($prepared, 'bind_result'), $parameters);
		
		$nrs = 0;
		while($prepared->fetch()) {
			$cls = new stdClass;
			foreach($row as $key => $val)
				$cls->$key = $val;
			$results[] = $cls;
			$nrs++;
		}
		
		return ($nrs == 0) ? 0 : $results;
	}
	public function is_mysqlnd() {
		if(function_exists('mysqli_stmt_get_result'))
			return true;
		return false;
	}
	public function __destruct() {
		$this->mysqli->close();
	}
}

$mysql = new Filex_Mysql($mysqli);