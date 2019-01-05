<?php
require_once 'graph_main.inc.php';

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {

	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$stats = new statistics($db,$start_date,$end_date);
	$paperTypesData = $stats->paperTypesTotalInches();
	
	$data_legend;
	$data;
	
	if (count($paperTypesData) > 0) {
		foreach($paperTypesData as $row) {
			$data_legend[] = $row['paperTypes_name'] . " - " . $row['totalLength'] . "\"";
			$data[] = $row['totalLength'];
		}
	}
	else{
		$data[0] = 1;
		$data_legend[0] = "None";
	}
	
	$graph = new PieGraph(900,600,"auto");
	$graph->SetShadow();
	$graph->title->Set("Inches Per Paper Type");
	$graph->title->SetColor("#000000");
	$p1 = new PiePlot3d($data);
	$p1->SetAngle(85);
	$p1->SetSize(0.35);
	$p1->SetCenter(0.3,0.5);
	$graph->legend->SetPos(0.57,0.2,"left","top");
	$graph->legend->SetLayout("LEGEND_VERT");
	$p1->SetLegends($data_legend);
	$graph->Add($p1);
	$graph->Stroke();
	
}
?>
