<?php
include_once '../../includes/settings.inc.php';
set_include_path(get_include_path() . ':../../libs');
include_once 'db.class.inc.php';
include_once 'statistics.class.inc.php';
include_once 'jpgraph.php';
include_once 'jpgraph_bar.php';
$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);


if (isset($_GET['year'])) {

	$year = $_GET['year'];
	$stats = new statistics($db,'','');
	$ordersPerMonthData = $stats->ordersPerMonth($year);
	
	$data_legend;
	$data;
	//print_r($ordersPerMonthData);
	if (count($ordersPerMonthData) > 0) {
	foreach($ordersPerMonthData as $key=>$value) {
		$datax[] = $key;
		$datay[] = $value;
	}
	}
	else {
		$datax[] = $key;
		$datay[] = 0;
	}
	$graph = new Graph(600,500,"auto");
	$graph->SetScale("textlin");
	
	$graph->yaxis->scale->SetGrace(20);
	$graph->SetMarginColor('#ffffff');
	$graph->title->Set("Orders Per Month");
	$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$graph->SetFrame(false,'#ffffff');
	$graph->xaxis->SetTickLabels($datax);
	$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,10);

	$graph->xaxis->SetLabelAngle('55');
	$bplot = new BarPlot($datay);
	$bplot->SetAlign("center");
	//$bplot->value->Show();
	$graph->Add($bplot);
	$graph->Stroke();
}


?>
