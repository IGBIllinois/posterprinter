<?php

include_once 'includes/main.inc.php';
include_once 'posterTube.inc.php';
include_once 'rushOrder.inc.php';

if (isset($_POST['updatePosterTube'])) {
	$posterTubeCost = $_POST['posterTubeCost'];
	$posterTubeOldId = $_POST['posterTubeId'];
	
	if (($posterTubeCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$posterTubeCost)) {
		$posterTubeMsg = "<b class='b'>Please enter a valid cost.</b>";
	}
	else {
		$posterTubeSql = "UPDATE tbl_posterTube SET posterTube_available=0 WHERE posterTube_id='" . $posterTubeOldId . "' LIMIT 1";
		$db->non_select_query($posterTubeSql);
		$posterTubeSql = "INSERT INTO tbl_posterTube(posterTube_name,posterTube_cost,posterTube_available) VALUES('Yes',$posterTubeCost,1)";
		$posterTubeId = $db->insert_query($posterTubeSql);
	}
	


}
elseif (isset($_POST['updateRushOrder'])) {
	$rushOrderCost = $_POST['rushOrderCost'];
	$rushOrderOldId = $_POST['rushOrderId'];
	if (($rushOrderCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$rushOrderCost)) {
		$rushOrderMsg = "<b class='b'>Please enter a valid cost</b>";
	}
	else {
		$rushOrderSql = "UPDATE tbl_rushOrder SET rushOrder_available=0 WHERE rushOrder_id=$rushOrderOldId";
		$db->non_select_query($rushOrderSql);
		$rushOrderSql = "INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES('Yes',$rushOrderCost,1)";
		$rushOrderId = $db->insert_query($rushOrderSql);
	
	}
	

	
}

$posterTubeInfo = getPosterTubeInfo($db);
$posterTubeId = $posterTubeInfo[0]['id'];
$posterTubeCost = $posterTubeInfo[0]['cost'];
	
$rushOrderInfo = getRushOrderInfo($db);
$rushOrderId = $rushOrderInfo[0]['id'];
$rushOrderCost = $rushOrderInfo[0]['cost'];

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
