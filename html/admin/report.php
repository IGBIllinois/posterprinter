<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_POST['create_report'])) {

	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$data = functions::getOrdersReport($db,$month,$year);
	$filename =  "PosterReport-" . $month . "-" . $year . "." . $type;
	

}

switch ($type) {
	case 'csv':
		report::create_csv_report($data,$filename);
		break;
	case 'xls':
	      	report::create_excel_2003_report($data,$filename);
                break;
	case 'xlsx':
		report::create_excel_2007_report($data,$filename);
		break;
}

?>
