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

if (isset($_SESSION['loggedIn'])) {
	
	$username = $_SESSION['username'];

}
else {
	header('Location: login.php');
	exit;
	
}
	
?>