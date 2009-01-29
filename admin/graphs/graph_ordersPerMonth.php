<?php
include '../includes/statistics.class.inc.php';
include '../../includes/settings.inc.php';
include 'jpgraph.php';

include 'jpgraph_bar.php';


if (isset($_GET['year'])) {

	$year = $_GET['year'];
	$stats = new statistics($mysqlSettings);
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