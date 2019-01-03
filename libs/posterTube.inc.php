<?php
require_once 'db.class.inc.php';

function getPosterTubeCost($db) {

	$sql = "SELECT posterTube_cost FROM tbl_posterTube ";
	$sql .= "WHERE posterTube_available=1 ";
	$sql .= "AND posterTube_name='Yes'";
	$result = $db->query($sql);
	return $result[0]['posterTube_cost'];

}

function getPosterTubes($db) {
	$sql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1";
	return $db->query($sql);

}

function getPosterTube($db,$posterTubeId) {
	$sql = "SELECT * FROM tbl_posterTube WHERE posterTube_id='" . $posterTubeId . "' LIMIT 1"; 
	return $db->query($sql);


}
function getPosterTubeInfo($db) {
	
	$sql = "SELECT posterTube_id as id, posterTube_cost as cost ";
	$sql .= "FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes' LIMIT 1";
	return $db->query($sql);
				
}

function updatePosterTube($db,$cost) {
	if (($cost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$cost)) {
		$message = "<b class='error'>Please enter a valid poster tube cost.</b>";
		return array('RESULT'=>FALSE,
					'MESSAGE'=>$message);
	}
	else {
		$result = getPosterTubeInfo($db);
		$posterTube_id = $result[0]['id'];
		$update_sql = "UPDATE tbl_posterTube SET posterTube_available=0 WHERE posterTube_id='" . $posterTube_id . "' LIMIT 1";
		$db->non_select_query($update_sql);
		$insert_sql = "INSERT INTO tbl_posterTube(posterTube_name,posterTube_cost,posterTube_available) VALUES('Yes','" . $cost . "',1)";
		$insert_id = $db->insert_query($insert_sql);
		$message = "<b>Poster Tube cost successfully updated.</b>";
		return array('RESULT'=>TRUE,
					'ID'=>$insert_id,
					'MESSAGE'=>$message);
	}
	

}
?>
