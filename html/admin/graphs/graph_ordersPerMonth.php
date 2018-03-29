<?php
require_once 'graph_main.inc.php';


if (isset($_GET['year'])) {

	$year = $_GET['year'];
	$stats = new statistics($db,'','');
	$ordersPerMonthData = $stats->ordersPerMonth($year);
	
	$data_legend;
	$data;
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
	$graph = new Graph(900,650,"auto");
	$graph->SetScale("textlin");
	$graph->yaxis->scale->SetGrace(20);
	$graph->SetMarginColor('#ffffff');
	//$graph->title->Set("Orders Per Month");
	$graph->SetFrame(false,'#ffffff');
	$graph->xaxis->SetTickLabels($datax);

	$graph->xaxis->SetLabelAngle('55');
	$bplot = new BarPlot($datay);
	$bplot->SetAlign("center");
	//$bplot->value->Show();
	$graph->Add($bplot);
	$graph->Stroke();
}


?>
