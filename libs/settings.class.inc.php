<?php


class settings {

	const ldap_port = 389;
	const ldap_ssl = false;

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

	public static function get_ldap_host() {
		return ldap_host;

	}
	public static function get_ldap_base_dn() {
		return ldap_base_dn;
		
	}
	public static function get_ldap_people_ou() {
		return ldap_people_ou;
	}
	public static function get_ldap_group_ou() {
		return ldap_group_ou;
	}

	public static function get_ldap_group() {
		return ldap_group;

	}
	public static function get_ldap_ssl() {
		if (defined(ldap_ssl)) {
			return ldap_ssl;
		}
		else {
			return ldap_ssl;
		}

	}
	public static function get_ldap_port() {
		if (defined(ldap_port)) {
			return ldap_port;
		}
		else {
			return self::ldap_port;
		}

	}
}

?>
