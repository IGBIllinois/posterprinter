<?php
require_once 'includes/main.inc.php';
$session = new session(settings::get_session_name());
$session->destroy_session();
header("Location: login.php")
?>
