<?php


class settings {
	private const TITLE = "Poster Printer";
	private const DEBUG = false;
	private const ENABLED = true;
	private const ENABLE_LOG = false;
	private const SMTP_PORT = 25;
	private const SMTP_HOST = "localhost";
	private const SMTP_USERNAME = "";
	private const SMTP_PASSWORD = "";
	private const SESSION_TIMEOUT = 3600;
	private const LDAP_HOST = "localhost";
	private const LDAP_PORT = 389;
	private const LDAP_BASE_DN = "";
	private const LDAP_SSL = false;
	private const LDAP_TLS = false;
	private const LDAP_BIND_USER = "";
	private const LDAP_BIND_PASS = "";
	private const MYSQL_HOST = "localhost";
	private const MYSQL_PORT = 3306;
	private const MYSQL_SSL = false;
	private const TIMEZONE = "UTC";

	public static function get_title() {
		if (defined("TITLE") && (TITLE != "")) {
			return TITLE;
		}
		return self::TITLE;

	}
	public static function debug() {
		if (defined("DEBUG") && is_bool(DEBUG)) {
                        return DEBUG;
                }
                return self::DEBUG;

	}
	public static function get_version() {
		return VERSION;
	}

	public static function get_website_url() {
		return WEBSITE_URL;
	}

	public static function site_enabled() {
		if (defined("ENABLED") && is_bool(ENABLED)) {
                        return ENABLED;
                }
                return self::ENABLE;

	}
	public static function get_poster_dir() {
		return POSTER_DIR;
	}


	public static function get_unoconv_exec() {
		if (file_exists(UNOCONV_EXEC)) {
			return UNOCONV_EXEC;
		}
		return false;

	}
	public static function get_max_width() {
		return PRINTER_MAX_WIDTH;
	}
	public static function get_max_length() {
		return PRINTER_MAX_LENGTH;
	}

	public static function get_order_timeframe() {
		return ORDER_TIME;
	}
	public static function get_rush_order_timeframe() {
		return RUSH_ORDER_TIME;
	}

	public static function get_valid_filetypes() {
		$result = explode(",",VALID_FILETYPES);
		sort($result,SORT_STRING);
		return $result;
	}

	public static function get_ldap_host() {
		if (defined("LDAP_HOST")) {
			return LDAP_HOST;
		}
		return self::LDAP_HOST;
	}

	public static function get_ldap_port() {
		if (defined("LDAP_PORT")) {
			return LDAP_PORT;
		}
		return self::LDAP_PORT;
	}
	public static function get_ldap_base_dn() {
		if (defined("LDAP_BASE_DN")) {
			return LDAP_BASE_DN;
		}
		return self::LDAP_BASE_DN;
	}
	public static function get_ldap_ssl() {
		if (defined("LDAP_SSL")) {
			return LDAP_SSL;
		}
		return self::LDAP_SSL;
	}

	public static function get_ldap_tls() {
		if (defined("LDAP_TLS")) {
			return LDAP_TLS;
		}
		return self::LDAP_TLS;
	}
	public static function get_ldap_bind_user() {
		if (defined("LDAP_BIND_USER")) {
			return LDAP_BIND_USER;
		}
		return self::LDAP_BIND_USER;
	}
	public static function get_ldap_bind_password() {
		if (defined("LDAP_BIND_PASS")) {
			return LDAP_BIND_PASS;
		}
		return self::LDAP_BIND_PASS;
	}
	public static function get_ldap_group() {
		if (defined("LDAP_GROUP")) {
			return LDAP_GROUP;
		}
		return self::LDAP_GROUP;

	}

	public static function get_boa_cfop() {
		return BOA_CFOP;
	}

	public static function get_boa_activity_code() {
		return BOA_ACTIVITY_CODE;
	}


	public static function get_session_timeout() {
		if (defined("SESSION_TIMEOUT")) {
			return SESSION_TIMEOUT;
		}
		return self::SESSION_TIMEOUT;
	}
	public static function get_session_name() {
		if (defined("SESSION_NAME")) {
			return SESSION_NAME;
		}
		return NULL;
	}
	public static function get_twig_dir() {
		$dir = dirname(__DIR__) . "/" . TWIG_DIR;
		return $dir;
	}

	public static function get_admin_email() {
		if (defined("ADMIN_EMAIL")) {
                        return ADMIN_EMAIL;
                }
		return false;

	}
	public static function get_admin_name() {
		if (defined("ADMIN_NAME")) {
			return ADMIN_NAME;
		}
		return "";

	}
	public static function get_smtp_host() {
		if (defined("SMTP_HOST")) {
				return SMTP_HOST;
		}
		return self::SMTP_HOST;

	}
	public static function get_smtp_port() {
		if (defined("SMTP_PORT")) {
			return SMTP_PORT;
		}
		return self::SMTP_PORT;

	}

	public static function get_smtp_username() {
		if (defined("SMTP_USERNAME")) {
			return SMTP_USERNAME;
		}
		return false;
	}

	public static function get_smtp_password() {
		if (defined("SMTP_PASSWORD")) {
			return SMTP_PASSWORD;
		}
		return false;

	}

	public static function get_mysql_host() {
		if (defined("MYSQL_HOST")) {
			return MYSQL_HOST;
		}
		return self::MYSQL_HOST;

	}

	public static function get_mysql_user() {
		if (defined("MYSQL_USER")) {
			return MYSQL_USER;
		}
		return false;
	}

	public static function get_mysql_password() {
		if (defined("MYSQL_PASSWORD")) {
			return MYSQL_PASSWORD;
		}
		return false;
	}
	public static function get_mysql_port() {
		if (defined("MYSQL_PORT")) {
			return MYSQL_PORT;
		}
		return self::MYSQL_PORT;

	}

	public static function get_mysql_database() {
		if (defined("MYSQL_DATABASE")) {
			return MYSQL_DATABASE;
		}
		return false;
	}

	public static function get_mysql_ssl() {
		if (defined("MYSQL_SSL")) {
			return MYSQL_SSL;
		}
		return self::MYSQL_SSL;

	}

	public static function get_log_enabled() {
		if (defined("ENABLE_LOG") && (is_bool(ENABLE_LOG))) {
			return ENABLE_LOG;
		}
		return self::ENABLE_LOG;
	}

	public static function get_logfile() {
                if (self::get_log_enabled() && !file_exists(LOG_FILE)) {
                        touch(LOG_FILE);
                }
                return LOG_FILE;

	}
	public static function get_timezone() {
		if (defined("TIMEZONE") && (TIMEZONE != "")) {
			return TIMEZONE;
		}
		return self::TIMEZONE;
	}

	public static function get_cfop_api_key() {
		if (defined("CFOP_API_KEY")) {
			return CFOP_API_KEY;
		}
		return false;
	}

	public static function get_faq_url() {
		if (defined("FAQ_URL")) {
			return FAQ_URL;
		}
		return false;

	}
}

?>
