<?php


class settings {

	public static function get_title() {
		return title;

	}
	public static function get_poster_dir() {
		return poster_dir;
	}


	public static function get_unoconv_exec() {
		if (file_exists(unoconv_exec)) {
			return unoconv_exec;
		}
		return false;

	}
	public static function get_max_width() {
		return max_width;
	}
	public static function get_max_length() {
		return max_length;
	}

	public static function get_status() {
		global $conf;
		return $conf['status'];
	}

	public static function get_admin_email() {
		return admin_email;
	}

	public static function get_order_timeframe() {
		return order_time;
	}
	public static function get_rush_order_timeframe() {
		return rush_order_time;
	}

	public static function get_valid_filetypes() {
		global $conf;
		return $conf['valid_filetypes'];
	}
}

?>
