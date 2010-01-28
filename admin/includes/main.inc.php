<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	session.inc.php
//
//	Used to verify the user is logged in before proceeding
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

include_once '../includes/settings.inc.php';
set_include_path(get_include_path() . ':../libs');
include_once 'db.class.inc.php';
session_start();

$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);

if (isset($_SESSION['admin'])) {
	
	$username = $_SESSION['username'];
	$admin = $_SESSION['admin'];
}
else {
	session_start();
	$_SESSION['webpage'] = $_SERVER['REQUEST_URI'];
	header('Location: login.php');
	exit;
	
}
	
?>
