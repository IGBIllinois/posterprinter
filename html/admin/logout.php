<?php
require_once 'includes/main.inc.php';
$session = new \IGBIllinois\session(settings::get_session_name());

if ($session->get_var('username') != false) {
	$log->send_log("User " . $session->get_var('username') . " logged out");
}

$session->destroy_session();
header("Location: login.php")
?>
