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

$session = new \IGBIllinois\session(settings::get_session_name());
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

		$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
                        settings::get_ldap_base_dn(),
                        settings::get_ldap_port(),
                        settings::get_ldap_ssl(),
                        settings::get_ldap_tls());
		if (settings::get_ldap_bind_user() != "") {
			$ldap->bind(settings::get_ldap_bind_user(),settings::get_ldap_bind_password());
		}
		$success = $ldap->authenticate($username,$password,settings::get_ldap_group());
	
		if ($success) {
			$log->send_log("User " . $username . " logged in");
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
			$log->send_log("User " . $username . " failed logging in",\IGBIllinois\log::ERROR);
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
<link rel="stylesheet" href="../vendor/fortawesome/font-awesome/css/font-awesome.min.css" type="text/css" />

<TITLE><?php echo settings::get_title(); ?> Login</TITLE>
</HEAD>
<body style='padding-top: 70px; padding-bottom: 60px;' OnLoad="document.login.username.focus();">
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'><?php echo settings::get_title(); ?> Administration</a>
	<span class='navbar-text py-0'>Version <?php echo settings::get_version(); ?>&nbsp;
	<a class='btn btn-danger btn-sm' role='button' href='../'>Main Page</a>
	</span>

</nav>
<div class='container'>

<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6 offset-md-3 offset-lg-3 offset-xl-3'>
<br>Only Poster Printer Administrators are allowed to login
<form class='form' role='form'  action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' name='login'>
	<div class='form-group'>
		<label for='username' class='col-form-label'>Username</label>
			<div class='input-group'> 
			<input class='form-control' type='text' autocapitalize='off' tabindex='1' 
				name='username' tabindex='1' placeholder='Username'
				value='<?php if (isset($username)) { echo $username; } ?>'>
			<div class="input-group-append">
				<span class='input-group-text'> <i class='fa fa-user'></i></span>
			</div>
			</div>
	</div>
	<div class='form-group'>
		<label for='password' class='col-form-label'>Password</label>
			<div class='input-group'>
			<input class='form-control' type='password' name='password' tabindex='2'
			placeholder='Password' tabindex='2'>		
			<div class='input-group-append'>
				<span class='input-group-text'><i class='fa fa-lock'></i></span>
			</div>
			</div>

	</div>
	<div class='form-group'>
		<button type='submit' name='login' class='btn btn-primary'>Login</button>
		<div class='float-right'>
			<?php if (settings::get_password_reset_url()) {
				echo "<a class='pull-right' target='_blank' href='" . settings::get_password_reset_url() . "'>Forgot Password?</a>";
			}
			?>
		</div>
	</div>

</form>
<p></p>
<?php if (isset($message)) { echo $message; } ?>

<?php require_once '../includes/footer.inc.php'; ?>
