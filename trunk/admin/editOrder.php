<?php
include_once 'includes/main.inc.php';
include_once 'mail.inc.php';
include_once 'order.class.inc.php';
include_once 'orders.inc.php';
include_once 'paperTypes.inc.php';
include_once 'finishOptions.inc.php';
include_once 'posterTube.inc.php';
include_once 'rushOrder.inc.php';

if (isset($_POST['editOrder'])) {

	$cfop1 = $_POST['cfop1'];
	$cfop2 = $_POST['cfop2'];
	$cfop3 = $_POST['cfop3'];
	$cfop4 = $_POST['cfop4'];
	$activityCode = $_POST['activityCode'];
	$finishOptionId = $_POST['finishOption'];
	$paperTypeId = $_POST['paperType'];
	$posterTubeId = $_POST['posterTube'];
	$rushOrderId = $_POST['rushOrder'];
	$orderId = $_POST['orderId'];
	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$cfop = $cfop1 . "-" . $cfop2 . "-" . $cfop3 . "-" . $cfop4;
	//trims activity code and upper cases the letter
	$activityCode = strtoupper(trim(rtrim($activityCode)));
	
	$error = false;
	if (!eregi('^1-[0-9]{6}-[0-9]{6}-[0-9]{6}$',$cfop)) {
		$error = true;
		$cfopMsg = "<br><b class='error'>Invalid CFOP Number</b>";
	}
	elseif (!eregi('^[a-zA-Z0-9]{6}',$activityCode) && (strlen($activityCode) > 0)) {
		$error = true;
		$activityCodeMsg = 	"<b><b class='error'>Invalid Activity Code</b>";
	}
	
	if ($error == false) {
		
	
		//Gets Finish Options Information
		$finishOption = getFinishOption($db,$finishOptionId);
		$finishOptionCost = $finishOption[0]['finishOptions_cost'];
	
		//Gets Paper Type Information
		$paperType = getPaperType($db,$paperTypeId);
		$paperTypeCost = $paperType[0]['paperTypes_cost'];
		$paperTypeWidth = $paperType[0]['paperTypes_width'];
		$widthSwitched;
	
		//Gets Power Tube Information
		$posterTubeResult = getPosterTube($db,$posterTubeId);
		$posterTubeCost =  $posterTubeResult[0]["posterTube_cost"];
		
		//Gets Rush Order Information
		$rushOrderResult = getRushOrder($db,$rushOrderId);
		$rushOrderCost = $rushOrderResult[0]["rushOrder_cost"];
		
		//Switches around the poster width and length to make the length the shortest possible to save money.
		if (($posterWidth <= $paperTypeWidth) && ($posterLength <= $paperTypeWidth) && ($posterWidth < $posterLength)) {
			$tempPosterWidth = $posterWidth;
			$posterWidth = $posterLength;
			$posterLength = $tempPosterWidth;
			$widthSwitched = 1;
		}
		elseif (($posterWidth > $paperTypeWidth) && ($posterLength <= $paperTypeWidth)) {
			$tempPosterWidth = $posterWidth;
			$posterWidth = $posterLength;
			$posterLength = $tempPosterWidth;
			$widthSwitched = 1;
		}
		else {
			$widthSwitched = 0;
		}
	
		//Calculates Total Cost
		$totalCost = ($posterLength * $paperTypeCost) + $finishOptionCost + $posterTubeCost + $rushOrderCost;
		$editOrderSql = "UPDATE tbl_orders SET orders_cfop='" . $cfop . "', ";
		$editOrderSql .= "orders_activityCode='" . $activityCode . "', ";
		$editOrderSql .= "orders_width='" . $posterWidth . "', ";
		$editOrderSql .= "orders_length='" . $posterLength . "', ";
		$editOrderSql .= "orders_finishOptionsId='" . $finishOptionId . "', ";
		$editOrderSql .= "orders_paperTypesId='" . $paperTypeId . "', ";
		$editOrderSql .= "orders_posterTubeId='" . $posterTubeId . "', ";
		$editOrderSql .= "orders_rushOrderId='" . $rushOrderId . "', ";
		$editOrderSql .= "orders_widthSwitched='" . $widthSwitched . "', ";
		$editOrderSql .= "orders_totalCost='" . $totalCost . "' ";
		$editOrderSql .= "WHERE orders_id='" . $orderId . "' LIMIT 1 ";
		//runs query and gets the order_id
		$db->non_select_query($editOrderSql);
	
		header("Location: orders.php?orderId=" . $orderId);
	}

	
}


if (isset($_GET['orderId']) && is_numeric($_GET['orderId'])) {

	
	//gets order id
	$orderId = $_GET['orderId'];
	
	$order = new order($db,$orderId);
		
	////////////////Paper Types////////////
	$paperTypes = getValidPaperTypes($db,$order->get_width(),$order->get_length());
	$paperTypesHTML = "<select name='paperType'>";
	for ($i=0;$i < count($paperTypes);$i++) {
		$paperTypeId = $paperTypes[$i]["paperTypes_id"];
		$paperTypeName = $paperTypes[$i]["paperTypes_name"];
		if ($order->get_paper_type_id() == $paperTypeId) {
			$paperTypesHTML .= "<option selected='true' value='" . $paperTypeId . "'>" . $paperTypeName . "</option>";
		}
		else { $paperTypesHTML .= "<option value='" . $paperTypeId . "'>" . $paperTypeName . "</option>"; }
	}
	$paperTypesHTML .= "</select>";
	
	///////////////////Finish Options//////////////
	$finishOptions = getValidFinishOptions($db,$order->get_width(),$order->get_length());
	$finishOptionsHTML = "<select name='finishOption'>";
	for ($i=0; $i < count($finishOptions); $i++) {
		$finishOptionName = $finishOptions[$i]["finishOptions_name"];
		$finishOptionId = $finishOptions[$i]["finishOptions_id"];
		
		if ($order->get_finish_option_id() == $finishOptionId) {
			$finishOptionsHTML .= "<option selected='true' value='" . $finishOptionId . "'>" . $finishOptionName . "</option>";
		
		}
		else {
			$finishOptionsHTML .= "<option value='" . $finishOptionId . "'>" . $finishOptionName . "</option>";
		}
	}
	$finishOptionsHTML .= "</select>";
	
	////////////////////Poster Tube////////////////////
	$posterTube = getPosterTubes($db);	
	$posterTubeHTML = "<select name='posterTube'>";
	for($i=0;$i<count($posterTube);$i++) {
		$posterTubeId = $posterTube[$i]['posterTube_id'];
		$posterTubeName = $posterTube[$i]['posterTube_name'];
		
		if ($posterTubeId == $order->get_poster_tube_id()) {
			$posterTubeHTML .= "<option selected='true' value='" . $posterTubeId . "'>" . $posterTubeName . "</option>";
		}
		else {
			$posterTubeHTML .= "<option value='" . $posterTubeId . "'>" . $posterTubeName . "</option>";
		}
		
	
	}
	$posterTubeHTML .= "</select>";
	
	
	/////////////////Rush Order//////////////
	$rushOrder = getRushOrders($db);
	$rushOrderHTML = "<select name='rushOrder'>";
	for($i=0;$i<count($rushOrder);$i++) {
		$rushOrderId = $rushOrder[$i]['rushOrder_id'];
		$rushOrderName = $rushOrder[$i]['rushOrder_name'];
		
		if ($rushOrderId == $order->get_rush_order_id()) {
			$rushOrderHTML .= "<option selected value='" . $rushOrderId . "'>" . $rushOrderName . "</option>";
		}
		else {
			$rushOrderHTML .= "<option value='" . $rushOrderId . "'>" . $rushOrderName . "</option>";
		}
		
	
	}
	$rushOrderHTML .= "</select>";
	
				
}

include_once 'includes/header.inc.php';

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
<form method='post' action='editOrder.php?orderId=<?php echo $orderId; ?>'>
<table class='table_1'>
	<tr><th colspan='2'>Edit Order Information</th></tr>
	<tr><td class='td_2'>Order Number:</td><td><?php echo $order->get_order_id(); ?></td></tr>
	<tr><td class='td_2'>Email: </td><td><?php echo $order->get_email(); ?></td></tr>
	<tr><td class='td_2'>Full Name: </td><td><?php echo $order->get_name(); ?></td></tr>
	<tr><td class='td_2'>File:</td><td><a href='download.php?orderId=<?php echo $order->get_order_id(); ?>'><?php echo $order->get_filename();  ?></a></td></tr>
	<tr><td class='td_2'>CFOP:</td>
		<td>
		<input type='text' name='cfop1' id='cfop1' maxlength='1' class='input_3' onKeyUp='cfopAdvance1()' value='<?php echo $order->get_cfop_college(); ?>'> - 
		<input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='input_4' onKeyUp='cfopAdvance2()' value='<?php echo $order->get_cfop_fund(); ?>'> - 
		<input type='text' name='cfop3' id='cfop3' maxlength='6' class='input_4' onKeyUp='cfopAdvance3()' value='<?php echo $order->get_cfop_organization(); ?>'> - 
		<input type='text' name='cfop4' id='cfop4' maxlength='6' class='input_4' value='<?php echo $order->get_cfop_program(); ?>'>
		</td>
	</tr>
	<tr><td class='td_2'>Activity Code:</td><td><input type='text' name='activityCode' maxlength='6' size='6' value='<?php echo $order->get_activity_code(); ?>'></td></tr>
	<tr><td class='td_2'>Time Created:</td><td><?php echo $order->get_time_created(); ?></td></tr>
	<tr><td class='td_2'>Total Cost:</td><td><?php echo $order->get_total_cost(); ?></td></tr>
	<tr><td class='td_2'>Width:</td><td><?php echo $order->get_width(); ?>"</td></tr>
	<tr><td class='td_2'>Length:</td><td><?php echo $order->get_length(); ?>"</td></tr>
	<tr><td class='td_2'>Paper Type:</td><td><?php echo $paperTypesHTML; ?></td></tr>
	<tr><td class='td_2'>Finish Option:</td><td><?php echo $finishOptionsHTML;  ?></td></tr>
	<tr><td class='td_2'>Poster Tube:</td><td><?php echo $posterTubeHTML; ?></td></tr>
	<tr><td class='td_2'>Rush Order:</td><td><?php echo $rushOrderHTML; ?></td></tr>
	<tr><td class='td_2' valign='top'>Comments:</td><td><?php echo $order->get_comments(); ?></td></tr>
</table>
<br>
<input type='hidden' name='orderId' value='<?php echo $order->get_order_id(); ?>'>
<input type='hidden' name='posterWidth' value='<?php echo $order->get_width(); ?>'>
<input type='hidden' name='posterLength' value='<?php echo $order->get_length(); ?>'>
<input type='submit' name='editOrder' value='Edit Order' onClick='return confirmUpdate()'>
</form>

<?php if (isset($cfopMsg)){echo $cfopMsg; } ?>
<?php if (isset($activityCodeMsg)){echo $activityCodeMsg; } ?>
<?php include 'includes/footer.inc.php'; ?>
