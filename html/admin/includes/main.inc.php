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

require_once '../includes/settings.inc.php';

$include_paths = array('../libs',
                '../includes/jpgraph-3.5.0b1/src',
                '../includes/PHPExcel_1.8.0/Classes');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');


//connects to database
$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);	
?>
