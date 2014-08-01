<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	man.inc.php
//
//	Used to verify the user is logged in before proceeding
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

include_once '../includes/settings.inc.php';
set_include_path(get_include_path() . ':../libs');


session_start();
if (isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	include_once 'db.class.inc.php';
	$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);
}
else {
	$_SESSION['webpage'] = $_SERVER['REQUEST_URI'];
	header('Location: login.php');
	exit;
	
}
	
?>
