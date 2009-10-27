<?php
include 'includes/session.inc.php';
include '../includes/mail.inc.php';
include '../includes/settings.inc.php';

if (isset($_POST['editOrder'])) {


	$cfop1 = $_POST['cfop1'];
	$cfop2 = $_POST['cfop2'];
	$cfop3 = $_POST['cfop3'];
	$cfop4 = $_POST['cfop4'];
	$activityCode = $_POST['activityCode'];
	$finishOptionsId = $_POST['finishOption'];
	$paperTypesId = $_POST['paperType'];
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
		//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
		$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
		mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	
		//Gets Finish Options Information
		$finishOptionsSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_id=" . $finishOptionsId;
		$finishOptionsResult = mysql_query($finishOptionsSql,$db)
			or die("Problem with database. " . mysql_error());
		$finishOptionCost = mysql_result($finishOptionsResult,0,'finishOptions_cost');
	
		//Gets Paper Type Information
		$paperTypesSql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_id=" . $paperTypesId;
		$paperTypesResult = mysql_query($paperTypesSql,$db)
			or die("Problem with database. " . mysql_error());
		$paperTypeCost = mysql_result($paperTypesResult,0,'paperTypes_cost');
		$paperTypeWidth = mysql_result($paperTypesResult,0,'paperTypes_width');
		$widthSwitched;
	
		//Gets Power Tube Information
		$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_id=$posterTubeId"; 
		$posterTubeResult = mysql_query($posterTubeSql,$db)
			or die("Problem with poster tube. " . mysql_error());
		$posterTubeCost =  mysql_result($posterTubeResult,0,"posterTube_cost");
		
		
		//Gets Rush Order Information
		$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_id=$rushOrderId";
		$rushOrderResult = mysql_query($rushOrderSql,$db)
			or die("Problem with rush order. " . mysql_error());
		$rushOrderCost = mysql_result($rushOrderResult,0,"rushOrder_cost");
		
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
		
		$editOrderSql = "UPDATE tbl_orders SET orders_cfop='$cfop', orders_activityCode='" . $activityCode . "',orders_width=$posterWidth, orders_length=$posterLength, orders_finishOptionsId=$finishOptionsId, orders_paperTypesId=$paperTypesId,";
		$editOrderSql .= "orders_posterTubeId=$posterTubeId,orders_rushOrderId=$rushOrderId,orders_widthSwitched=$widthSwitched,orders_totalCost=$totalCost ";
		$editOrderSql .= "WHERE orders_id=$orderId";
	
		//runs query and gets the order_id
		mysql_query($editOrderSql,$db) or die("Error Updating Order: " . mysql_error());
	
		header("Location: orders.php?orderId=$orderId");
	}

	
}


if (isset($_GET['orderId']) && is_numeric($_GET['orderId'])) {

	include 'includes/header.inc.php';
	//gets order id
	$orderId = $_GET['orderId'];
	
	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

	//sql string to get order information
	$orderSql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.*,tbl_posterTube.*,tbl_rushOrder.* FROM tbl_orders 
			LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
			LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id 
			LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE orders_id=" . $orderId;
	

	//runs query	
	$orderResult = mysql_query($orderSql,$db)
		or die("Problem with database. " . mysql_error());

	//sets order information to variables
	$orderEmail = mysql_result($orderResult,0,"orders_email");
	$orderName = mysql_result($orderResult,0,"orders_name");
	$orderFileName = mysql_result($orderResult,0,"orders_fileName");
	$orderCFOP = mysql_result($orderResult,0,"orders_cfop");
	$orderActivityCode = mysql_result($orderResult,0,"orders_activityCode");
	$orderTimeCreated = mysql_result($orderResult,0,"orders_timeCreated");
	$orderTotalCost = mysql_result($orderResult,0,"orders_totalCost");
	$orderWidth = mysql_result($orderResult,0,"orders_width");
	$orderLength =  mysql_result($orderResult,0,"orders_length");
	$orderPaperTypeId = mysql_result($orderResult,0,"paperTypes_id");
	$orderFinishOptionId = mysql_result($orderResult,0,"finishOptions_id");
	$orderPosterTubeName = mysql_result($orderResult,0,"posterTube_name");
	$orderPosterTubeId = mysql_result($orderResult,0,"posterTube_id");
	$orderRushOrderName = mysql_result($orderResult,0,"rushOrder_name");
	$orderRushOrderId = mysql_result($orderResult,0,"rushOrder_id");
	$orderComments = mysql_result($orderResult,0,"orders_comments");
	$orderStatusId = mysql_result($orderResult,0,"orders_statusId");
	
	$cfop1 = substr($orderCFOP,0,1);
	$cfop2 = substr($orderCFOP,2,6);
	$cfop3 = substr($orderCFOP,9,6);
	$cfop4 = substr($orderCFOP,16,6);
	
	
	////////////////Paper Types////////////
	$paperTypesSql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_available=1 AND (paperTypes_width>=$orderWidth OR paperTypes_width>=$orderLength) ORDER BY paperTypes_name ASC";
	$paperTypesResult = mysql_query($paperTypesSql,$db);
	$paperTypesHTML = "<select name='paperType'>";
	for ($i=0;$i < mysql_num_rows($paperTypesResult);$i++) {
		$paperTypeId = mysql_result($paperTypesResult,$i,"paperTypes_id");
		$paperTypeName = mysql_result($paperTypesResult,$i,"paperTypes_name");
		if ($orderPaperTypeId == $paperTypeId) {
		$paperTypesHTML .= "<option selected='true' value='" . $paperTypeId . "'>" . $paperTypeName . "</option>";
		
		}
		else {
			$paperTypesHTML .= "<option value='" . $paperTypeId . "'>" . $paperTypeName . "</option>";
		}
	}
	$paperTypesHTML .= "</select>";
	
	///////////////////Finish Options//////////////
	$finishOptionsSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_available=1 AND finishOptions_maxLength>=$orderLength AND" . 
						"(finishOptions_maxWidth>=$orderWidth OR finishOptions_maxWidth>=$orderLength) ORDER BY finishOptions_name ASC";
	$finishOptionsResult = mysql_query($finishOptionsSql,$db);
	$finishOptionsHTML = "<select name='finishOption'>";
	for ($i=0; $i < mysql_num_rows($finishOptionsResult); $i++) {
		$finishOptionName = mysql_result($finishOptionsResult,$i,"finishOptions_name");
		$finishOptionId = mysql_result($finishOptionsResult,$i,"finishOptions_id");
		
		if ($orderFinishOptionId == $finishOptionId) {
			$finishOptionsHTML .= "<option selected='true' value='" . $finishOptionId . "'>" . $finishOptionName . "</option>";
		
		}
		else {
			$finishOptionsHTML .= "<option value='" . $finishOptionId . "'>" . $finishOptionName . "</option>";
		}
	}
	$finishOptionsHTML .= "</select>";
	
	////////////////////Poster Tube////////////////////
	$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1";
	$posterTubeResult = mysql_query($posterTubeSql,$db);
	$posterTubeHTML = "<select name='posterTube'>";
	for($i=0;$i<mysql_num_rows($posterTubeResult);$i++) {
		$posterTubeId = mysql_result($posterTubeResult,$i,'posterTube_id');
		$posterTubeName = mysql_result($posterTubeResult,$i,'posterTube_name') ;
		
		if ($posterTubeName == $orderPosterTubeName) {
			$posterTubeHTML .= "<option selected='true' value='" . $posterTubeId . "'>" . $posterTubeName . "</option>";
		}
		else {
			$posterTubeHTML .= "<option value='" . $posterTubeId . "'>" . $posterTubeName . "</option>";
		}
		
	
	}
	$posterTubeHTML .= "</select>";
	
	
	/////////////////Rush Order//////////////
	$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1";
	$rushOrderResult = mysql_query($rushOrderSql,$db);
	$rushOrderHTML = "<select name='rushOrder'>";
	for($i=0;$i<mysql_num_rows($rushOrderResult);$i++) {
		$rushOrderId = mysql_result($rushOrderResult,$i,'rushOrder_id');
		$rushOrderName = mysql_result($rushOrderResult,$i,'rushOrder_name');
		
		if ($rushOrderName == $orderRushOrderName) {
			$rushOrderHTML .= "<option selected value='" . $rushOrderId . "'>" . $rushOrderName . "</option>";
		}
		else {
			$rushOrderHTML .= "<option value='" . $rushOrderId . "'>" . $rushOrderName . "</option>";
		}
		
	
	}
	$rushOrderHTML .= "</select>";
	
				
}
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
<form method='post' action='editOrder.php?orderId=<?php echo $orderId; ?>'><table class='table_1'>
				<tr><th colspan='2'>Edit Order Information</th></tr>
				<tr><td class='td_2'>Order Number:</td><td><?php echo $orderId; ?></td></tr>
				<tr><td class='td_2'>Email: </td><td><?php echo $orderEmail; ?></td></tr>
				<tr><td class='td_2'>Full Name: </td><td><?php echo $orderName; ?></td></tr>
				<tr><td class='td_2'>File:</td><td><a href='download.php?orderId=<?php echo $orderId; ?>'><?php echo $orderFileName;  ?></a></td></tr>
				<tr><td class='td_2'>CFOP:</td>
					<td>
						<input type='text' name='cfop1' id='cfop1' maxlength='1' class='input_3' onKeyUp='cfopAdvance1()' value='<?php echo $cfop1; ?>'> - 
						<input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='input_4' onKeyUp='cfopAdvance2()' value='<?php echo $cfop2; ?>'> - 
						<input type='text' name='cfop3' id='cfop3' maxlength='6' class='input_4' onKeyUp='cfopAdvance3()' value='<?php echo $cfop3; ?>'> - 
						<input type='text' name='cfop4' id='cfop4' maxlength='6' class='input_4' value='<?php echo $cfop4; ?>'>
					</td>
				</tr>
				<tr><td class='td_2'>Activity Code:</td><td><input type='text' name='activityCode' maxlength='6' size='6' value='<?php echo $orderActivityCode; ?>'></td></tr>
				<tr><td class='td_2'>Time Created:</td><td><?php echo $orderTimeCreated; ?></td></tr>
				<tr><td class='td_2'>Total Cost:</td><td><?php echo $orderTotalCost; ?></td></tr>
				<tr><td class='td_2'>Width:</td><td><?php echo $orderWidth; ?>"</td></tr>
				<tr><td class='td_2'>Length:</td><td><?php echo $orderLength; ?>"</td></tr>
				<tr><td class='td_2'>Paper Type:</td><td><?php echo $paperTypesHTML; ?></td></tr>
				<tr><td class='td_2'>Finish Option:</td><td><?php echo $finishOptionsHTML;  ?></td></tr>
				<tr><td class='td_2'>Poster Tube:</td><td><?php echo $posterTubeHTML; ?></td></tr>
				<tr><td class='td_2'>Rush Order:</td><td><?php echo $rushOrderHTML; ?></td></tr>
				<tr><td class='td_2' valign='top'>Comments:</td><td><?php echo $orderComments; ?></td></tr>
			</table>
			<br>
			<input type='hidden' name='orderId' value='<?php echo $orderId; ?>'>
			<input type='hidden' name='posterWidth' value='<?php echo $orderWidth; ?>'>
			<input type='hidden' name='posterLength' value='<?php echo $orderLength; ?>'>
			<input type='submit' name='editOrder' value='Edit Order' onClick='return confirmUpdate()'>
			</form>

<?php if (isset($cfopMsg)){echo $cfopMsg; } ?>
<?php if (isset($activityCodeMsg)){echo $activityCodeMsg; } ?>
<?php include 'includes/footer.inc.php'; ?>