<?php
require_once 'db.class.inc.php';

//getFinishOptions()
//$db - database object
//returns array of all the enabled finish options.
function getFinishOptions($db) {

	$sql = "SELECT finishOptions_id as id, finishOptions_name as name, ";
	$sql .= "finishOptions_cost as cost, finishOptions_maxWidth as maxWidth, ";
	$sql .= "finishOptions_maxLength as maxLength, finishOptions_default ";
	$sql .= "FROM tbl_finishOptions ";
	$sql .= "WHERE finishOptions_available=1 ";
	$sql .= "ORDER BY finishOptions_name ASC";
	return $db->query($sql);
}

//getValidFinishOptions()
//$db - database object
//$width - integer - width in inches
//$length - intenger - length in inches
//returns array of finish options that can be used on the poster based on the width and length.
function getValidFinishOptions($db,$width,$length) {

	$sql = "SELECT finishOptions_id as id, finishOptions_name as name, ";
	$sql .= "finishOptions_cost as cost, finishOptions_maxWidth as maxWidth, ";
	$sql .= "finishOptions_maxLength as maxLength, finishOptions_default ";
	$sql .= "FROM tbl_finishOptions ";
	$sql .= "WHERE finishOptions_available='1' ";
	$sql .= "AND finishOptions_maxLength>='" . $length . "' ";
	$sql .= "AND (finishOptions_maxWidth>='" . $width . "' OR finishOptions_maxWidth>='" . $length . "') ";
	$sql .= "ORDER BY finishOptions_name ASC";
	return $db->query($sql);

}

//addFinishOption()
//$db - database object
//$name - string - name of finish option
//$cost - decimal - cost of the finish option
//$maxWidth - integer - maximum width of the finish option in inches
//$maxLength - integer - maximum length of the finish option in inches
//$default - boolean -  makes the finish option the default selected choice.
//returns the new finish option id
function addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default = 0) {

	$errors = 0;
	$message = "";
	if ($name == "") {
		$message .= "<br><b class='error'>Pleae enter finish option name</b>";
		$errors++;
	}
	if (($cost == "") || !preg_match('/^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$/',$cost)) {
		$message .= "<br><b class='error'>Please enter a valid cost</b>";
		$errors++;
	}
	
	if (($maxWidth == "") || ($maxWidth > max_printer_width) || !(preg_match("/^[0-9]{1,2}$/", $maxWidth))) {
		$message .= "<br><b class='error'>Please enter a valid Max Width. Maximum is " . max_printer_width . " inches</b>";
		$errors++;
	}
	if (($maxLength == "") || !(preg_match("/^[0-9]{1,3}$/", $maxLength))) {
		$message .= "<br><b class='error'>Please enter a valid Max Length</b>";
		$errors++;
	}
	
	if ($errors == 0) {
	
		$available = 1;
        if ($default == 1) {
        	removeDefaultFinishOption($db);
		}
		else { $default = 0; }
        $sql = "INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available,finishOptions_default)";
        $sql .= "VALUES('" . $name . "','" . $cost . "','" . $maxWidth . "','" . $maxLength . "','" . $available . "','" . $default . "')";
		$id = $db->insert_query($sql);
		$message = "<br>Finish Option successfully added.";
		return array ('RESULT'=>TRUE,
					'ID'=>$id,
					'MESSAGE'=>$message);
	
	}
	else {
		return array('RESULT'=>FALSE,
					'MESSAGE'=>$message);
		
	}
}

//deleteFinishOption()
//$db - database object
//$finishOptionId - integer - finish option id
//returns true on success of deletion of finish option
function deleteFinishOption($db,$finishOptionId) {
	$sql = "UPDATE tbl_finishOptions SET finishOptions_available=0 WHERE finishOptions_id='" . $finishOptionId . "'";
	$db->non_select_query($sql);

}

//removeDefaultFinishOption()
//$db - database object
//removes the default finish option.  This is a helper function.
function removeDefaultFinishOption($db) {
	$sql = "UPDATE tbl_finishOptions SET finishOptions_default=0";
	$db->non_select_query($sql);
}

//setDefaultFinishOption()
//$db - database object
//$finishOptionId - integer - finish option id
//returns true on success of making the finish option the default
function setDefaultFinishOption($db,$finishOptionId) {

	removeDefaultFinishOption($db);
	$sql =  "UPDATE tbl_finishOptions SET finishOptions_default=1 WHERE finishOptions_id='" . $finishOptionId . "'";
	return $db->non_select_query($sql);

}
//updateFinishOption()
//$db - database object
//$finishOptionId - integer - finish option id
//$name - string - name of finish option
//$cost - decimal - cost of the finish option
//$maxWidth - integer - maximum width of the finish option in inches
//$maxLength - integer - maximum length of the finish option in inches
//$default - boolean -  makes the finish option the default selected choice.
//returns id of the updated finish option.
//this function actually deletes the finish option then creates a new one.  If we really just
//updated the finish option then calculating the cost for previous orders will be inconsistant.
function updateFinishOption($db,$finishOptionId,$name,$cost,$maxWidth,$maxLength,$default) {
	$result = addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default);
	deleteFinishOption($db,$finishOptionId);	
	$message = "<br>Finish Option successfully updated.";
	if ($result['RESULT']) {
		return array('RESULT'=>TRUE,
					'MESSAGE'=>$message);
	}
	else { return $result; }
}

//getFinishOption()
//$db - database object
//$finishOptionId - integer - finish option id
//returns array of information for the select finish option
function getFinishOption($db,$finishOptionId) {
	$sql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_id='" . $finishOptionId . "' LIMIT 1";
	return $db->query($sql);


}

?>
