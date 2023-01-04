<?php
//////////////////////////////////////////////////
//						//
//	session.inc.php				//
//						//
//	Used to verify the user is		// 
//	logged in before proceeding		//
//						//
//	David Slater				//
//	May 2009				//
//						//
//////////////////////////////////////////////////

$session = new \IGBIllinois\session(settings::get_session_name());

//If session timeout is reach
if (time() > $session->get_var('timeout') + settings::get_session_timeout()) {
	$session->destroy_session();
	header('Location: index.php');
}
//If IP address is different
elseif ($_SERVER['REMOTE_ADDR'] != $session->get_var('ipaddress')) {
	$session->destroy_session();
	header('Location: index.php');
}
elseif (!(isset($_GET['session'])) || ($_GET['session'] != $session->get_session_id())) {
        $session->destroy_session();
        header('Location: index.php');

}

else {
	//Reset Timeout
	$session_vars = array('timeout'=>time());
	$session->set_session($session_vars);
}
?>
