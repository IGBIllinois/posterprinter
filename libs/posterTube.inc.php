<?php
include_once 'db.class.inc.php';

function getPosterTubeCost($db) {

	$sql = "SELECT posterTube_cost FROM tbl_posterTube ";
	$sql .= "WHERE posterTube_available=1 ";
	$sql .= "AND posterTube_name='Yes'";
	$result = $db->query($sql);
	return $result[0]['posterTube_cost'];

}

function getPosterTube($db) {
	$sql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1";
	return $db->query($sql);

}

function getPosterTubeInfo($db) {
	
	$sql = "SELECT posterTube_id as id, posterTube_cost as cost ";
	$sql .= "FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes' LIMIT 1";
	return $db->query($sql);
				
}

?>
