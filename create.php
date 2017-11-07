<?php

error_reporting(E_ALL);

require_once 'includes/main.inc.php';

$msg = "POST: ";
foreach ($_POST as $key=>$value) {
	$msg .= $key . ": " . $value . ", ";

}
error_log($msg);
$id = 0;
$key = 0;
$message = array();
$valid = 0;

	foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

	$posterWidth = $_POST['posterWidth'];
        $posterLength = $_POST['posterLength'];
        $paperTypesId = $_POST['paperTypesId'];
        $finishOptionsId = $_POST['finishOptionsId'];
        $activityCode = $_POST['activityCode'];
        $posterFileName = $_FILES['posterFile']['name'];
        $name = stripslashes($_POST['name']);
        $comments = stripslashes($_POST['comments']);
        $activityCode = $_POST['activityCode'];
	//makes the complete CFOP number
        $cfop = $_POST['cfop1'] . "-" . $_POST['cfop2'] . "-" . $_POST['cfop3'] . "-" . $_POST['cfop4'];

        if (isset($_POST['posterTube'])) {
                $posterTube = $_POST['posterTube'];
        }
        else {
                $posterTube = 0;
        }
        if (isset($_POST['rushOrder'])) {
                $rushOrder = $_POST['rushOrder'];
        }
        else {
                $rushOrder = 0;
        }
        //makes the complete CFOP number
        $cfop = $_POST['cfop1'] . "-" . $_POST['cfop2'] . "-" . $_POST['cfop3'] . "-" . $_POST['cfop4'];
	$errors = false;
	
	if (!verify::verify_email($_POST['email'])) {
		$errors = true;
		array_push($message,"Please enter a valid email");

	}

	if (!verify::verify_cfop($cfop)) {
		$errors = true;
		array_push($message,"Please enter a valid CFOP");
	}

	if (!verify::verify_activity_code($_POST['activityCode'])) {
		$errors = true;
		array_push($message,"Please enter a valid activity code");

	}
	if ($posterFileName == "") {
		$errors = true;
		array_push($message,"Please select a poster file to upload");
	}
	if ($_FILES['posterFile']['error'] === "") { $_FILES['posterFile']['error'] = 4; }

	if ((isset($_FILES['posterFile']['error'])) && ($_FILES['posterFile']['error'] !== 0)) {
		$errors = true;
		array_push($message,"Error Uploading File: " . functions::get_upload_error($_FILES['posterFile']['error']));
	}
	if (!verify::verify_filetype($_FILES['posterFile']['name'])) {
		$errors = true;
		array_push($message,"Please upload a valid filetype.  Valid filetypes are ." . implode(", .",settings::get_valid_filetypes()) . ".");
		
	}
	if (!$errors) {
	        $posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
		$posterThumbFileTmpName = poster::create_image($posterFileTmpName);
		$_POST['posterThumbFileTmpName'] = $posterThumbFileTmpName;
		$_POST['posterFileTmpName'] = $posterFileTmpName;
		$_POST['step2'] = 1;
		$valid = true;
	}
echo json_encode(array('valid'=>$valid,
                        'post'=>$_POST,
                        'key'=>$key,
                        'message'=>$message));

?>

