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

	/*try {
		$cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
		$cfop_obj->validate_cfop($cfop,$_POST['activityCode']);
			
	}
	catch (\Exception $e) {
		$error = true;
		array_push($message,functions::alert($e->getMessage(),0));
	}*/

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
	$posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
	$verify_poster_size = verify::verify_poster_size(poster::get_tmp_path() . "/" . $posterFileTmpName,$_POST['width'],$_POST['length']);
	if (!$verify_poster_size['valid']) {
		$errors = true;
		$err_message = "Submitted poster is not same size as the width and length that was specified.";
		$err_message .= "<br>Please adjust the size of your poster or submit a new order with correct width and length.";
		$err_message .= "<br>Poster width and length is " . $verify_poster_size['width'] . "x" . $verify_poster_size['length'] . ". Submitted width and length is " . $_POST['width'] . "x" . $_POST['length'];
		array_push($message,functions::alert($err_message,0));

	}
	if (!$errors) {
		if (!$posterFileTmpName) {
			array_push($message,functions::alert("Error in moving uploaded file",0));
		}
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
