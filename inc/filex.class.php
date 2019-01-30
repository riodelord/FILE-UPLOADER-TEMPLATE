<?php

class Filex {
	public function create_fake_name($chars, $length) {
		$fake_name = null;
		for($i = 0; $i < $length; $i++)
			$fake_name .= $chars{rand(0, strlen($chars)-1)};
		return $fake_name;
	}
	
	public function get_file_size_mb($file) {
		$fs = filesize($file);
		$fs = number_format($fs / 1048576, 2);
		return $fs;
	}
	
	public function parse_date($date) {
		$str = strtotime($date);
		return date('F jS, Y \a\t H:i:s', $str);
	}
}

$filex = new Filex;