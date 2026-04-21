<?php
require_once 'includes/main.inc.php';

if (isset($_GET['image_path'])) {
	$allowed_base = realpath(__DIR__ . '/../' . settings::get_poster_dir());
	$requested = realpath($_GET['image_path']);

	if ($requested && $allowed_base && strpos($requested, $allowed_base) === 0 && file_exists($requested)) {
		$mime = mime_content_type($requested);
		if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/tiff'])) {
			header('Content-Type: ' . $mime);
			readfile($requested);
		}
	}
}

?>
