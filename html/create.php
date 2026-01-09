<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'includes/main.inc.php';


$_POST = array_map('trim',$_POST);
$id = 0;
$key = 0;
$message = array(functions::alert("ERROR: No Submitted Variables",0));
$valid = 0;
$post = array();
$response_code = 200;


if (isset($_POST['step1'])) {
        $result = poster::verify_dimensions($db,$_POST['width'],$_POST['length']);
	$message = array();
        if (!$result['RESULT']) {
                array_push($message,$result['MESSAGE']);
		$valid = 0;

        }
        else {
		$post = array('width'=>$_POST['width'],
				'length'=>$_POST['length']
		);
		$valid = 1;	
        }
}

elseif (isset($_POST['step2'])) {
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
		array_push($message,functions::alert("Please enter valid additional email addresses",0));
	}
	if (!\IGBIllinois\cfop::verify_format($cfop,$_POST['activityCode'])) {
		$errors = true;
		array_push($message,functions::alert("Please enter a valid CFOP",0));
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

	// Validate that dimensions were provided (detected from file in step2.php)
	if (empty($_POST['width']) || empty($_POST['length']) || $_POST['width'] <= 0 || $_POST['length'] <= 0) {
		$errors = true;
		array_push($message,functions::alert("Could not determine poster dimensions. Please ensure you uploaded a valid file and confirmed the dimensions.",0));
	}

	if (!$errors) {
		$posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
		if (!$posterFileTmpName) {
			array_push($message,functions::alert("Error in moving uploaded file",0));
			$errors = true;
		}
	}

	if (!$errors) {
		$posterThumbFileTmpName = poster::create_image($posterFileTmpName);
		$thumb_result = poster::create_image($posterFileTmpName);
		$_POST['posterThumbFileTmpName'] = $posterThumbFileTmpName['THUMB'];
		$post = $_POST;
		$post['cfop'] = $cfop;
		$post['step3'] = 1;
		$post['posterFileTmpName'] = $posterFileTmpName;
		$post['posterFileName'] = $_FILES['posterFile']['name'];
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

ob_clean();
http_response_code((int)$response_code);
echo $json_result;

?>