<?php

function rushOrderInformation($mysqlSettings) {
	
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='Yes'";
	$rushOrderResult = mysql_query($rushOrderSql,$db);
	$rushOrderCost = mysql_result($rushOrderResult,0,'rushOrder_cost');
	$rushOrderId = mysql_result($rushOrderResult,0,'rushOrder_id');
	mysql_close($db);
	$rushOrderArray = array('id' => $rushOrderId,
							'cost' => $rushOrderCost
						);
	return $rushOrderArray;

}
function posterTubeInformation($mysqlSettings) {
	
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes'";
	$posterTubeResult = mysql_query($posterTubeSql,$db);
	$posterTubeCost = mysql_result($posterTubeResult,0,'posterTube_cost');
	$posterTubeId = mysql_result($posterTubeResult,0,'posterTube_id');
	mysql_close($db);
	$posterTubeArray = array('id' => $posterTubeId,
							'cost' => $posterTubeCost
							);
	return $posterTubeArray;
							

}
include_once 'includes/main.inc.php';

//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

if (isset($_POST['updatePosterTube'])) {
	$posterTubeCost = $_POST['posterTubeCost'];
	$posterTubeOldId = $_POST['posterTubeId'];
	
	if (($posterTubeCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$posterTubeCost)) {
		$posterTubeMsg = "<b class='b'>Please enter a valid cost</b>";
	}
	else {
		$posterTubeSql = "UPDATE tbl_posterTube SET posterTube_available=0 WHERE posterTube_id=$posterTubeOldId";
		mysql_query($posterTubeSql,$db);
		$posterTubeSql = "INSERT INTO tbl_posterTube(posterTube_name,posterTube_cost,posterTube_available) VALUES('Yes',$posterTubeCost,1)";
		mysql_query($posterTubeSql,$db);
		$posterTubeId = mysql_insert_id($db);
	}
	
	$rushOrderInfo = rushOrderInformation($mysqlSettings);
	$rushOrderId = $rushOrderInfo['id'];
	$rushOrderCost = $rushOrderInfo['cost'];

}
elseif (isset($_POST['updateRushOrder'])) {
	$rushOrderCost = $_POST['rushOrderCost'];
	$rushOrderOldId = $_POST['rushOrderId'];
	if (($rushOrderCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$rushOrderCost)) {
		$rushOrderMsg = "<b class='b'>Please enter a valid cost</b>";
	}
	else {
		$rushOrderSql = "UPDATE tbl_rushOrder SET rushOrder_available=0 WHERE rushOrder_id=$rushOrderOldId";
		mysql_query($rushOrderSql,$db);
		$rushOrderSql = "INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES('Yes',$rushOrderCost,1)";
		mysql_query($rushOrderSql,$db);
		$rushOrderId = mysql_insert_id($db);
	
	}
	
	$posterTubeInfo = posterTubeInformation($mysqlSettings);
	$posterTubeId = $posterTubeInfo['id'];
	$posterTubeCost = $posterTubeInfo['cost'];
	
}
else {
	$posterTubeInfo = posterTubeInformation($mysqlSettings);
	$posterTubeId = $posterTubeInfo['id'];
	$posterTubeCost = $posterTubeInfo['cost'];
	
	$rushOrderInfo = rushOrderInformation($mysqlSettings);
	$rushOrderId = $rushOrderInfo['id'];
	$rushOrderCost = $rushOrderInfo['cost'];

}

include 'includes/header.inc.php';
?>

<script language="JavaScript">

function confirmUpdate()
{
var agree=confirm("Are you sure you wish to update?");
if (agree)
	return true ;
else
	return false ;
}
</script>

<form method='post' action='otherOptions.php'>
<table class='table_2'>
	<tr>
		<th colspan='3'>Poster Tube</th>
	</tr>
    <tr><td>Price</td>
    	<td>$<input type='text' name='posterTubeCost' value='<?php echo $posterTubeCost; ?>' maxlength='6' size='6'></td>
    	<td><input type='hidden' name='posterTubeId' value='<?php echo $posterTubeId; ?>' /><input type='submit' name='updatePosterTube' value='Update Price' onClick='return confirmUpdate()'/></td>
	</tr>
</table>

<?php if (isset($posterTubeMsg)){echo $posterTubeMsg; }?>
<br />
<br />

<table class='table_2'>
	<tr><th colspan='3'>Rush Order</th></tr>
	<tr><td>Price</td>
    <td>$<input type='text' name='rushOrderCost' value='<?php echo $rushOrderCost; ?>' maxlength='6' size='6'></td>
    <td><input type='hidden' name='rushOrderId' value='<?php echo $rushOrderId; ?>' /><input type='submit' name='updateRushOrder' value='Update Price' onClick='return confirmUpdate()'/></td>
</table>
</form>

<?php include 'includes/footer.inc.php'; ?>
