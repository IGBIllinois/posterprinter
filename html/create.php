<?php

error_reporting(E_ALL);

require_once 'includes/main.inc.php';


$id = 0;
$key = 0;
$message = array("No Submitted Variables");
$valid = 0;
$post = array();

if (isset($_POST['step2'])) {
	foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

        $posterFileName = $_FILES['posterFile']['name'];
	//makes the complete CFOP number
        $cfop = $_POST['cfop1'] . "-" . $_POST['cfop2'] . "-" . $_POST['cfop3'] . "-" . $_POST['cfop4'];

	$posterTube = 0;
        if (isset($_POST['posterTube'])) {
                $posterTube = $_POST['posterTube'];
        }
        
	$rushOrder = 0;
	if (isset($_POST['rushOrder'])) {
                $rushOrder = $_POST['rushOrder'];
        }
        
	$errors = false;
	$message = array();
	if (!verify::verify_name($_POST['name'])) {
                $errors = true;
                array_push($message,"Please enter your first and last name");
        }

	if (!verify::verify_email($_POST['email'])) {
		$errors = true;
		array_push($message,"Please enter a valid email");

	}

	if (!verify::verify_cc_emails($_POST['additional_emails'])) {
		$errors = true;
		array_push($message,"Please eneter valid email addresses");
	}
	if (!verify::verify_cfop($cfop)) {
		$errors = true;
		array_push($message,"Please enter a valid CFOP");
	}

	if (!verify::verify_activity_code($_POST['activityCode'])) {
		$errors = true;
		array_push($message,"Please enter a valid activity code");

	}
	if ($_FILES['posterFile']['name'] == "") {
		$errors = true;
		array_push($message,"Please select a poster file to upload");
	}


	if ($_FILES['posterFile']['error'] === "") { 
		$_FILES['posterFile']['error'] = 4; 
	}

	if ((isset($_FILES['posterFile']['error'])) && ($_FILES['posterFile']['error'] !== 0)) {
		$errors = true;
		array_push($message,"Error Uploading File: " . functions::get_upload_error($_FILES['posterFile']['error']));
	}
	if (!verify::verify_filetype($_FILES['posterFile']['name'])) {
		$errors = true;
		array_push($message,"Please upload a valid filetype.  Valid filetypes are ." . implode(", .",settings::get_valid_filetypes()) . ".");
		
	}
	error_log("Errors: " . $errors);
	if (!$errors) {
	        
		$posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
		if (!$posterFileTmpName) {
			array_push($message,"Error in moving uploaded file");
		}
		//$posterThumbFileTmpName = poster::create_image($posterFileTmpName);
		//$thumb_result = poster::create_image($posterFileTmpName);
		//if (!$thumb_result['RESULT'])) {
		//	error_log('Error making thumbnail');
		//}
		//$_POST['posterThumbFileTmpName'] = $posterThumbFileTmpName;
		$_POST['posterFileTmpName'] = $posterFileTmpName;
		$_POST['step3'] = 1;
		$post = $_POST;
		$valid = true;
	}
}
$json_result = json_encode(array('valid'=>$valid,
                        'post'=>$post,
                        'key'=>$key,
                        'message'=>$message
));
error_log($json_result);
header('Content-type: application/json; charset=UTF-8');
echo $json_result;

?>

