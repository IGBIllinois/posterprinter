<?php
//////////////////////////////////////////////////////////////////////////////
//																			//
//	Poster Printer Order Submittion											//
//	download.php															//
//																			//
//	Downloads the poster file that is to be printed							//
//																			//
//	David Slater															//
//	April 2007																//
//																			//
//////////////////////////////////////////////////////////////////////////////

//Include files for the script to run

//include 'includes/session.inc.php';
include '../includes/settings.inc.php';

if (isset($_GET['orderId'])) {

	$orderId = $_GET['orderId'];
	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	$orderSql = "SELECT * FROM tbl_orders WHERE orders_id=" . $orderId;
	//runs query and gets the order_id
	$orderResult = mysql_query($orderSql,$db);
	//gets the file name
	$filename = mysql_result($orderResult,0,"orders_fileName");

	//gets the file type
	$fileType = end(explode(".",$filename));
	//creates the link to the stored file
	$linkToFile = "../" . $posterDirectory . "/" . $orderId . "." . $fileType;
	
	//creates the html header that is used to download the file.
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: application-download');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	//opens up the file so it can be downloaded.
	readfile($linkToFile);

}
?>