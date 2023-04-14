<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$message = "";
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
	if (!\IGBIllinois\cfop::verify_format($cfop,$activityCode)) {
                $errors = true;
                $message = functions::alert("Please enter a valid CFOP",0);
        }

        try {
                $cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
                $cfop_obj->validate_cfop($cfop,$activityCode);

        }
        catch (\Exception $e) {
                $error = true;
                $message = functions::alert($e->getMessage(),0);
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
		$order = new order($db,$order_id);
		$order->edit($cfop, $activityCode, $finishOptionId, $paperTypeId, $posterTubeId, $rushOrderId, $totalCost);
		header("Location: order.php?order_id=" . $order_id);
	}

	
}


if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {

	
	//gets order id
	$order_id = $_GET['order_id'];
	
	$order = new order($db,$order_id);
		
	////////////////Paper Types////////////
	$paperTypes = functions::getValidPaperTypes($db,$order->get_width(),$order->get_length());
	$paperTypesHTML = "<div class='col-md-6'>";
	$paperTypesHTML .= "<select class='form-control' name='paperType'>";
	foreach ($paperTypes as $paperType) {
		if ($order->get_paper_type_id() === $paperType["id"]) {
			$paperTypesHTML .= "<option selected='true' value='" . $paperType["id"] . "'>" . $paperType["name"] . "</option>";
		}
		else { $paperTypesHTML .= "<option value='" . $paperType["id"] . "'>" . $paperType["name"]. "</option>"; }
	}
	$paperTypesHTML .= "</select>";
	$paperTypesHTML .= "</div>";
	
	///////////////////Finish Options//////////////
	$finishOptions = functions::getValidFinishOptions($db,$order->get_width(),$order->get_length());
	$finishOptionsHTML = "<div class='col-md-6'>";
	$finishOptionsHTML .= "<select class='form-control' name='finishOption'>";
	foreach ($finishOptions as $finishOption) {
		if ($order->get_finish_option_id() == $finishOption["id"]) {
			$finishOptionsHTML .= "<option selected='true' value='" . $finishOption["id"] . "'>" . $finishOption["name"] . "</option>";
		
		}
		else {
			$finishOptionsHTML .= "<option value='" . $finishOption["id"] . "'>" . $finishOption["name"] . "</option>";
		}
	}
	$finishOptionsHTML .= "</select>";
	$finishOptionsHTML .= "</div>";
	
	////////////////////Poster Tube////////////////////
	$posterTube = poster_tube::getPosterTubes($db);
	$posterTubeHTML = "<div class='col-md-4'>";
	$posterTubeHTML .= "<select class='form-control' name='posterTube'>";
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
	$posterTubeHTML .= "</div>";
	
	
	/////////////////Rush Order//////////////
	$rushOrder = rush_order::getRushOrders($db);
	$rushOrderHTML = "<div class='col-md-4'>";
	$rushOrderHTML .= "<select class='form-control' name='rushOrder'>";
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
	$rushOrderHTML .= "</div>";
	
				
}

require_once 'includes/header.inc.php';

?>
<script>
function confirmUpdate()
{
var agree=confirm("Are you sure you wish to update?");
if (agree)
	return true ;
else
	return false ;
}
</script>

<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?order_id=<?php echo $order_id; ?>'>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>

<table class='table table-bordered table-sm'>
	<tr><th colspan='2'>Edit Order Information</th></tr>
	<tr><td class='text-right'>Order Number</td><td><?php echo $order->get_order_id(); ?></td></tr>
	<tr><td class='text-right'>Email</td><td><?php echo $order->get_email(); ?></td></tr>
	<tr><td class='text-right'>Additional Emails </td><td><?php echo $order->get_cc_emails() ?></td></tr>
	<tr><td class='text-right'>Full Name</td><td><?php echo $order->get_name(); ?></td></tr>
	<tr><td class='text-right'>File</td><td><a href='download.php?orderId=<?php echo $order->get_order_id(); ?>'><?php echo $order->get_filename();  ?></a></td></tr>
	<tr><td class='text-right' style='vertical-align:middle;'>CFOP</td>
		<td>
		<div class='form-group row'>
		<div class='col-md-2'><input type='text' name='cfop1' id='cfop1' maxlength='1' class='form-control' onKeyUp='cfopAdvance1()' value='<?php echo $order->get_cfop_college(); ?>'></div> 
		<div class='col-md-2'><input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='form-control' onKeyUp='cfopAdvance2()' value='<?php echo $order->get_cfop_fund(); ?>'></div>
		<div class='col-md-2'><input type='text' name='cfop3' id='cfop3' maxlength='6' class='form-control' onKeyUp='cfopAdvance3()' value='<?php echo $order->get_cfop_organization(); ?>'></div>
		<div class='col-md-2'><input type='text' name='cfop4' id='cfop4' maxlength='6' class='form-control' value='<?php echo $order->get_cfop_program(); ?>'></div>
		</div>
		</td>
	</tr>
	<tr><td class='text-right' style='vertical-align:middle;'>Activity Code</td><td><div class='col-md-2'><input class='form-control' type='text' name='activityCode' maxlength='6' value='<?php echo $order->get_activity_code(); ?>'></div></td></tr>
	<tr><td class='text-right'>Time Created</td><td><?php echo $order->get_time_created(); ?></td></tr>
	<tr><td class='text-right'>Total Cost</td><td>$<?php echo $order->get_total_cost(); ?></td></tr>
	<tr><td class='text-right'>Width</td><td><?php echo $order->get_width(); ?>"</td></tr>
	<tr><td class='text-right'>Length</td><td><?php echo $order->get_length(); ?>"</td></tr>
	<tr><td class='text-right' style='vertical-align:middle;'>Paper Type</td><td><?php echo $paperTypesHTML; ?></td></tr>
	<tr><td class='text-right' style='vertical-align:middle;'>Finish Option</td><td><?php echo $finishOptionsHTML;  ?></td></tr>
	<tr><td class='text-right' style='vertical-align:middle;'>Poster Tube</td><td><?php echo $posterTubeHTML; ?></td></tr>
	<tr><td class='text-right' style='vertical-align:middle;'>Rush Order</td><td><?php echo $rushOrderHTML; ?></td></tr>
	<tr><td class='text-right'>Comments</td><td><?php echo $order->get_wordwrap_comments(); ?></td></tr>
</table>
</div>
<br>
<input type='hidden' name='orderId' value='<?php echo $order->get_order_id(); ?>'>
<input type='hidden' name='posterWidth' value='<?php echo $order->get_width(); ?>'>
<input type='hidden' name='posterLength' value='<?php echo $order->get_length(); ?>'>
<div class='col-sm-12 col-md-12'>
<a class='btn btn-warning' href='order.php?order_id=<?php echo $order->get_order_id(); ?>'>Cancel</a>
<button class='btn btn-primary' type='submit' name='editOrder' onClick='return confirmUpdate()'>Edit Order</button>
</div>
</form>
<br>
<?php if (isset($message)){echo $message; } ?>
<?php require_once '../includes/footer.inc.php'; ?>
