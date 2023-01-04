<?php

require_once '../../../conf/app.inc.php';
require_once '../../../conf/settings.inc.php';
require_once '../../../vendor/autoload.php';

$include_paths = array('../../../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
function my_autoloader($class_name) {
        if(file_exists("../../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');


$db = new \IGBIllinois\db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);

//JpGraph\JpGraph::load();
//JpGraph\JpGraph::module('bar');
//JpGraph\JpGraph::module('pie');
//JpGraph\JpGraph::module('pie3d');
//JpGraph\JpGraph::module('line');

?>
