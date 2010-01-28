<?php

function getPosterTube() {

	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	
	$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes'";
	$posterTubeResult = mysql_query($posterTubeSql,$db);
	$posterTubeHTML = "<tr><td class='td_2'>Poster Tube</td><td class='td_2'>$" . mysql_result($posterTubeResult,0,"posterTube_cost") ."</td>" .
					"<td class='form'><input type='checkbox' name='posterTube' value='1'></td></tr>";






}

function getRushOrder() {

	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	
	$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='Yes'";
	$rushOrderResult = mysql_query($rushOrderSql,$db);
	$rushOrderHTML = "<tr><td class='td_2'>Rush Order</td><td class='td_2'>$" . mysql_result($rushOrderResult,0,"rushOrder_cost") ."</td>" .
					"<td class='form'><input type='checkbox' name='rushOrder' value='1'></td></tr>";

}

function getReportData($db,$month,$year) {

        $sql = "SELECT tbl_orders.orders_id as 'Order Number', tbl_orders.orders_email as 'Email', ";
	$sql .= "tbl_orders.orders_name as 'Full Name', tbl_orders.orders_timeCreated as 'Date', ";
	$sql .= "tbl_orders.orders_cfop as 'CFOP', tbl_orders.orders_activityCode as 'Activity Code', ";
	$sql .= "tbl_orders.orders_totalCost as 'Cost' "; 
	$sql .= "FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id ";
	$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
	$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id "; 
	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year . "' AND month(orders_timeCreated)='" . $month . "') ";
	$sql .= "AND status_name='Completed' ";
	$sql .= "ORDER BY orders_id ASC";
	return $db->query($sql);











}



?>
