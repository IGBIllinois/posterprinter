<?php

//getPreviousOrders()
//$db - database object
//$month - integer - month of the year
//$year - intenger - year
//returns array of previous orders for given month and year.
function getPreviousOrders($db,$month,$year) {

	$sql = "SELECT tbl_orders.*, tbl_paperTypes.*,tbl_finishOptions.* ";
	$sql .= "FROM tbl_orders ";
	$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year ."' AND month(orders_timeCreated)='" . $month ."') ";
	$sql .= "AND (tbl_orders.orders_status='Completed' OR tbl_orders.orders_status='Cancel') ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

//getCurrentOrders()
//$db - database object
//returns array of all the current orders.
function getCurrentOrders($db) {

	$sql = "SELECT tbl_orders.*, tbl_rushOrder.* ";
	$sql .= "FROM tbl_orders ";
	$sql .= "LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id ";
	$sql .= "WHERE NOT (tbl_orders.orders_status='Completed' OR tbl_orders.orders_status='Cancel') ";
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
	$sql .= "tbl_orders.orders_totalCost as 'Cost', ";
	$sql .= "tbl_paperTypes.paperTypes_name as 'Paper Type', tbl_paperTypes.paperTypes_cost as 'Paper Type Cost (per Inch)', ";
	$sql .= "tbl_finishOptions.finishOptions_name as 'Finish Option', tbl_finishOptions.finishOptions_cost as 'Finish Option Cost', ";
	$sql .= "tbl_rushOrder.rushOrder_name as 'Rush Order', tbl_rushOrder.rushOrder_cost as 'Rush Order Cost', ";
	$sql .= "tbl_posterTube.posterTube_name as 'Poster Tube', tbl_posterTube.posterTube_cost as 'Poster Tube Cost' ";
	$sql .= "FROM tbl_orders ";
	$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id ";
	$sql .= "LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id ";
	$sql .= "LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year . "' AND month(orders_timeCreated)='" . $month . "') ";
	$sql .= "AND tbl_orders.orders_status='Completed' ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

?>
