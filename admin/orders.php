<?php
include_once 'includes/main.inc.php';
include_once 'mail.inc.php';


if (isset($_POST['changeStatus'])) {

	$orderId = $_POST['orderId'];
	$statusId = $_POST['statusId'];
	$finishOptionName = $_POST['finishOptionName'];
	$paperTypeName = $_POST['paperTypeName'];
	
	$timeFinished = date( 'Y-m-d H:i:s');

	//updates the order to the new status
	$updateStatusSql = "UPDATE tbl_orders SET orders_statusId='" . $statusId . "',orders_timeFinished='" . $timeFinished . "' WHERE orders_id= " . $orderId;
	$db->non_select_query($updateStatusSql);

	//if status is set to "Complete", then it will email the user saying to come pick up the poster
	if ($statusId == 3) {
	
		$orderSql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.*,tbl_posterTube.*,tbl_rushOrder.* FROM tbl_orders 
			LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
			LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id 
			LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE orders_id=" . $orderId;
		
		$orderResult = $db->query($orderSql);

		
		//sets an array with order information.
		$orderInfo = array(
					'email' => $orderResult[0]['orders_email'],
					'name' => $orderResult[0]['orders_name'],
					'orderID' => $orderId,
					'fileName' =>  $orderResult[0]['orders_fileName'],
					'totalCost' => $orderResult[0]['orders_totalCost'],
					'posterLength' =>  $orderResult[0]['orders_length'],
					'posterWidth' =>  $orderResult[0]['orders_width'],
					'cfop' =>  $orderResult[0]['orders_cfop'],
					'activityCode' =>  $orderResult[0]['orders_activityCode'],
					'paperType' =>  $orderResult[0]['paperTypes_name'],
					'finishOption' =>  $orderResult[0]['finishOptions_name'],
					'posterTube' => $orderResult[0]['posterTube_name'],
					'rushOrder' => $orderResult[0]['rushOrder_name'],
					'comments' => $orderResult[0]['orders_comments'],
					'adminEmail' => admin_email
		);
				
		mailUserOrderComplete($orderInfo);
		header("Location: index.php");
	}
	//else if status is set to "Cancel"
	elseif ($statusId == 4) {

		header("Location: index.php");
	}
	
		
	



}

if (isset($_GET['orderId'])) {

	include 'includes/header.inc.php';
	//gets order id
	$orderId = $_GET['orderId'];
	
	//sql string to get order information
	$orderSql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.*,tbl_posterTube.*,tbl_rushOrder.* FROM tbl_orders 
			LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
			LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id 
			LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE orders_id=" . $orderId;
	

	//runs query	
	$orderResult = $db->query($orderSql);

	//sets order information to variables
	$orderEmail = $orderResult[0]["orders_email"];
	$orderName = $orderResult[0]["orders_name"];
	$orderFileName = $orderResult[0]["orders_fileName"];
	$orderCFOP = $orderResult[0]["orders_cfop"];
	$orderActivityCode = $orderResult[0]["orders_activityCode"];
	$orderTimeCreated = $orderResult[0]["orders_timeCreated"];
	$orderTotalCost = $orderResult[0]["orders_totalCost"];
	$orderWidth = $orderResult[0]["orders_width"];
	$orderLength =  $orderResult[0]["orders_length"];
	$orderPaperType = $orderResult[0]["paperTypes_name"];
	$orderFinishOption = $orderResult[0]["finishOptions_name"];
	$posterTube = $orderResult[0]["posterTube_name"];
	$rushOrder = $orderResult[0]["rushOrder_name"];
	$orderComments = $orderResult[0]["orders_comments"];
	$orderStatusId = $orderResult[0]["orders_statusId"];
	
	//gets the different possible status options
	$statusSql = "SELECT * FROM tbl_status";
	$statusResult = $db->query($statusSql);

	
	$statusHTML = "<form action='orders.php?orderId=" . $orderId . "' method='post'>
					<select name='statusId'>";

	for ($i=0; $i<count($statusResult); $i++) {
		$statusId = $statusResult[$i]["status_id"];
		$statusName = $statusResult[$i]["status_name"];
		//used to have the current status of the order be the one selected in the drop down box
		if ($statusId == $orderStatusId) {
			$statusHTML .= "<option value='" . $statusId . "' selected>" . $statusName . "</option>";
		}
		else {
			$statusHTML .= "<option value='" . $statusId . "'>" . $statusName . "</option>";
		}
	}
	$statusHTML .= "</select>
					<input type='hidden' name='orderId' value='" . $orderId . "'>
					<input type='submit' value='Change' name='changeStatus'>
					</form>";


	$ordersHTML = "<table class='table_1'>
				<tr><th colspan='2'>Order Information</th></tr>
				<tr><td class='td_2'>Order Number:</td><td>" . $orderId . "</td></tr>
				<tr><td class='td_2'>Email: </td><td>" . $orderEmail . "</td></tr>
				<tr><td class='td_2'>Full Name: </td><td>" . $orderName . "</td></tr>
				<tr><td class='td_2'>File:</td><td><a href='download.php?orderId=" . $orderId . "'>" . $orderFileName . "</a></td></tr>
				<tr><td class='td_2'>CFOP:</td><td>" . $orderCFOP . "</td></tr>
				<tr><td class='td_2'>Activity Code:</td><td>" . $orderActivityCode . "</td></tr>
				<tr><td class='td_2'>Time Created:</td><td>" . $orderTimeCreated . "</td></tr>
				<tr><td class='td_2'>Total Cost:</td><td>$" . $orderTotalCost . "</td></tr>
				<tr><td class='td_2'>Width:</td><td>" . $orderWidth . "\"</td></tr>
				<tr><td class='td_2'>Length:</td><td>" . $orderLength . "\"</td></tr>
				<tr><td class='td_2'>Paper Type:</td><td>" . $orderPaperType . "</td></tr>
				<tr><td class='td_2'>Finish Option:</td><td>" . $orderFinishOption . "</td></tr>
				<tr><td class='td_2'>Poster Tube:</td><td>" . $posterTube . "</td></tr>
				<tr><td class='td_2'>Rush Order:</td><td>" . $rushOrder . "</td></tr>
				<tr><td class='td_2' valign='top'>Comments:</td><td>" . $orderComments . "</td></tr>
				<tr><td class='td_2'>Status:</td><td>" . $statusHTML . "</td></tr>
			</table>
			<br>";

	
		$ordersHTML .= "<form method='get' action='editOrder.php'>
			<input type='hidden' name='orderId' value='" . $orderId . "'>
			<input type='submit' value='Edit Order'>
			</form>";
	

				
}

echo $ordersHTML;
?>

<?php include 'includes/footer.inc.php'; ?>
