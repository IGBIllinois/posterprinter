<?php
////////////////////////////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	download.php
//
//	Downloads the poster file that is to be printed
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////////////////////////////

//Include files for the script to run

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_GET['orderId'])) {

	$orderId = $_GET['orderId'];
	$order = new order($db,$orderId);

	//creates the link to the stored file
	$linkToFile = $order->get_file();	
	//creates the html header that is used to download the file.
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $order->get_filename() . '"');
	header('Content-Length: ' . $order->get_filesize());
	header('Content-Transfer-Encoding: binary');
	//opens up the file so it can be downloaded.
	readfile($linkToFile);

}
?>
