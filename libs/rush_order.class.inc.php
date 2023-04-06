<?php

class rush_order {


	public static function getRushOrderCost($db) {

		$sql = "SELECT rushOrder_cost FROM rushOrder ";
		$sql .= "WHERE rushOrder_available=1 ";
		$sql .= "AND rushOrder_name='Yes'";
		$result = $db->query($sql);
		return $result[0]['rushOrder_cost'];
	}

	public static function getRushOrders($db) {

		$sql = "SELECT * FROM rushOrder WHERE rushOrder_available=1";
		return $db->query($sql);

	}

	public static function getRushOrder($db,$rushOrderId) {
		$sql = "SELECT * FROM rushOrder WHERE rushOrder_id=:rushorder_id LIMIT 1";
		$parameters = array(
			':rushorder_id'=>$rushOrderId
		);
		return $db->query($sql.$parameters);

	}

	public static function getRushOrderInfo($db) {
	
		$sql = "SELECT rushOrder_id as id, rushOrder_cost as cost ";
		$sql .= "FROM rushOrder ";
		$sql .= "WHERE rushOrder_available=1 AND rushOrder_name='Yes' LIMIT 1";
		return $db->query($sql);
	}

	public static function getRushOrderStuff($db,$yesno = 0) {
		$name = "No";
	        if ($yesno == 1) {
        	        $name = "Yes";
	        }

		$sql = "SELECT rushOrder_id as id, rushOrder_name as name, rushOrder_cost as cost ";
		$sql .= "FROM rushOrder WHERE rushOrder_available='1' and rushOrder_name=:name LIMIT 1";
		$parameters = array(
			':name'=>$name
		);
		$result = $db->query($sql,$parameters);
		if (count($result)) {
			return $result[0];
		}
		return false;


	}

	public static function updateRushOrder($db, $cost) {

		if (!verify::verify_cost($cost)) {
			$message = "Please enter a valid rush order cost";
			return array('RESULT'=>FALSE,
					'MESSAGE'=>$message);
		}
		else {

			$result = getRushOrderInfo($db);
			$rushOrder_id = $result[0]['id'];
			$update_sql = "UPDATE rushOrder SET rushOrder_available=0 WHERE rushOrder_id=:rushorder_id";
			$update_parameters = array(
				':rushorder_id'=>$rushOrder_id
			);
			$db->non_select_query($update_sql,$update_parameters);
			$insert_sql = "INSERT INTO rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES('Yes',:cost,1)";
			$insert_parameters = array(
				':cost'=>$cost
			);
			$insert_id = $db->insert_query($insert_sql,$insert_parameters);
			$message = "Rush Order cost successfully updated.";
			return array('RESULT'=>TRUE,
				'ID'=>insert_id,
				'MESSAGE'=>$message);

		}
	}

}
?>
