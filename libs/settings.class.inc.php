<?php


class settings {

	public static function get_title() {
		return __TITLE__;

	}
	public static function debug() {
		return __DEBUG__;
	}
	public static function get_version() {
		return __VERSION__;
	}

	public static function site_enabled() {
		return __ENABLED__;
	}
	public static function get_poster_dir() {
		return __POSTER_DIR__;
	}


	public static function get_unoconv_exec() {
		if (file_exists(__UNOCONV_EXEC__)) {
			return __UNOCONV_EXEC__;
		}
		return false;

	}
	public static function get_max_width() {
		return __PRINTER_MAX_WIDTH__;
	}
	public static function get_max_length() {
		return __PRINTER_MAX_LENGTH__;
	}

	public static function get_status() {
		return explode(',',__STATUS__);
	}

	public static function get_admin_email() {
		return __ADMIN_EMAIL__;
	}

	public static function get_order_timeframe() {
		return __ORDER_TIME__;
	}
	public static function get_rush_order_timeframe() {
		return __RUSH_ORDER_TIME__;
	}

	public static function get_valid_filetypes() {
		$result = explode(",",__VALID_FILETYPES__);
		sort($result,SORT_STRING);
		return $result;
	}

	public static function get_ldap_host() {
		return __LDAP_HOST__;

	}
	public static function get_ldap_base_dn() {
		return __LDAP_BASE_DN__;
		
	}
	public static function get_ldap_people_ou() {
		return __LDAP_PEOPLE_OU__;
	}
	public static function get_ldap_group_ou() {
		return __LDAP_GROUP_OU__;
	}

	public static function get_ldap_group() {
		return __LDAP_GROUP__;

	}
	public static function get_ldap_ssl() {
		return __LDAP_SSL__;

	}
	public static function get_ldap_port() {
		return __LDAP_PORT__;

	}

	public static function get_boa_cfop() {
		return __BOA_CFOP__;
	}

	public static function get_boa_activity_code() {
		return __BOA_ACTIVITY_CODE__;
	}

	public static function get_session_name() {
		return __SESSION_NAME__;
	}
	public static function get_session_timeout() {
		return __SESSION_TIMEOUT__;
	}

	public static function get_twig_dir() {
		return __TWIG_DIR__;
	}
}

?>
