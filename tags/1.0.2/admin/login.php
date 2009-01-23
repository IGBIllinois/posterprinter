<?php
//////////////////////////////////////////////////////////////////////////////
//																			//
//	Poster Printer Order Submittion											//
//	adminlogin.php															//
//																			//
//	Logges in the admin users so they can view orders and download			//
//	the poster files														//
//																			//
//	David Slater															//
//	April 2007																//
//																			//
//////////////////////////////////////////////////////////////////////////////

include 'includes/authentication.inc.php';
include '../includes/settings.inc.php';
session_start();

if (isset($_SESSION['webpage'])) {
	$webpage = $_SESSION['webpage'];
}
else {
	$webpage = "/posterprinter/admin/index.php";
}

if (isset($_POST['login'])) {
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	
	$success = authenticate($username,$password,$authenticationSettings);
	
	if ($success == "1") {
		
		session_destroy();
		session_start();
		
		$_SESSION['username'] = $username;
		$_SESSION['admin'] = True;
		header("Location: http://" . $_SERVER['SERVER_NAME'] . $webpage);
	}
	elseif ($success != "1") {
	
		echo "<b class='error'>Invalid Login</b><br />";
	
	}
	
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="../includes/stylesheet.css">
<TITLE>IGB Poster Printer Admin Login</TITLE>
</HEAD>
<BODY OnLoad="document.login.username.focus();">
<div id="content_center">

<h2>Poster Printer Admin Login</h2>


<center>
<form id='login' action='login.php' method='post' name='login'>
	<table bgcolor='white' class='table_2'>
		<tr>
			<td>Username:</td>
			<td><input type='text' name='username' tabindex='1'></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type='password' name='password' tabindex='2'></td>
		</tr>
		<tr>
			<td colspan='2' style='padding:5px 0px 10px 0px;' align='center'><input type='submit' value='Login' name='login' class='button_1'></td>
		</tr>
	
	</table>

</form>
</center>

</div>
</BODY>
</HTML>