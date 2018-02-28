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

require_once 'includes/main.inc.php';

$session = new session(session_name);
$message = "";
$webpage = $dir = dirname($_SERVER['PHP_SELF']) . "/index.php";
if ($session->get_var('webpage') != "") {
        $webpage = $session->get_var('webpage');
}


//logs in
if (isset($_POST['login'])) {
	
        $username = trim(rtrim($_POST['username']));
        $password = $_POST['password'];

	$error = false;
        if ($username == "") {
                $error = true;
                $message .= functions::alert("Please enter your username",false);
        }
        if ($password == "") {
                $error = true;
                $message .= functions::alert("Please enter your password",false);
        }
        if ($error == false) {

		$success = functions::authenticate($username,$password);
	
		if ($success) {
		
                        $session_vars = array('login'=>true,
	                        'username'=>$username,
        	                'timeout'=>time(),
                	        'ipaddress'=>$_SERVER['REMOTE_ADDR']
                        );
                        $session->set_session($session_vars);


                        $location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
                        header("Location: " . $location);
	
		}
		else {
			$message = functions::alert("Invalid Login",false);
	
		}
	
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css"
        href="../vendor/components/bootstrap/css/bootstrap.min.css">

<TITLE><?php echo settings::get_title(); ?> Login</TITLE>
</HEAD>
<BODY OnLoad="document.login.username.focus();">
<div class='container'>

<div class='col-lg-6 col-lg-offset-3'>
<h2>Poster Printer Admin Login</h2>


<form class='form' id='login' action='login.php' method='post' name='login'>
<div class='form-group'>
	<label for='username'>Username:</label>
	<input class='form-control' type='text' name='username' id='username' tabindex='1'>
</div>
<div class='form-group'>
	<label for='password'>Password:</label>
	<input class='form-control' type='password' name='password' tabindex='2'>
</div>
<div class='form-group'>
	<input class='btn btn-primary' type='submit' value='Login' name='login'>
</div>
	

</form>
<?php if (isset($message)) { echo $message; } ?>
</div>
</div>
</BODY>
</HTML>
