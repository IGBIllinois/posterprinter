<?php
include_once 'graph_main.inc.php';

if (isset($_GET['startDate']) && isset($_GET['endDate'])) {

	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$stats = new statistics($db,$startDate,$endDate);
	$finishOptionsData = $stats->popularFinishOptions();
	
	$data_legend;
	$data;
	if (count($finishOptionsData) > 0) {
		foreach($finishOptionsData as $row) {
			$data_legend[] = $row['finishOptions_name'] . " - " . $row['count'];
			$data[] = $row['count'];
		}
	}
	else {
		$data[0] = 1;
		$data_legend[0] = "None";
	}
	
	
	$graph = new PieGraph(600,300,"auto");
	$graph->SetTheme($theme_class);
	$graph->SetShadow();
	$graph->title->Set("Finish Options");
	$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$graph->title->SetColor("#000000");
	$p1 = new PiePlot3d($data);
	$p1->SetAngle(85);
	$p1->SetSize(0.35);
	$p1->SetCenter(0.3,0.5);
	$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->legend->SetPos(0.57,0.2,"left","top");
	$graph->legend->SetLayout("LEGEND_VERT");
	$p1->SetLegends($data_legend);
	$p1->value->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->Add($p1);
	$graph->Stroke();
}
?>
