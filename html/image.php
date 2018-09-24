<?php

require_once 'includes/main.inc.php';

if (isset($_GET['image'])) {

	$full_path = poster::get_tmp_path() . "/" . $_GET['image'];
	$contents = file_get_contents($full_path);
	header('Content-type: image/jpeg');
	echo $contents;



}








?>
