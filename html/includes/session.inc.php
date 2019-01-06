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

$session = new session(settings::get_session_name());

//If session timeout is reach
if (time() > $session->get_var('timeout') + settings::get_session_timeout()) {
//	header('Location: index.php');
}
//If IP address is different
elseif ($_SERVER['REMOTE_ADDR'] != $session->get_var('ipaddress')) {
 //       header('Location: index.php');
}

else {
	//Reset Timeout
	$session_vars = array('timeout'=>time());
	$session->set_session($session_vars);
}
?>
