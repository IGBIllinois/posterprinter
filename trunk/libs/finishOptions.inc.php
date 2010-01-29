<?php
include_once 'db.class.inc.php';

function getFinishOptions($db) {

	$sql = "SELECT * FROM tbl_finishOptions ";
	$sql .= "WHERE finishOptions_available=1 ";
	$sql .= "ORDER BY finishOptions_name ASC";
	return $db->query($sql);
}

function getValidFinishOptions($db,$width,$length) {

	$sql = "SELECT * FROM tbl_finishOptions ";
	$sql .= "WHERE finishOptions_available=1 ";
	$sql .= "AND finishOptions_maxLength>=$length ";
	$sql .= "AND (finishOptions_maxWidth>='" . $width . "' OR finishOptions_maxWidth>='" . $length . "') ";
	$sql .= "ORDER BY finishOptions_name ASC";
	return $db->query($sql);

}

function addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default) {

	$available = 1;
        if ($default == 1) {
        	removeDefaultFinishOption($db);
	}
        else { $default = 0; }
        $sql = "INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available,finishOptions_default)";
	$sql .= "VALUES('" . $name . "','" . $cost . "','" . $maxWidth . "','" . $maxLength . "','" . $available . "','" . $default . "')";
        return $db->insert_query($sql);

}

function deleteFinishOption($db,$finishOptionId) {
	$sql = "UPDATE tbl_finishOptions SET finishOptions_available=0 WHERE finishOptions_id='" . $finishOptionId . "'";
	$db->non_select_query($sql);

}

function removeDefaultFinishOption($db) {
	$sql = "UPDATE tbl_finishOptions SET finishOptions_default=0";
	$db->non_select_query($sql);
}

function setDefaultFinishOption($db,$finishOptionId) {

	removeDefaultFinishOption($db);
	$sql =  "UPDATE tbl_finishOptions SET finishOptions_default=1 WHERE finishOptions_id='" . $finishOptionId . "'";
	$db->non_select_query($sql);

}

function updateFinishOption($db,$finishOptionId,$name,$cost,$maxWidth,$maxLength,$default) {
	deleteFinishOption($db,$finishOptionId);
	return addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default);
}

function getFinishOption($db,$finishOptionId) {
	$sql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_id='" . $finishOptionId . "' LIMIT 1";
	return $db->query($sql);


}

?>