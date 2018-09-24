<?php

require_once 'includes/main.inc.php';

$start_date = "";
$end_date = "";
$year = date('Y');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}
elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
}

$graph_type = "";
if (isset($_GET['graph_type'])) {
	$graph_type = $_GET['graph_type'];
}


switch ($graph_type) {

	case 'finishoptions':
		$stats = new statistics($db,$start_date,$end_date);
		$data = $stats->popularFinishOptions();
		$title = "Finish Options";
		graphs::pie_graph($data,$title);
		break;

	case 'inches_per_papertype':
		$stats = new statistics($db,$start_date,$end_date);
	        $data = $stats->paperTypesTotalInches();
		$title = "Inches Per Paper Type";
		graphs::pie_graph($data,$title);
		break;

	case 'monthly_avg':
		$stats = new statistics($db,'','');
		$data = $stats->avgOrdersPerMonth();
		$title = "Average Number of Orders";
		graphs::line_graph($data,$title);
		break;
	case 'orders_per_month':
	        $stats = new statistics($db,'','');
		$data = $stats->ordersPerMonth($year);
		$title = "Orders Per Month";
		graphs::barplot($data,$title);
		break;
	case 'papertypes':
		$stats = new statistics($db,$start_date,$end_date);
		$data = $stats->popularPaperTypes();
		$title = "Paper Types";
		graphs::pie_graph($data,$title);
		break;
}






?>
