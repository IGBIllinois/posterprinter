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

ini_set('log_errors',1);
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

set_include_path(get_include_path() . ":../libs");

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

//connects to database
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

$session = new session(settings::get_session_name());

?>
