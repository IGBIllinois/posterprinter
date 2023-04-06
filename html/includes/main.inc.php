<?php

//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	man.inc.php
//
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

require_once '../conf/app.inc.php';
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

set_include_path(get_include_path() . ":../libs");

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

if (settings::debug()) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors',1);

}

date_default_timezone_set(settings::get_timezone());

$db = new \IGBIllinois\db(settings::get_mysql_host(),
			settings::get_mysql_database(),
			settings::get_mysql_user(),
			settings::get_mysql_password(),
			settings::get_mysql_ssl(),
			settings::get_mysql_port()
		);

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());


?>
