<?php

require_once '../../conf/settings.inc.php';
require_once '../../vendor/autoload.php';

$include_paths = array('../../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
function my_autoloader($class_name) {
        if(file_exists("../../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');


$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

JpGraph\JpGraph::load();
JpGraph\JpGraph::module('bar');
JpGraph\JpGraph::module('pie');
JpGraph\JpGraph::module('pie3d');
JpGraph\JpGraph::module('line');

?>
