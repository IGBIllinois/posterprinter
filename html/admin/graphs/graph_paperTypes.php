<?php
require_once 'graph_main.inc.php';

if (isset($_GET['startDate']) && isset($_GET['endDate'])) {

	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$stats = new statistics($db,$startDate,$endDate);
	$paperTypesData = $stats->popularPaperTypes();
	
	$data_legend;
	$data;
	
	if (count($paperTypesData) > 0) {
		foreach($paperTypesData as $row) {
			$data_legend[] = $row['paperTypes_name'] . " - " . $row['count'];
			$data[] = $row['count'];
		}
	}
	else{
		$data[0] = 1;
		$data_legend[0] = "None";
	
	
	}
	
	$graph = new PieGraph(600,300,"auto");
	$graph->title->Set("Paper Types");
	$graph->title->SetColor("#000000");
	$p1 = new PiePlot3d($data);
	$p1->SetAngle(85);
	$p1->SetSize(0.35);
	$p1->SetCenter(0.3,0.5);
	$p1->SetLegends($data_legend);
	$graph->legend->SetPos(0.6,0.2,"left","top");
	$graph->legend->SetLayout("LEGEND_VERT");
	$graph->Add($p1);
	$graph->Stroke();
}


?>
