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




?>