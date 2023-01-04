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
elseif (isset($_POST['create_boa_report'])) {
	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$data = functions::get_boa_report($db,$month,$year);
	$filename = "BoaPosterReport-" . $month . "-" . $year . "." . $type;


}

switch ($type) {
	case 'csv':
		\IGBIllinois\report::create_csv_report($data,$filename);
		break;
	case 'xls':
	      	\IGBIllinois\report::create_excel_2003_report($data,$filename);
                break;
	case 'xlsx':
		\IGBIllinois\report::create_excel_2007_report($data,$filename);
		break;
}
?>
