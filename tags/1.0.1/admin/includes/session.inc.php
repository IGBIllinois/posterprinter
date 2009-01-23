<?php
//////////////////////////////////////////////////////////////////////////////
//																			//
//	Poster Printer Order Submittion											//
//	session.inc.php															//
//																			//
//	Used to verify the user is logged in before proceeding					//
//																			//
//	David Slater															//
//	April 2007																//
//																			//
//////////////////////////////////////////////////////////////////////////////
session_start();

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