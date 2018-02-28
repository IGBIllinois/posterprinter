<?php

class poster_tube {

	public static function getPosterTubeCost($db) {

		$sql = "SELECT posterTube_cost FROM posterTube ";
		$sql .= "WHERE posterTube_available=1 ";
		$sql .= "AND posterTube_name='Yes'";
		$result = $db->query($sql);
		return $result[0]['posterTube_cost'];

	}

	public static function getPosterTubes($db) {
		$sql = "SELECT * FROM posterTube WHERE posterTube_available=1";
		return $db->query($sql);

	}

	public static function getPosterTube($db,$posterTubeId) {
		$sql = "SELECT * FROM posterTube WHERE posterTube_id='" . $posterTubeId . "' LIMIT 1"; 
		return $db->query($sql);


	}
	public static function getPosterTubeInfo($db) {
	
		$sql = "SELECT posterTube_id as id, posterTube_cost as cost ";
		$sql .= "FROM posterTube WHERE posterTube_available=1 AND posterTube_name='Yes' LIMIT 1";
		return $db->query($sql);
				
	}

	public static function getPosterTubeStuff($db,$yesno = 0) {
		$name = "No";
		if ($yesno == 1) {
			$name = "Yes";
		}
		$sql = "SELECT posterTube_id as id, posterTube_name as name, posterTube_cost as cost, posterTube_maxWidth as max_width, ";
		$sql .= "posterTube_maxLength as max_length ";
		$sql .= " FROM posterTube WHERE posterTube_available=1 AND posterTube_name='" . $name . "' LIMIT 1";
		$result = $db->query($sql);
		if (count($result)) {
			return $result[0];
		}
		return false;
	

	}

	public static function updatePosterTube($db,$cost) {
		
		if (!verify::verify_cost($cost)) {
			$message = "Please enter a valid poster tube cost.";
			return array('RESULT'=>FALSE,
					'MESSAGE'=>$message);
		}
		else {
			$result = getPosterTubeInfo($db);
			$posterTube_id = $result[0]['id'];
			$update_sql = "UPDATE posterTube SET posterTube_available=0 WHERE posterTube_id='" . $posterTube_id . "' LIMIT 1";
			$db->non_select_query($update_sql);
			$insert_sql = "INSERT INTO posterTube(posterTube_name,posterTube_cost,posterTube_available) VALUES('Yes','" . $cost . "',1)";
			$insert_id = $db->insert_query($insert_sql);
			$message = "Poster Tube cost successfully updated.";
			return array('RESULT'=>TRUE,
					'ID'=>$insert_id,
					'MESSAGE'=>$message);
		}
	

	}

}
?>
