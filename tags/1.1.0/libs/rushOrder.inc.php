<?php
include_once 'db.class.inc.php';

function getRushOrderCost($db) {

	$sql = "SELECT rushOrder_cost FROM tbl_rushOrder ";
	$sql .= "WHERE rushOrder_available=1 ";
	$sql .= "AND rushOrder_name='Yes'";
	$result = $db->query($sql);
	return $result[0]['rushOrder_cost'];
}

function getRushOrders($db) {

	$sql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1";
	return $db->query($sql);

}

function getRushOrder($db,$rushOrderId) {
	$sql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_id='" . $rushOrderId . "' LIMIT 1";
	return $db->query($sql);

}
function getRushOrderInfo($db) {
	
	$sql = "SELECT rushOrder_id as id, rushOrder_cost as cost ";
	$sql .= "FROM tbl_rushOrder ";
	$sql .= "WHERE rushOrder_available=1 AND rushOrder_name='Yes' LIMIT 1";
	return $db->query($sql);
}

function updateRushOrder($db, $cost) {

	$result = getRushOrderInfo($db);
	$rushOrder_id = $result[0]['id'];
	$update_sql = "UPDATE tbl_rushOrder SET rushOrder_available=0 WHERE rushOrder_id='" . $rushOrder_id . "'";
	$db->non_select_query($update_sql);

	$insert_sql = "INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES('Yes','" . $cost . "',1)";
	return $db->insert_query($insert_sql);

}

?>
