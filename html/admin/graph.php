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
		$finishoptions_data = $stats->popularFinishOptions();
		$data;
		$i = 0;
		foreach ($finishoptions_data as $row)
        	{
                	$data[$i]['legend'] = $row['finishOptions_name'];
			$data[$i]['value'] = $row['count'];
			$i++;
	        }
		$title = "Finish Options";
		\IGBIllinois\graphs::pie_graph($data,$title);
		break;

	case 'inches_per_papertype':
		$stats = new statistics($db,$start_date,$end_date);
		$total_inches = $stats->paperTypesTotalInches();
		$data;
                $i = 0;
                foreach ($total_inches as $row)
                {
                        $data[$i]['legend'] = $row['paperTypes_name'];
                        $data[$i]['value'] = $row['totalLength'];
                        $i++;
                }

		$title = "Inches Per Paper Type";
		\IGBIllinois\graphs::pie_graph($data,$title);
		break;

	case 'monthly_avg':
		$stats = new statistics($db,'','');
		$data = $stats->avgOrdersPerMonth();
		$title = "Average Number of Orders";
		\IGBIllinois\graphs::line_graph($data,$title);
		break;
	case 'orders_per_month':
	        $stats = new statistics($db,'','');
		$data = $stats->ordersPerMonth($year);
		$xaxis = "month_name";
		$yaxis = "count";
		$title = "Orders Per Month";
		\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;
	case 'papertypes':
		$stats = new statistics($db,$start_date,$end_date);
		$papertypes_data = $stats->popularPaperTypes();
		$data;
                $i = 0;
                foreach ($papertypes_data as $row)
                {
                        $data[$i]['legend'] = $row['paperTypes_name'];
                        $data[$i]['value'] = $row['count'];
                        $i++;
                }

		$title = "Paper Types";
		\IGBIllinois\graphs::pie_graph($data,$title);
		break;
}






?>
