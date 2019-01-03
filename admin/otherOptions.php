<?php

require_once 'includes/main.inc.php';
require_once 'posterTube.inc.php';
require_once 'rushOrder.inc.php';

if (isset($_POST['updatePosterTube'])) {
	$posterTubeCost = $_POST['posterTubeCost'];
	$result = updatePosterTube($db,$posterTubeCost);
	
	
}
elseif (isset($_POST['updateRushOrder'])) {
	$rushOrderCost = $_POST['rushOrderCost'];
	$result = updateRushOrder($db,$rushOrderCost);
		
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
<table>
	<tr><td colspan='3' class='header'>Poster Tube</td></tr>
	<tr><td>Price</td>
    	<td>$<input type='text' name='posterTubeCost' value='<?php echo $posterTubeCost; ?>' maxlength='6' size='6'></td>
    	<td><input type='submit' name='updatePosterTube' value='Update Price' onClick='return confirmUpdate()'/></td>
	</tr>
</table>

<br />

<table>
	<tr><td colspan='3' class='header'>Rush Order</td></tr>
	<tr><td>Price</td>
    <td>$<input type='text' name='rushOrderCost' value='<?php echo $rushOrderCost; ?>' maxlength='6' size='6'></td>
    <td><input type='submit' name='updateRushOrder' value='Update Price' onClick='return confirmUpdate()'/></td>
</table>
</form>


<?php 
if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }

require_once 'includes/footer.inc.php'; ?>
