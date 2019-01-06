<?php

require_once 'includes/main.inc.php';

if (isset($_GET['image_path'])) {

	if (file_exists($_GET['image_path'])) {
	header('Content-Type: image/jpeg');
		readfile($_GET['image_path']);
	}

}

?>
