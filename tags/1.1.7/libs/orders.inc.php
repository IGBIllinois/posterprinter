<?php
include_once 'db.class.inc.php';

//getPreviousOrders()
//$db - database object
//$month - integer - month of the year
//$year - intenger - year
//returns array of previous orders for given month and year.
function getPreviousOrders($db,$month,$year) {

	$sql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.* ";
	$sql .= "FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id ";
	$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year ."' AND month(orders_timeCreated)='" . $month ."') ";
	$sql .= "AND (status_name='Completed' OR status_name='Cancel') ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

//getCurrentOrders()
//$db - database object
//returns array of all the current orders.
function getCurrentOrders($db) {

	$sql = "SELECT tbl_orders.*, tbl_status.*, tbl_rushOrder.* ";
	$sql .= "FROM tbl_orders ";
	$sql .= "LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id ";
	$sql .= "LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id ";
	$sql .= "WHERE NOT (status_name='Completed' OR status_name='Cancel') ";
	$sql .= "ORDER BY orders_id ASC";	
	return $db->query($sql);


}

//getOrdersReport()
//$db - database object
//$month - integer - month of the year
//$year - integer - year
//returns array of previous orders for given month and year with array keys having pretty names
//this is ment to pass to the report functions to build monthly reports
function getOrdersReport($db,$month,$year) {

	$sql = "SELECT tbl_orders.orders_id as 'Order Number', tbl_orders.orders_email as 'Email', ";
	$sql .= "tbl_orders.orders_name as 'Full Name', tbl_orders.orders_timeCreated as 'Date', ";
	$sql .= "tbl_orders.orders_cfop as 'CFOP', tbl_orders.orders_activityCode as 'Activity Code', ";
	$sql .= "tbl_orders.orders_totalCost as 'Cost' "; 
	$sql .= "FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id ";
	$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
        $sql .= "tbl_paperTypes.paperTypes_cost as 'Cost per Inch', ";
	$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id ";
        $sql .= "tbl_finishoptions.finishoption_cost as 'Cost from Finish Options', ";
        $sql .= "tbl_rushOrder.rushOrder_cost as 'Cost from Rush Order', ";
        $sql .= "tbl_posterTube.posterTube_cost as 'Cost from Poster Tube', ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year . "' AND month(orders_timeCreated)='" . $month . "') ";
	$sql .= "AND status_name='Completed' ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

//getAllStatus()
//$db - database object
//returns array of all the different possible order status.
function getAllStatus($db) {
	$sql = "SELECT * FROM tbl_status";
	return $db->query($sql);
}

//getStatusName()
//$db - database object
//$status_id - integer - status id
//returns the status name base on the status id.
function getStatusName($db,$status_id) {
	$sql = "SELECT status_name FROM tbl_status ";
	$sql .= "WHERE status_id='" . $status_id . "' LIMIT 1";
	$result = $db->query($sql);
	return $result[0]['status_name'];


}
?>
