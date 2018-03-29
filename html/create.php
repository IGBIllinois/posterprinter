<?php

error_reporting(E_ALL);

require_once 'includes/main.inc.php';


$id = 0;
$key = 0;
$message = array("No Submitted Variables");
$valid = 0;
$post = array();

if (isset($_POST['step1'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}
        $result = poster::verify_dimensions($db,$_POST['width'],$_POST['length']);
	$message = array();
        if (!$result['RESULT']) {
                array_push($message,$result['MESSAGE']);
		$valid = 0;

        }
        else {
		$post = $_POST;
		$valid = 1;	
        }

}

elseif (isset($_POST['step2'])) {
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
                array_push($message,functions::alert("Please enter your first and last name",0));
        }

	if (!verify::verify_email($_POST['email'])) {
		$errors = true;
		array_push($message,functions::alert("Please enter a valid email",0));

	}

	if (!verify::verify_cc_emails($_POST['additional_emails'])) {
		$errors = true;
		array_push($message,functions::alert("Please eneter valid email addresses",0));
	}
	if (!verify::verify_cfop($cfop)) {
		$errors = true;
		array_push($message,functions::alert("Please enter a valid CFOP",0));
	}

	if (!verify::verify_activity_code($_POST['activityCode'])) {
		$errors = true;
		array_push($message,functions::alert("Please enter a valid activity code",0));

	}
	if ($_FILES['posterFile']['name'] == "") {
		$errors = true;
		array_push($message,functions::alert("Please select a poster file to upload",0));
	}


	if ($_FILES['posterFile']['error'] === "") { 
		$_FILES['posterFile']['error'] = 4; 
	}

	if ((isset($_FILES['posterFile']['error'])) && ($_FILES['posterFile']['error'] !== 0)) {
		$errors = true;
		array_push($message,functions::alert("Error Uploading File: " . functions::get_upload_error($_FILES['posterFile']['error'],0)));
	}
	if (!verify::verify_filetype($_FILES['posterFile']['name'])) {
		$errors = true;
		array_push($message,functions::alert("Please upload a valid filetype.  Valid filetypes are ." . implode(", ",settings::get_valid_filetypes()) . ".",0));
		
	}
	error_log("Errors: " . $errors);
	if (!$errors) {
	        
		$posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
		if (!$posterFileTmpName) {
			array_push($message,functions::alert("Error in moving uploaded file",0));
		}
		//$posterThumbFileTmpName = poster::create_image($posterFileTmpName);
		//$thumb_result = poster::create_image($posterFileTmpName);
		//if (!$thumb_result['RESULT'])) {
		//	error_log('Error making thumbnail');
		//}
		//$_POST['posterThumbFileTmpName'] = $posterThumbFileTmpName;
		$posterThumbFileTmpName = "";
		$post = $_POST;
		$post['cfop'] = $cfop;
		$post['step3'] = 1;
		$post['posterFileTmpName'] = $posterFileTmpName;
		$post['posterFileName'] = $_FILES['posterFile']['name'];
		$post['posterThumbFileTmpName'] = $posterThumbFileTmpName;
		$post['posterFileSize'] = $_FILES['posterFile']['size'];
		$valid = true;
	}
}
$json_result = json_encode(array('valid'=>$valid,
                        'post'=>$post,
                        'key'=>$key,
                        'message'=>implode('&nbsp;',$message)
));

if (!$json_result) {
	$json_result = json_encode(array('Error', json_last_error_msg()));
}

error_log($json_result);
header('Content-type: application/javascript; charset=UTF-8',true);
echo $json_result;

?>

