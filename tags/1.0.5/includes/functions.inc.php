<?php
include_once 'db.class.inc.php';


function getPaperTypes($mysqlSettings) {

	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "ORDER BY paperTypes_name ASC";

	return $db->query($sql);

}

function getValidPaperTypes($width,$length,$mysqlSettings) {
	
	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "AND (paperTypes_width>=$width OR paperTypes_width>=$length) ";
	$sql .= "ORDER BY paperTypes_name ASC";
	return $db->query($sql);

}

function getValidFinishOptions($width,$lenght,$mysqlSettings) {

	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
	$sql = "SELECT * FROM tbl_finishOptions ";
	$sql .= "WHERE finishOptions_available=1 ";
	$sql .= "AND finishOptions_maxLength>=$length ";
	$sql .= "AND (finishOptions_maxWidth>=$width ";
	$sql .= "OR finishOptions_maxWidth>=$length) ";
	$sql .= "ORDER BY finishOptions_name ASC";
	return $db->query($sql);

}

function getPosterTubeCost($mysqlSettings) {

	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);

	$sql = "SELECT posterTube_cost FROM tbl_posterTube ";
	$sql .= "WHERE posterTube_available=1 ";
	$sql .= "AND posterTube_name='Yes'";

	$result = $db->query($sql);
	return $result[0]['posterTube_cost'];

}

function getRushOrderCost($mysqlSettings) {

	$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
	$sql = "SELECT rushOrder_cost FROM tbl_rushOrder ";
	$sql .= "WHERE rushOrder_available=1 ";
	$sql .= "AND rushOrder_name='Yes'";
	$result = $db->query($sql);
	return $result[0]['rushOrder_cost'];
}





?>
