<?php
include_once 'includes/main.inc.php';
include_once 'mail.inc.php';
include_once 'orders.inc.php';
include_once 'order.class.inc.php';


if ((isset($_GET['orderId'])) && is_numeric($_GET['orderId'])) {
	$orderId = $_GET['orderId'];

	$order = new order($db,$orderId);
}
else {
	exit;
}
if (isset($_POST['changeStatus'])) {

	$statusId = $_POST['statusId'];
	
	//updates the order to the new status
	$order->set_status($statusId);

	//if status is set to "Complete", then it will email the user saying to come pick up the poster
	if ($statusId == 3) {
					
		mailUserOrderComplete($db,$orderId,admin_email);
		header("Location: index.php");
	}
	//else if status is set to "Cancel"
	elseif ($statusId == 4) { header("Location: index.php"); }
	
}
//get last day of previous month
$startDate = date('Y/m',strtotime('-1 month')) . "/01";
$previous_date = strtotime('-1 second', strtotime($startDate));

if (strtotime($order->get_time_created()) < $previous_date) {
	$status_html = $order->get_status();
	$edit_order_html = "";
}
else {
	$orderStatusId = $order->get_status_id();
	//gets the different possible status options
	$statusResult = getAllStatus($db);

	$status_html = "<form action='orders.php?orderId=" . $orderId . "' method='post'>";
	$status_html .= "<select name='statusId'>";

	for ($i=0; $i<count($statusResult); $i++) {
		$statusId = $statusResult[$i]["status_id"];
		$statusName = $statusResult[$i]["status_name"];
		//used to have the current status of the order be the one selected in the drop down box
		if ($statusId == $orderStatusId) {
			$status_html .= "<option value='" . $statusId . "' selected>" . $statusName . "</option>";
		}
		else { $status_html .= "<option value='" . $statusId . "'>" . $statusName . "</option>"; }
	}
	$status_html .= "</select>";
	$status_html .= "<input type='submit' value='Change' name='changeStatus'>";
	$status_html .= "</form>";
	
	$edit_order_html = "<form method='get' action='editOrder.php'>";
	$edit_order_html .= "<input type='hidden' name='orderId' value='" . $order->get_order_id() . "'>";
	$edit_order_html .= "<input type='submit' value='Edit Order'>";
	$edit_order_html .= "</form>";
}

include_once 'includes/header.inc.php';

?>
<table class='table_1'>
<tr><th colspan='2'>Order Information</th></tr>
<tr><td class='td_2'>Order Number:</td><td><?php echo $orderId; ?></td></tr>
<tr><td class='td_2'>Email: </td><td><?php echo $order->get_email() ?></td></tr>
<tr><td class='td_2'>Full Name: </td><td><?php echo $order->get_name() ?></td></tr>
<tr><td class='td_2'>File:</td><td><a href='download.php?orderId=<?php echo $order->get_order_id() ?>'><?php echo $order->get_filename() ?></a></td></tr>
<tr><td class='td_2'>CFOP:</td><td><?php echo $order->get_cfop(); ?></td></tr>
<tr><td class='td_2'>Activity Code:</td><td><?php echo $order->get_activity_code(); ?></td></tr>
<tr><td class='td_2'>Time Created:</td><td><?php echo $order->get_time_created(); ?></td></tr>
<tr><td class='td_2'>Total Cost:</td><td><?php echo $order->get_total_cost(); ?></td></tr>
<tr><td class='td_2'>Width:</td><td><?php echo $order->get_width(); ?>''</td></tr>
<tr><td class='td_2'>Length:</td><td><?php echo $order->get_length(); ?>''</td></tr>
<tr><td class='td_2'>Paper Type:</td><td><?php echo$order->get_paper_type_name(); ?></td></tr>
<tr><td class='td_2'>Finish Option:</td><td><?php echo $order->get_finish_option_name(); ?></td></tr>
<tr><td class='td_2'>Poster Tube:</td><td><?php echo $order->get_poster_tube_name(); ?></td></tr>
<tr><td class='td_2'>Rush Order:</td><td><?php echo $order->get_rush_order_name(); ?></td></tr>
<tr><td class='td_2' valign='top'>Comments:</td><td><?php echo $order->get_comments(); ?></td></tr>
<tr><td class='td_2'>Status:</td><td><?php echo $status_html; ?></td></tr>
</table>
<br>		
<?php echo $edit_order_html; ?>


<?php include_once 'includes/footer.inc.php'; ?>
