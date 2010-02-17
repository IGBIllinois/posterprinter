<?php
include_once '../../includes/settings.inc.php';
set_include_path(get_include_path() . ':../../libs');
include_once 'db.class.inc.php';
include_once 'statistics.class.inc.php';
include_once 'jpgraph.php';
include_once 'jpgraph_bar.php';
include_once 'jpgraph_pie.php';
include_once 'jpgraph_pie3d.php';
$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);



?>