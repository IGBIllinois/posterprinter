<?php
include_once 'includes/main.inc.php';
include_once 'orders.inc.php';
include_once 'reports.inc.php';

if (isset($_POST['create_report'])) {

	$year = $_POST['year'];
	$month = $_POST['month'];
	$type = $_POST['report_type'];
	$data = getOrdersReport($db,$month,$year);
	
	$filename = "PosterReport-" . $month . "-" . $year;
	if ($type == 'csv') {
		$filename .=  ".csv";
		create_csv_report($data,$month,$year);
	}
	elseif ($type == 'excel2003') {
		$filename .= ".xls";
		create_excel_2003_report($data,$month,$year);
	}
	elseif ($type == 'excel2007') {
		$filename .= ".xlsx";
		create_excel_2007_report($data,$month,$year);
	}
	elseif ($type == 'pdf') {
		$filename .= ".pdf";
		create_pdf_report($data,$month,$year);

	}
}

?>
