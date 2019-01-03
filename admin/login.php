<?php
///////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	login.php
//
//	Logs in the admin users so they can view orders and download
//	the poster files
//
//	David Slater
//	April 2007
//
/////////////////////////////////////////////

require_once '../includes/settings.inc.php';
set_include_path(get_include_path() . ':../libs');
require_once 'db.class.inc.php';
require_once 'authentication.inc.php';

$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);

session_start();

//sets first webpage
if (isset($_SESSION['webpage'])) { $webpage = $_SESSION['webpage']; }
else { $webpage = "index.php"; }

//logs in
if (isset($_POST['login'])) {
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$success = authenticate($username,$password,ldap_host,ldap_base_dn,ldap_people_ou,ldap_group_ou,ldap_group,ldap_ssl,ldap_port);
	
	if ($success == "1") {
		
		session_destroy();
		session_start();
		
		$_SESSION['username'] = $username;
		//header("Location: http://" . $_SERVER['SERVER_NAME'] . $webpage);
		header("Location: " . $webpage);
	}
	else {
	
		$login_msg = "<b class='error'>Invalid Login</b><br />";
	
	}
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="../includes/stylesheet.css">
<TITLE>IGB Poster Printer Admin Login</TITLE>
</HEAD>
<BODY OnLoad="document.login.username.focus();">
<div id='container'>
<div id='login_page'>

<h2>Poster Printer Admin Login</h2>


<form id='login' action='login.php' method='post' name='login'>
	<table class='center'>
		<tr>
			<td class='right'>Username:</td>
			<td class='left'><input type='text' name='username' tabindex='1'></td>
		</tr>
		<tr>
			<td class='right'>Password:</td>
			<td class='left'><input type='password' name='password' tabindex='2'></td>
		</tr>
		<tr>
			<td colspan='2' style='padding:5px 0px 10px 0px;' align='center'><input type='submit' value='Login' name='login'></td>
		</tr>
	
	</table>

</form>
<?php if (isset($login_msg)) { echo $login_msg; } ?>
</div>
</div>
</BODY>
</HTML>
