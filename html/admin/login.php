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

$throttle = new loginthrottle(7, 900); // 7 attempts, 15 minute lockout
$client_ip = $_SERVER['REMOTE_ADDR'];

// Periodically clean up old lockout files
if (rand(1, 50) == 1) {
	$throttle->cleanup();
}

//logs in
if (isset($_POST['login'])) {

	// Check if IP is locked out
	if ($throttle->is_locked($client_ip)) {
		$remaining = ceil($throttle->get_remaining_lockout($client_ip) / 60);
		$log->send_log("Locked out IP " . $client_ip . " attempted login", \IGBIllinois\log::ERROR);
		$message = functions::alert("Too many failed login attempts. Please try again in " . $remaining . " minutes.", false);
	}
	else {
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
			settings::get_ldap_tls()
		);
		if (settings::get_ldap_bind_user() != "") {
			$ldap->bind(settings::get_ldap_bind_user(),settings::get_ldap_bind_password());
		}
		$success = $ldap->authenticate($username,$password,settings::get_ldap_group());
		if ($success) {
			$throttle->reset($client_ip);
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
			$attempts = $throttle->record_failure($client_ip);
			$remaining_attempts = 7 - $attempts;
			$log->send_log("User " . $username . " failed logging in from " . $client_ip . " (attempt " . $attempts . "/7)", \IGBIllinois\log::ERROR);
			if ($remaining_attempts > 0) {
				$message = functions::alert("Invalid Username or Password.",false);
			}
			else {
				$message = functions::alert("Too many failed login attempts. Please try again later.",false);
			}
		}

	}
	} // end lockout check
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
<form class='form' role='form'  action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' name='login'>
	<div class='mb-3'>
		<label for='username' class='col-form-label'>Username</label>
			<div class='input-group'> 
			<input class='form-control' type='text' autocapitalize='off' tabindex='1' 
				name='username' tabindex='1' placeholder='Username'
				value='<?php if (isset($username)) { echo $username; } ?>'>
			<span class='input-group-text'> <i class='fa fa-user'></i></span>
			</div>
	</div>
	<div class='mb-3'>
		<label for='password' class='col-form-label'>Password</label>
			<div class='input-group'>
			<input class='form-control' type='password' name='password' tabindex='2'
			placeholder='Password' tabindex='2'>		
			<span class='input-group-text'><i class='fa fa-lock'></i></span>
			</div>

	</div>
	<div class='mb-3'>
		<button type='submit' name='login' class='btn btn-primary'>Login</button>
		<div class='float-end'>
			<?php if (settings::get_password_reset_url()) {
				echo "<a target='_blank' href='" . settings::get_password_reset_url() . "'>Forgot Password?</a>";
			}
			?>
		</div>
	</div>

</form>
<p></p>
<?php if (isset($message)) { echo $message; } ?>

<?php require_once '../includes/footer.inc.php'; ?>
