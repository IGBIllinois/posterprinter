<?php
include_once 'graph_main.inc.php';

$stats = new statistics($db,'','');
$avgOrders = $stats->avgOrdersPerMonth();

foreach($avgOrders as $value) {
		$data_x[] = $value['month_name'];
		$avgOrders_y[] = $value['avg'];
}



$graph = new Graph(600,500,"auto");
$graph->SetScale("textlin");
$graph->SetTheme($theme_class);
$graph->yaxis->scale->SetGrace(20);
$graph->xaxis->SetTickLabels($data_x);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,10);
$graph->xaxis->SetLabelAngle('55');

//Orders
$avgOrders_plot = new LinePlot($avgOrders_y);
$avgOrders_plot->SetLegend('Orders');

$graph->Add($avgOrders_plot);
//Legend
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->legend->SetPos(0.8,0.15,"left","top");
$graph->legend->SetLayout("LEGEND_VERT");

//Display Graph
$graph->Stroke();



?>
