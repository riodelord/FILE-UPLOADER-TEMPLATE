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
		$prepared = $this->prepare("SELECT `val` FROM `filex_settings` WHERE `setting`=?", 'get_setting()');
		$this->bind_param($prepared->bind_param('s', $setting), 'get_setting()');
		$this->execute($prepared, 'get_setting()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$row = $result->fetch_object();
		}else{
			$prepared->bind_result($val);
			$prepared->fetch();
			return $val;
		}
		
		return $row->val;
	}
	
	/***
	  *  Updates Filex setting
	  *
	***/
	public function update_setting($setting, $val) {
		$prepared = $this->prepare("UPDATE `filex_settings` SET `val`=? WHERE `setting`=?", 'update_setting()');
		$this->bind_param($prepared->bind_param('ss', $val, $setting), 'update_setting()');
		$this->execute($prepared, 'update_setting()');
		
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
	  *  Gets files visits for the graphs.
	  *
	***/
	public function get_files_visitors($type) {
		if($type == 'THIS_WEEK') {
			$days = array(
				date('Y-m-d', strtotime('sunday last week')),
				date('Y-m-d', strtotime('monday this week')),
				date('Y-m-d', strtotime('tuesday this week')),
				date('Y-m-d', strtotime('wednesday this week')),
				date('Y-m-d', strtotime('thursday this week')),
				date('Y-m-d', strtotime('friday this week')),
				date('Y-m-d', strtotime('saturday this week'))
			);
			$days2 = array(
				date('m/d/Y', strtotime('sunday last week')),
				date('m/d/Y', strtotime('monday this week')),
				date('m/d/Y', strtotime('tuesday this week')),
				date('m/d/Y', strtotime('wednesday this week')),
				date('m/d/Y', strtotime('thursday this week')),
				date('m/d/Y', strtotime('friday this week')),
				date('m/d/Y', strtotime('saturday this week'))
			);
			
			$query = "SELECT '{$days2[0]}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '{$days[0]}'";
			for($i = 1; $i <= 6; $i++)
				$query .= " UNION SELECT '{$days2[$i]}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '{$days[$i]}'";
			
			$res = $this->query($query, 'get_files_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
			
		}elseif($type == 'THIS_MONTH') {
			$last = date('d', strtotime('last day of this month'));
			$last = 29;
			
			$query = null;
			for($i = 1; $i <= 28; $i+=7) {
				$dt1 = date('Y-m-').$i;
				$dt2 = date('Y-m-').($i+6);
				$dt11 = date('m/'.($i).'/Y');
				$dt22 = date('m/'.($i+6).'/Y');
				if($i == 1)
					$query .= "SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2'";
				else
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2'";
			}
			
			if($last > 28) {
				if($last == 29) {
					$dt1 = date('Y-m-').'29';
					$dt11 = date('m/29/Y');
					$query .= " UNION SELECT 'On {$dt11}' as `value`, '{$dt11}' as `from`, '0' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '$dt1'";
				}else{
					$dt1 = date('Y-m-').'29';
					$dt2 = date('Y-m-').$last;
					$dt11 = date('m/29/Y');
					$dt22 = date("m/$last/Y");
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2'";
				}
			}
			
			$res = $this->query($query, 'get_files_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[] = array($obj->value, $obj->from, $obj->to, $obj->c);
			
			return $endarr;
		}else{
			$year = date('Y');
			$query = "SELECT '1' as `value`, COUNT(*) as c FROM `filex_visits` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '1'";
			for($i = 2; $i <= 12; $i++)
				$query .= "UNION SELECT '{$i}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '{$i}'";
				
			$res = $this->query($query, 'get_files_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}
	}
	
	/***
	  *  Gets visitors of a file for the graphs.
	  *
	***/
	public function get_file_visitors($type, $fileid) {
		if($type == 'THIS_WEEK') {
			$days = array(
				date('Y-m-d', strtotime('sunday last week')),
				date('Y-m-d', strtotime('monday this week')),
				date('Y-m-d', strtotime('tuesday this week')),
				date('Y-m-d', strtotime('wednesday this week')),
				date('Y-m-d', strtotime('thursday this week')),
				date('Y-m-d', strtotime('friday this week')),
				date('Y-m-d', strtotime('saturday this week'))
			);
			$days2 = array(
				date('m/d/Y', strtotime('sunday last week')),
				date('m/d/Y', strtotime('monday this week')),
				date('m/d/Y', strtotime('tuesday this week')),
				date('m/d/Y', strtotime('wednesday this week')),
				date('m/d/Y', strtotime('thursday this week')),
				date('m/d/Y', strtotime('friday this week')),
				date('m/d/Y', strtotime('saturday this week'))
			);
			
			$query = "SELECT '{$days2[0]}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '{$days[0]}' && fileid=$fileid";
			for($i = 1; $i <= 6; $i++)
				$query .= " UNION SELECT '{$days2[$i]}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '{$days[$i]}' && fileid=$fileid";
			
			$res = $this->query($query, 'get_file_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
			
		}elseif($type == 'THIS_MONTH') {
			$last = date('d', strtotime('last day of this month'));
			$last = 29;
			
			$query = null;
			for($i = 1; $i <= 28; $i+=7) {
				$dt1 = date('Y-m-').$i;
				$dt2 = date('Y-m-').($i+6);
				$dt11 = date('m/'.($i).'/Y');
				$dt22 = date('m/'.($i+6).'/Y');
				if($i == 1)
					$query .= "SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
				else
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
			}
			
			if($last > 28) {
				if($last == 29) {
					$dt1 = date('Y-m-').'29';
					$dt11 = date('m/29/Y');
					$query .= " UNION SELECT 'On {$dt11}' as `value`, '{$dt11}' as `from`, '0' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = '$dt1' && fileid=$fileid";
				}else{
					$dt1 = date('Y-m-').'29';
					$dt2 = date('Y-m-').$last;
					$dt11 = date('m/29/Y');
					$dt22 = date("m/$last/Y");
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
				}
			}
			
			$res = $this->query($query, 'get_file_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[] = array($obj->value, $obj->from, $obj->to, $obj->c);
			
			return $endarr;
		}else{
			$year = date('Y');
			$query = "SELECT '1' as `value`, COUNT(*) as c FROM `filex_visits` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '1' && fileid=$fileid";
			for($i = 2; $i <= 12; $i++)
				$query .= " UNION SELECT '{$i}' as `value`, COUNT(*) as c FROM `filex_visits` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '{$i}' && fileid=$fileid";
			
			$res = $this->query($query, 'get_file_visitors()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}
	}
	
	/***
	  *  Gets downloads of a file for the graphs.
	  *
	***/
	public function get_file_downloads($type, $fileid) {
		if($type == 'THIS_WEEK') {
			$days = array(
				date('Y-m-d', strtotime('sunday last week')),
				date('Y-m-d', strtotime('monday this week')),
				date('Y-m-d', strtotime('tuesday this week')),
				date('Y-m-d', strtotime('wednesday this week')),
				date('Y-m-d', strtotime('thursday this week')),
				date('Y-m-d', strtotime('friday this week')),
				date('Y-m-d', strtotime('saturday this week'))
			);
			$days2 = array(
				date('m/d/Y', strtotime('sunday last week')),
				date('m/d/Y', strtotime('monday this week')),
				date('m/d/Y', strtotime('tuesday this week')),
				date('m/d/Y', strtotime('wednesday this week')),
				date('m/d/Y', strtotime('thursday this week')),
				date('m/d/Y', strtotime('friday this week')),
				date('m/d/Y', strtotime('saturday this week'))
			);
			
			$query = "SELECT '{$days2[0]}' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = '{$days[0]}' && fileid=$fileid";
			for($i = 1; $i <= 6; $i++)
				$query .= " UNION SELECT '{$days2[$i]}' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = '{$days[$i]}' && fileid=$fileid";
			
			$res = $this->query($query, 'get_file_downloads()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}elseif($type == 'THIS_MONTH') {
			$last = date('d', strtotime('last day of this month'));
			$last = 29;
			
			$query = null;
			for($i = 1; $i <= 28; $i+=7) {
				$dt1 = date('Y-m-').$i;
				$dt2 = date('Y-m-').($i+6);
				$dt11 = date('m/'.($i).'/Y');
				$dt22 = date('m/'.($i+6).'/Y');
				if($i == 1)
					$query .= "SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
				else
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
			}
			
			if($last > 28) {
				if($last == 29) {
					$dt1 = date('Y-m-').'29';
					$dt11 = date('m/29/Y');
					$query .= " UNION SELECT 'On {$dt11}' as `value`, '{$dt11}' as `from`, '0' as `to`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = '$dt1' && fileid=$fileid";
				}else{
					$dt1 = date('Y-m-').'29';
					$dt2 = date('Y-m-').$last;
					$dt11 = date('m/29/Y');
					$dt22 = date("m/$last/Y");
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid";
				}
			}
			
			$res = $this->query($query, 'get_file_downloads()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[] = array($obj->value, $obj->from, $obj->to, $obj->c);
			
			return $endarr;
		}else{
			$year = date('Y');
			$query = "SELECT '1' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '1' && fileid=$fileid";
			for($i = 2; $i <= 12; $i++)
				$query .= " UNION SELECT '{$i}' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '{$i}' && fileid=$fileid";
				
			$res = $this->query($query, 'get_file_downloads()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}
	}

	/***
	  *  Gets correct password attempts of a file for the graphs.
	  *
	***/
	public function get_correct_password_attempts($type, $fileid) {
		if($type == 'THIS_WEEK') {
			$days = array(
				date('Y-m-d', strtotime('sunday last week')),
				date('Y-m-d', strtotime('monday this week')),
				date('Y-m-d', strtotime('tuesday this week')),
				date('Y-m-d', strtotime('wednesday this week')),
				date('Y-m-d', strtotime('thursday this week')),
				date('Y-m-d', strtotime('friday this week')),
				date('Y-m-d', strtotime('saturday this week'))
			);
			$days2 = array(
				date('m/d/Y', strtotime('sunday last week')),
				date('m/d/Y', strtotime('monday this week')),
				date('m/d/Y', strtotime('tuesday this week')),
				date('m/d/Y', strtotime('wednesday this week')),
				date('m/d/Y', strtotime('thursday this week')),
				date('m/d/Y', strtotime('friday this week')),
				date('m/d/Y', strtotime('saturday this week'))
			);
			
			$query = "SELECT '{$days2[0]}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '{$days[0]}' && fileid=$fileid && `type`=2";
			for($i = 1; $i <= 6; $i++) {
				$query .= " UNION SELECT '{$days2[$i]}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '{$days[$i]}' && fileid=$fileid && `type`=2";
			}
			
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}elseif($type == 'THIS_MONTH') {
			$last = date('d', strtotime('last day of this month'));
			$last = 29;
			
			$query = null;
			for($i = 1; $i <= 28; $i+=7) {
				$dt1 = date('Y-m-').$i;
				$dt2 = date('Y-m-').($i+6);
				$dt11 = date('m/'.($i).'/Y');
				$dt22 = date('m/'.($i+6).'/Y');
				if($i == 1)
					$query .= "SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=2";
				else
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=2";
			}
			
			if($last > 28) {
				if($last == 29) {
					$dt1 = date('Y-m-').'29';
					$dt11 = date('m/29/Y');
					$query .= " UNION SELECT 'On {$dt11}' as `value`, '{$dt11}' as `from`, '0' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '$dt1' && fileid=$fileid && `type`=2";
				}else{
					$dt1 = date('Y-m-').'29';
					$dt2 = date('Y-m-').$last;
					$dt11 = date('m/29/Y');
					$dt22 = date("m/$last/Y");
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=2";
				}
			}
			
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[] = array($obj->value, $obj->from, $obj->to, $obj->c);
			
			return $endarr;
		}else{
			$year = date('Y');
			$query = "SELECT '1' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '1' && fileid=$fileid && `type`=2";
			for($i = 2; $i <= 12; $i++)
				$query .= " UNION SELECT '{$i}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '{$i}' && fileid=$fileid && `type`=2";
				
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}
	}
	
	/***
	  *  Gets wrong password attempts of a file for the graphs.
	  *
	***/
	public function get_wrong_password_attempts($type, $fileid) {
		if($type == 'THIS_WEEK') {
			$days = array(
				date('Y-m-d', strtotime('sunday last week')),
				date('Y-m-d', strtotime('monday this week')),
				date('Y-m-d', strtotime('tuesday this week')),
				date('Y-m-d', strtotime('wednesday this week')),
				date('Y-m-d', strtotime('thursday this week')),
				date('Y-m-d', strtotime('friday this week')),
				date('Y-m-d', strtotime('saturday this week'))
			);
			$days2 = array(
				date('m/d/Y', strtotime('sunday last week')),
				date('m/d/Y', strtotime('monday this week')),
				date('m/d/Y', strtotime('tuesday this week')),
				date('m/d/Y', strtotime('wednesday this week')),
				date('m/d/Y', strtotime('thursday this week')),
				date('m/d/Y', strtotime('friday this week')),
				date('m/d/Y', strtotime('saturday this week'))
			);
			
			$query = "SELECT '{$days2[0]}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '{$days[0]}' && fileid=$fileid && `type`=1";
			for($i = 1; $i <= 6; $i++) {
				$query .= " UNION SELECT '{$days2[$i]}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '{$days[$i]}' && fileid=$fileid && `type`=1";
			}
			
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
			
		}elseif($type == 'THIS_MONTH') {
			$last = date('d', strtotime('last day of this month'));
			$last = 29;
			
			$query = null;
			for($i = 1; $i <= 28; $i+=7) {
				$dt1 = date('Y-m-').$i;
				$dt2 = date('Y-m-').($i+6);
				$dt11 = date('m/'.($i).'/Y');
				$dt22 = date('m/'.($i+6).'/Y');
				if($i == 1)
					$query .= "SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=1";
				else
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=1";
			}
			
			if($last > 28) {
				if($last == 29) {
					$dt1 = date('Y-m-').'29';
					$dt11 = date('m/29/Y');
					$query .= " UNION SELECT 'On {$dt11}' as `value`, '{$dt11}' as `from`, '0' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) = '$dt1' && fileid=$fileid && `type`=1";
				}else{
					$dt1 = date('Y-m-').'29';
					$dt2 = date('Y-m-').$last;
					$dt11 = date('m/29/Y');
					$dt22 = date("m/$last/Y");
					$query .= " UNION SELECT 'From {$dt11}<br />to {$dt22}' as `value`, '{$dt11}' as `from`, '{$dt22}' as `to`, COUNT(*) as c FROM `filex_password_attempts` WHERE DATE(`date`) >= '$dt1' && DATE(`date`) <= '$dt2' && fileid=$fileid && `type`=1";
				}
			}
			
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[] = array($obj->value, $obj->from, $obj->to, $obj->c);
			
			return $endarr;
		}else{
			$year = date('Y');
			$query = "SELECT '1' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '1' && fileid=$fileid && `type`=1";
			for($i = 2; $i <= 12; $i++)
				$query .= " UNION SELECT '{$i}' as `value`, COUNT(*) as c FROM `filex_password_attempts` WHERE YEAR(`date`) = '{$year}' AND MONTH(`date`) = '{$i}' && fileid=$fileid && `type`=1";
				
			$res = $this->query($query, 'get_correct_password_attempts()');
			$endarr = array();
			while($obj = $res->fetch_object())
				$endarr[$obj->value] = $obj->c;
			
			return $endarr;
		}
	}
	
	/***
	  *  Counts downloads of a file
	  *
	***/
	public function count_file_downloads($fileid) {
		$query = "SELECT COUNT(*) as c FROM `filex_downloads` WHERE `fileid`=$fileid";
		$res = $this->query($query, 'count_file_downloads()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Returns list of downloads of a file
	  *
	***/
	public function get_file_downloadslist($fileid) {
		$query = "SELECT * FROM `filex_downloads` WHERE `fileid`=$fileid";
		$res = $this->query($query, 'get_file_downloads()');
		return $res;
	}
	
	/***
	  *  Check if certain IP is banned
	  *
	***/
	public function is_banned($ip) {
		$query = "SELECT COUNT(*) as c FROM `filex_blocked` WHERE `ip`='$ip'";
		$res = $this->query($query, 'is_banned()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null || $obj->c == 0)
			return false;
		return true;
	}
	
	/***
	  *  Ban IP by Download ID
	  *
	***/
	public function ban_ip_by_downloadid($id) {
		$date = date('Y-m-d H:i:s');
		
		$query = "INSERT INTO `filex_blocked`(`date`,`ip`) SELECT ?, `ip` FROM `filex_downloads` WHERE `filex_downloads`.`id` = ? ON DUPLICATE KEY UPDATE `filex_blocked`.`ip`=`filex_blocked`.`ip`";
		$prepared = $this->prepare($query, 'ban_ip_by_downloadid()');
		$this->bind_param($prepared->bind_param('si', $date, $id), 'ban_ip_by_downloadid()');
		$this->execute($prepared, 'ban_ip_by_downloadid()');
		
		return true;
	}
	
	/***
	  *  Unban IP by Download ID
	  *
	***/
	public function unban_ip_by_downloadid($id) {
		$query = "DELETE FROM `filex_blocked` WHERE `ip` IN (SELECT `ip` FROM `filex_downloads` WHERE id=?)";
		$prepared = $this->prepare($query, 'unban_ip_by_downloadid()');
		$this->bind_param($prepared->bind_param('i', $id), 'unban_ip_by_downloadid()');
		$this->execute($prepared, 'unban_ip_by_downloadid()');
		return true;
	}
	
	/***
	  *  Ban IP
	  *
	***/
	public function ban_ip($ip) {
		$date = date('Y-m-d H:i:s');
		
		$query = "INSERT INTO `filex_blocked`(`date`,`ip`) VALUES(?,?) ON DUPLICATE KEY UPDATE `ip`=`ip`";
		$prepared = $this->prepare($query, 'ban_ip()');
		$this->bind_param($prepared->bind_param('ss', $date, $ip), 'ban_ip()');
		$this->execute($prepared, 'ban_ip()');
		
		return true;
	}
	
	/***
	  *  Unban IP
	  *
	***/
	public function unban_ip($ip) {
		$query = "DELETE FROM `filex_blocked` WHERE `ip`=?";
		$prepared = $this->prepare($query, 'unban_ip()');
		$this->bind_param($prepared->bind_param('s', $ip), 'unban_ip()');
		$this->execute($prepared, 'unban_ip()');
		return true;
	}
	
	/***
	  *  Get uploads (for stats)
	  *
	***/
	public function get_files_uploads() {
		$day = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
		$day2 = date('m/d/Y', strtotime('-6 days', strtotime(date('Y-m-d'))));
		
		$query = "SELECT '{$day2}' as `value`, COUNT(*) as c FROM `filex_files` WHERE DATE(`date_uploaded`) = '{$day}'";
		
		for($i = -5; $i <= 0; $i++) {
			$day = date('Y-m-d', strtotime("$i days", strtotime(date('Y-m-d'))));
			$day2 = date('m/d/Y', strtotime($day));
			$query .= " UNION SELECT '$day2' as `value`, COUNT(*) as c FROM `filex_files` WHERE DATE(`date_uploaded`) = '{$day}'";
		}
		
		$res= $this->query($query, 'get_files_uploads()');
		$endarr = array();
		while($obj = $res->fetch_object())
			$endarr[$obj->value] = $obj->c;
		return $endarr;
	}
	
	/***
	  *  Get downloads (for stats)
	  *
	***/
	public function get_files_downloads() {
		$day = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
		$day2 = date('m/d/Y', strtotime('-6 days', strtotime(date('Y-m-d'))));
		
		$query = "SELECT '{$day2}' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = '{$day}'";
		
		for($i = -5; $i <= 0; $i++) {
			$day = date('Y-m-d', strtotime("$i days", strtotime(date('Y-m-d'))));
			$day2 = date('m/d/Y', strtotime($day));
			$query .= " UNION SELECT '$day2' as `value`, COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = '{$day}'";
		}
		
		$res= $this->query($query, 'get_files_downloads()');
		$endarr = array();
		while($obj = $res->fetch_object())
			$endarr[$obj->value] = $obj->c;
		return $endarr;
	}
	
	/***
	  *  Get expired files (for stats)
	  *
	***/
	public function get_files_expired() {
		$day = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
		$day2 = date('m/d/Y', strtotime('-6 days', strtotime(date('Y-m-d'))));
		
		$query = "SELECT '{$day2}' as `value`, COUNT(*) as c FROM `filex_expiration` WHERE DATE(`date`) = '{$day}'";
		
		for($i = -5; $i <= 0; $i++) {
			$day = date('Y-m-d', strtotime("$i days", strtotime(date('Y-m-d'))));
			$day2 = date('m/d/Y', strtotime($day));
			$query .= " UNION SELECT '$day2' as `value`, COUNT(*) as c FROM `filex_expiration` WHERE DATE(`date`) = '{$day}'";
		}
		
		$res= $this->query($query, 'get_files_expired()');
		$endarr = array();
		while($obj = $res->fetch_object())
			$endarr[$obj->value] = $obj->c;
		return $endarr;
	}
	
	/***
	  *  Get general statistics
	  *
	***/
	public function get_general_stat($stat) {
		switch($stat) {
			case 1:
				$query = "SELECT COUNT(*) as c FROM `filex_visits`";
				break;
			case 2:
				$query = "SELECT COUNT(*) as c FROM `filex_files`";
				break;
			case 3:
				$query = "SELECT COUNT(*) as c FROM `filex_downloads`";
				break;
			case 4:
				$query = "SELECT COUNT(*) as c FROM `filex_expiration`";
				break;
			case 5:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `password_protected`=0";
				break;
			case 6:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `password_protected`=1";
				break;
			case 7:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `days_expiration`!=0";
				break;
			case 8:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `downloads_expiration`!=0";
				break;
			case 9:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `status`=2 && `days_expiration`!=0 && IF(`downloads_expiration`!=0, `downloads_expiration`, -1) != `downloads`";
				break;
			case 10:
				$query = "SELECT COUNT(*) as c FROM `filex_files` WHERE `status`=2 && `downloads_expiration` != 0 && `downloads_expiration`=`downloads`";
				break;
		}
		
		// Disk usage
		if($stat == 11) {
			$size = 0;
			$scan = scandir('../uploads');
			if($scan != false && count($scan) != 2) {
				foreach($scan as $file){
					if(!is_dir("../uploads/$file"))
						$size += filesize("../uploads/$file");
				}
				return number_format(($size / 1048576), 2);
			}
			return 0;
		}
		
		// Query
		$res = $this->query($query, 'get_general_stat()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Count uploaded files
	  *
	***/
	public function count_uploaded_files() {
		$query = "SELECT COUNT(*) as c FROM `filex_files`";
		$res = $this->query($query, 'count_uploaded_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Count blocked IPs
	  *
	***/
	public function count_blocked_ips() {
		$query = "SELECT COUNT(*) as c FROM `filex_blocked`";
		$res = $this->query($query, 'count_blocked_ips()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Get list of files
	  *
	***/
	public function get_files_list($start, $records) {
		$query = "SELECT * FROM `filex_files` ORDER BY id DESC LIMIT $start,$records";
		$res = $this->query($query, 'count_uploaded_files()');
		return $res;
	}
	
	/***
	  *  Get list of banned IPs
	  *
	***/
	public function get_banned_list($start, $records) {
		$query = "SELECT * FROM `filex_blocked` ORDER BY id DESC LIMIT $start,$records";
		$res = $this->query($query, 'get_banned_list()');
		return $res;
	}
	
	/***
	  *  Get after expired visits
	  *
	***/
	public function get_ae_visits($fileid) {
		$query = "SELECT COUNT(*) as c FROM `filex_visits` INNER JOIN `filex_expiration` ON `filex_visits`.`fileid`=`filex_expiration`.`fileid` WHERE `filex_visits`.`fileid`=$fileid AND `filex_visits`.`date` >= `filex_expiration`.`date`";
		$res = $this->query($query, 'get_ae_visits()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Get file expiration date
	  *
	***/
	public function get_file_expiration_date($fileid) {
		$res = $this->query("SELECT `date` FROM `filex_expiration` WHERE `fileid`=$fileid", 'today_total_file_visits()');
		$obj = $res->fetch_object();
		return $obj->date;
	}
	
	/***
	  *  Get visits of today
	  *
	***/
	public function today_total_file_visits() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_visits` WHERE DATE(`date`) = DATE(NOW())", 'today_total_file_visits()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Get uploaded files of today
	  *
	***/
	public function today_uploaded_files() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_files` WHERE DATE(`date_uploaded`) = DATE(NOW())", 'today_uploaded_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Get downloaded files of today
	  *
	***/
	public function today_downloaded_files() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_downloads` WHERE DATE(`date`) = DATE(NOW())", 'today_downloaded_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Get expired files of today
	  *
	***/
	public function today_expired_files() {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_expiration` WHERE DATE(`date`) = DATE(NOW())", 'today_expired_files()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  Check is a file exists (by filecode)
	  *
	***/
	public function file_mysql_exist($filecode) {
		$prepared = $this->prepare("SELECT COUNT(*) as c FROM `filex_files` WHERE `filecode`=?", 'file_mysql_exist()');
		$this->bind_param($prepared->bind_param('s', $filecode), 'file_mysql_exist()');
		$this->execute($prepared, 'file_mysql_exist()');
		
		if($this->is_mysqlnd()) {
			$result = $prepared->get_result();
			$obj = $result->fetch_object();
			$c = $obj->c;
		}else{
			$prepared->bind_result($c);
			$prepared->fetch();
		}
		
		if($c == 0)
			return false;
		return true;
	}
	
	/***
	  *  Get file info (by filecode)
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
	  *  Get file visits (by fileid)
	  *
	***/
	public function get_file_visits($fileid) {
		$res = $this->query("SELECT COUNT(*) as c FROM `filex_visits` WHERE `fileid`=$fileid", 'get_file_visits()');
		$obj = $res->fetch_object();
		if($obj->c == '' || $obj->c == null)
			return 0;
		return $obj->c;
	}
	
	/***
	  *  check if a file should expire (by fileid)
	  *
	***/
	public function check_file_expiration($fileid) {
		$res = $this->query("SELECT `id` FROM `filex_files` WHERE `days_expiration` != 0 AND DATE_ADD(`date_uploaded`, INTERVAL `days_expiration` DAY) <= NOW() && `status`!=2 && `id`=$fileid", 'check_file_expiration()');
		while($obj = $res->fetch_object()) {
			$date = date('Y-m-d H:i:s');
			$this->query("UPDATE `filex_files` SET `status`=2 WHERE `id`={$obj->id}", 'check_file_expiration()');
			$this->query("INSERT INTO `filex_expiration`(`fileid`,`date`) VALUES({$obj->id}, '{$date}')", 'check_file_expiration()');
		}
		
		return true;
	}
	
	/***
	  *  Delete file (by filecode)
	  *
	***/
	public function delete_file($filecode) {
		$prepared = $this->prepare("DELETE FROM `filex_files` WHERE `filecode`=?", 'delete_file()');
		$this->bind_param($prepared->bind_param('s', $filecode), 'delete_file()');
		$this->execute($prepared, 'delete_file()');
		return true;
	}
	
	/***
	  *  Check and expire files that should expire
	  *
	***/
	public function check_expired_files() {
		$res = $this->query("SELECT `id` FROM `filex_files` WHERE `days_expiration` != 0 AND DATE_ADD(`date_uploaded`, INTERVAL `days_expiration` DAY) <= NOW() && `status` != 2", 'check_expired_files()');
		while($obj = $res->fetch_object()) {
			$date = date('Y-m-d H:i:s');
			$this->query("UPDATE `filex_files` SET `status`=2 WHERE `id`={$obj->id}", 'check_expired_files()');
			$this->query("INSERT INTO `filex_expiration`(`fileid`,`date`) VALUES({$obj->id}, '{$date}')", 'check_expired_files()');
		}
		
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