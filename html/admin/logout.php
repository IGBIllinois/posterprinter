<?php
include 'includes/main.inc.php';
$session = new session(session_name);
$session->destroy_session();
header("Location: login.php")
?>
