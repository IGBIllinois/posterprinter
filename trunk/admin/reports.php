<?php
include_once '../includes/settings.inc.php';
include_once 'includes/functions.inc.php';
include_once 'includes/reports.inc.php';
include_once '../includes/db.class.inc.php';

if (isset($_POST['create_report'])) {

	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
	$data = getReportData($db,$month,$year);
	
	$filename = "PosterReport-" . $month . "-" . $year;
	if ($type == 'csv') {
		$filename .=  ".csv";
		create_csv_report($data,$filename);
	}
	elseif ($type == 'excel2003') {
		$filename .= ".xls";
		create_excel_2003_report($data,$filename);
	}
	elseif ($type == 'excel2007') {
		$filename .= ".xlsx";
		create_excel_2007_report($data,$filename);
	}
	elseif ($type == 'pdf') {
		$filename .= ".pdf";
		create_pdf_report($data,$filename);

	}
}

?>
