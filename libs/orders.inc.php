<?php

//getPreviousOrders()
//$db - database object
//$month - integer - month of the year
//$year - intenger - year
//returns array of previous orders for given month and year.
function getPreviousOrders($db,$month,$year) {

	$sql = "SELECT orders.*, paperTypes.*,finishOptions.* ";
	$sql .= "FROM orders ";
	$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year ."' AND month(orders_timeCreated)='" . $month ."') ";
	$sql .= "AND (orders.orders_status='Completed' OR orders.orders_status='Cancel') ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

//getCurrentOrders()
//$db - database object
//returns array of all the current orders.
function getCurrentOrders($db) {

	$sql = "SELECT orders.*, rushOrder.* ";
	$sql .= "FROM orders ";
	$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
	$sql .= "WHERE NOT (orders.orders_status='Completed' OR orders.orders_status='Cancel') ";
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

	$sql = "SELECT orders.orders_id as 'Order Number', orders.orders_email as 'Email', ";
	$sql .= "orders.orders_name as 'Full Name', orders.orders_timeCreated as 'Date', ";
	$sql .= "orders.orders_cfop as 'CFOP', orders.orders_activityCode as 'Activity Code', ";
	$sql .= "orders.orders_totalCost as 'Cost', ";
	$sql .= "paperTypes.paperTypes_name as 'Paper Type', paperTypes.paperTypes_cost as 'Paper Type Cost (per Inch)', ";
	$sql .= "finishOptions.finishOptions_name as 'Finish Option', finishOptions.finishOptions_cost as 'Finish Option Cost', ";
	$sql .= "rushOrder.rushOrder_name as 'Rush Order', rushOrder.rushOrder_cost as 'Rush Order Cost', ";
	$sql .= "posterTube.posterTube_name as 'Poster Tube', posterTube.posterTube_cost as 'Poster Tube Cost' ";
	$sql .= "FROM orders ";
	$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
	$sql .= "LEFT JOIN posterTube ON orders.orders_posterTubeId=posterTube.posterTube_id ";
	$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year . "' AND month(orders_timeCreated)='" . $month . "') ";
	$sql .= "AND orders.orders_status='Completed' ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);
}

?>
