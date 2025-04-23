<?php
require_once '../../includes/settings.inc.php';
require_once '../../vendor/autoload.php';
set_include_path(get_include_path() . ':../../libs');
require_once 'db.class.inc.php';
require_once 'statistics.class.inc.php';

use mitoteam\jpgraph\MtJpGraph;

MtJpGraph::load();
MtJpGraph::load('bar');
MtJpGraph::load('pie');
MtJpGraph::load('pie3d');
MtJpGraph::load('line');
$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);



?>
