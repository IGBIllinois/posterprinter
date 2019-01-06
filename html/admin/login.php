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

$session = new session(__SESSION_NAME__);
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
                $message = functions::alert("Please enter your username",false);
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
			$message = functions::alert("Invalid Username or Password",false);
	
		}
	
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css"
        href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../vendor/fortawesome/font-awesome/css/font-awesome.css" type="text/css" />

<TITLE><?php echo settings::get_title(); ?> Login</TITLE>
</HEAD>
<body style='padding-top: 70px; padding-bottom: 60px;'>
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'><?php echo settings::get_title(); ?> Administration</a>
	<span class='navbar-text py-0'>Version <?php echo settings::get_version(); ?>&nbsp;
	<a class='btn btn-danger btn-sm' role='button' href='../'>Main Page</a>
	</span>

</nav>
<div class='container'>

<div class='col-md-6 col-lg-6 col-xl-6 offset-md-3 offset-lg-3 offset-xl-3'>

<form class='form' role='form'  action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' name='login'>
	<div class='form-group row'>
		<label for='username' class='col-form-label'>Username</label>
		<div class='col-md-8 col-lg-8 col-xl-8'>
			<div class='input-group'> 
			<input class='form-control' type='text'
				name='username' tabindex='1' placeholder='Username'
				value='<?php if (isset($username)) { echo $username; } ?>'>
			<div class="input-group-append">
				<span class='input-group-text'> <span class='fa fa-user'></span></span>
			</div>
			</div>
		</div>
	</div>
	<div class='form-group row'>
		<label for='password' class='col-form-label'>Password</label>
		<div class='col-md-8 col-lg-8 col-xl-8'>
			<div class='input-group'>
			<input class='form-control' type='password' name='password' 
			placeholder='Password' tabindex='2'>		
			<div class='input-group-append'>
				<span class='input-group-text'><span class='fa fa-lock'></span></span>
			</div>
			</div>
		</div>

	</div>
	<div class='row'>
		<button type='submit' name='login' class='btn btn-primary'>Login</button>
	</div>

</form>
<p></p>
<?php if (isset($message)) { echo $message; } ?>
</div>
</body>
</html>
