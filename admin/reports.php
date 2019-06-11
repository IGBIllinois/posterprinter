<?php

require_once 'includes/main.inc.php';
require_once 'orders.inc.php';
require_once 'reports.inc.php';

if (isset($_POST['create_report'])) {

	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$data = getOrdersReport($db,$month,$year);
	
	if ($type == 'csv') {
		$ext = 'csv';
	}
	elseif ($type == 'excel2007') {
		$ext = 'xlsx';
	}
	$filename = get_filename($month,$year,$ext);
}

elseif (isset($_POST['create_boa_report'])) {
	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$data = get_boa_report($db,$month,$year);
	if ($type == 'csv') {
                $ext = 'csv';
        }
        elseif ($type == 'excel2007') {
                $ext = 'xlsx';
        }
	$filename =  "BoaPosterReport-" . $month . "-" . $year . "." . $ext;
}

switch ($type) {
	case 'csv':
		create_csv_report($data,$filename);
		break;
	case 'excel2003':
	      	create_excel_2003_report($data,$filename);
                break;
	case 'excel2007':
		create_excel_2007_report($data,$filename);
		break;
}
?>
