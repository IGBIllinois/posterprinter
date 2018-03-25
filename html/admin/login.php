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

<TITLE><?php echo settings::get_title(); ?> Login</TITLE>
</HEAD>
<body style='padding-top: 60px; padding-bottom: 60px;'>
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand' href='#'><?php echo settings::get_title(); ?> Administration</a>
        <div class='collapse navbar-collapse' id='navbarText'>
                <ul class="navbar-nav mr-auto">
                </ul>
                <span class='navbar-text'>
                <p class='navbar-text pull-right'>Version <?php echo settings::get_version(); ?></p>
                </span>

        </div>
</nav>
<div class='container'>

<div class='row col-md-6 offset-md-3'>

<form class='form' role='form'  action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' name='login'>
	<div class='form-group row'>	
		<label for='username'>Username:</label> 
		<input class='form-control' type='text'
			name='username' tabindex='1' placeholder='Username'
			value='<?php if (isset($username)) { echo $username; } ?>'>
		<div class="input-group-append"> 
		<span class='glyphicon glyphicon-user' aria-hidden='true'></span>
		</div>
	</div>
	<div class='form-group row'>
		<label for='password'>Password:</label>
		<input class='form-control' type='password' name='password' 
			placeholder='Password' tabindex='2'>
		<div class="input-group-append">
		<span class='glyphicon glyphicon-lock' aria-hidden='true'></span>
		</div>

	</div>
	<div class='row'>
	<button type='submit' name='login' class='btn btn-primary'>Login</button>
	</div>

</form>
</div>
<div class='row col-md-6 offset-md-3'>
<?php if (isset($message)) { echo $message; } ?>
</div>
</div>
</body>
</html>
