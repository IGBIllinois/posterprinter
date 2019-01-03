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
		$filename =  get_filename($month,$year,$ext);
		create_csv_report($data,$filename);
	}
	elseif ($type == 'excel2003') {
		$ext = 'xls';
		$filename = get_filename($month,$year,$ext);
		create_excel_2003_report($data,$filename);
	}
	elseif ($type == 'excel2007') {
		$ext = 'xlsx';
		$filename = get_filename($month,$year,$ext);
		create_excel_2007_report($data,$filename);
	}

}

?>
