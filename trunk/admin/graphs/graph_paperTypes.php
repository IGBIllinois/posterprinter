<?php
include_once '../../includes/settings.inc.php';
set_include_path(get_include_path() . ':../../libs');
include_once 'db.class.inc.php';
include_once 'statistics.class.inc.php';
include_once 'jpgraph.php';
include_once 'jpgraph_pie.php';
include_once 'jpgraph_pie3d.php';
$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);

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
	
	$graph = new PieGraph(500,250,"auto");
	$graph->SetMargin(40,80,30,200);
	$graph->SetColor('#d3d1d2');
	$graph->SetMarginColor('#ffffff');
	$graph->SetFrame(false,'#d3d1d2');
	$graph->title->Set("Paper Types");
	$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$p1 = new PiePlot($data);
	$p1->SetTheme("earth");
	$p1->SetSize(0.30);
	$p1->SetCenter(0.35,0.55);
	$p1->SetLegends($data_legend);
	$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph ->legend->Pos( 0.05,0.5,"right" ,"center");
	$p1->value->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->Add($p1);
	$graph->Stroke();
}


?>
