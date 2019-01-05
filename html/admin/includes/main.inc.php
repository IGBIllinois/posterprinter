<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	man.inc.php
//
//	Used to verify the user is logged in before proceeding
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

require_once '../../conf/app.inc.php';
require_once '../../conf/settings.inc.php';
require_once '../../vendor/autoload.php';

$include_paths = array('../../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

if (settings::debug()) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors',1);
}
//connects to database
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);


?>
