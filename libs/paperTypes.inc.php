<?php
include_once 'db.class.inc.php';

function getPaperTypes($db) {
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "ORDER BY paperTypes_name ASC";
	return $db->query($sql);

}

function getValidPaperTypes($db,$width,$length) {
	
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "AND (paperTypes_width>='" . $width  . "' OR paperTypes_width>='" . $length . "') ";
	$sql .= "ORDER BY paperTypes_name ASC";;
	return $db->query($sql);

}

function addPaperType($db,$name,$cost,$width,$default) {

	$available = 1;	
        if ($default == 1) {
        	removeDefaultPaperType($db);
	}
	else { $default = 0; }
	$sql = "INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available,paperTypes_default) ";
	$sql .= "VALUES('" . $name . "','" . $cost . "','" . $width . "','" . $available . "','" . $default . "')";
        return $db->insert_query($sql);
}
function setDefaultPaperType($db,$paperTypeId) {
	removeDefaultPaperType($db);
	$sql = "UPDATE tbl_paperTypes SET paperTypes_default=1 WHERE paperTypes_id='" . $paperTypeId . "'";
	$db->non_select_query($sql);

}

function updatePaperType($db,$paperTypeId,$name,$cost,$width,$default) {
	deletePaperType($db,$paperTypeId);
	return addPaperType($db,$name,$cost,$width,$default);
}

function deletePaperType($db,$paperTypeId) {
	$sql = "UPDATE tbl_paperTypes SET paperTypes_available=0 WHERE paperTypes_id=$paperTypeId";
	$db->non_select_query($sql);

}

function getPaperType($db,$paperTypeId) {
	$sql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_id='" . $paperTypeId . "' LIMIT 1";
	return $db->query($sql);




}
function removeDefaultPaperType($db) {
	$sql = "UPDATE tbl_paperTypes SET paperTypes_default=0";
	$db->non_select_query($sql);

}
function removePaperType($db, $paperTypeId) {

	$sql = "UPDATE tbl_paperTypes SET paperTypes_available=0 WHERE paperTypes_id='" . $paperTypeId . "'";
	$db->non_select_query($sql);

}
?>
