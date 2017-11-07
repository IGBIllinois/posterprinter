<?php

require_once '../../includes/settings.inc.php';

$include_paths = array('../../libs',
                '../../includes/jpgraph-3.5.0b1/src',
                '../../includes/PHPExcel_1.8.0/Classes');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
function my_autoloader($class_name) {
        if(file_exists("../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');


$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);

require_once 'jpgraph.php';
require_once 'jpgraph_bar.php';
require_once 'jpgraph_pie.php';
require_once 'jpgraph_pie3d.php';
require_once 'jpgraph_line.php';
?>
