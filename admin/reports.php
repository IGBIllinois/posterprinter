<?php
//////////////////////////////////////////////////////////
//														//
//	reports.php											//
//	Creates an excel report file on order history		//
//	for a particular month								//
//														//
//	By: David Slater									//
//	Date: 1/25/2008										//
//														//
//////////////////////////////////////////////////////////
include 'includes/session.inc.php';
include '../includes/settings.inc.php';
include 'includes/excelwriter.inc.php';

if (isset($_POST['createReport'])) {
	//gets the month and year to create the report from
	$year = $_POST['year'];
	$month = $_POST['month'];

	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	//sql statement to get completed orders from a particular month
	$ordersSql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.* 
	FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
	LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
	LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id 
	WHERE (YEAR(orders_timeCreated)=$year AND month(orders_timeCreated)=$month)
	AND status_name='Completed'
	ORDER BY orders_id ASC";
	
	//runs query and gets previous orders
	$ordersResult = mysql_query($ordersSql,$db);

	if (mysql_num_rows($ordersResult) == 0) {

		echo "No Records";


	}
	else {
		//sets the location and file name of the report
		$reportFileName = "PosterReport-" . $month . "-" . $year . ".xls";
		$reportFileLink = "cache/" . $reportFileName;
		$report = new ExcelWriter($reportFileLink);
		//sets the column headings for the report
		$headings = array("<b>Order Number</b>","<b>Email</b>","<b>Full Name</b>","<b>Date</b>","<b>Cost</b>","<b>CFOP</b>");
		$report->writeLine($headings);
		for ($i=0; $i<mysql_numrows($ordersResult); $i++) {
			
			$orderId = mysql_result($ordersResult,$i,"orders_id");
			$orderEmail = mysql_result($ordersResult,$i,"orders_email");
			$orderName = mysql_result($ordersResult,$i,"orders_name");
			$orderCost = mysql_result($ordersResult,$i,"orders_totalCost");
			$orderCFOP = mysql_result($ordersResult,$i,"orders_cfop");
			$orderDate = mysql_result($ordersResult,$i,"orders_timeCreated");
			//sets the new row to write
			$row = array($orderId,$orderEmail,$orderName,$orderDate,"$" . $orderCost,$orderCFOP);
			//writes the new row into the report file
			$report->writeLine($row);
		}
		//closes the report file
		$report->close();
		//Sets headers then downloads the excel report file.
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition:attachment; filename=$reportFileName");
		readfile($reportFileLink);
		unlink($reportFileLink);

	}

}
//if you reached this page without the proper variables set, it redirects you to the main page
else {
	header("Location: index.php");
}

?>