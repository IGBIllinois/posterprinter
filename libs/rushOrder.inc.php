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

?>
