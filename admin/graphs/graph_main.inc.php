<?php
require_once '../../includes/settings.inc.php';
require_once '../../vendor/autoload.php';
set_include_path(get_include_path() . ':../../libs');
require_once 'db.class.inc.php';
require_once 'statistics.class.inc.php';
//require_once 'jpgraph.php';
//require_once 'jpgraph_bar.php';
//require_once 'jpgraph_pie.php';
//require_once 'jpgraph_pie3d.php';
//require_once 'jpgraph_line.php';
JpGraph\JpGraph::load();
JpGraph\JpGraph::module('bar');
JpGraph\JpGraph::module('pie');
JpGraph\JpGraph::module('pie3d');
JpGraph\JpGraph::module('line');
$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);
//$theme_class = new SoftyTheme();



?>
