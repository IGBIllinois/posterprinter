<?php

include_once 'includes/main.inc.php';
include_once 'posterTube.inc.php';
include_once 'rushOrder.inc.php';

if (isset($_POST['updatePosterTube'])) {
	$posterTubeCost = $_POST['posterTubeCost'];
	
	if (($posterTubeCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$posterTubeCost)) {
		$optionsMsg = "<b class='b'>Please enter a valid poster tube cost.</b>";
	}
	elseif (updatePosterTube($db,$posterTubeCost)) {
		$optionsMsg = "<b>Poster Tube cost successfully updated.</b>";
		
	}
	
}
elseif (isset($_POST['updateRushOrder'])) {
	$rushOrderCost = $_POST['rushOrderCost'];
	if (($rushOrderCost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$rushOrderCost)) {
		$optionsMsg = "<b class='b'>Please enter a valid rush order cost</b>";
	}
	elseif (updateRushOrder($db,$rushOrderCost)) {
		$optionsMsg = "<b>Rush Order cost successfully updated.</b>";
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
    	<td><input type='submit' name='updatePosterTube' value='Update Price' onClick='return confirmUpdate()'/></td>
	</tr>
</table>

<br />
<br />

<table class='table_2'>
	<tr><th colspan='3'>Rush Order</th></tr>
	<tr><td>Price</td>
    <td>$<input type='text' name='rushOrderCost' value='<?php echo $rushOrderCost; ?>' maxlength='6' size='6'></td>
    <td><input type='submit' name='updateRushOrder' value='Update Price' onClick='return confirmUpdate()'/></td>
</table>
</form>


<?php 
if (isset($optionsMsg)) { echo $optionsMsg; }

include_once 'includes/footer.inc.php'; ?>
