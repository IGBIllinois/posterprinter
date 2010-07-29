<?php
/////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	index.php
//
//	Views the current orders that need to be processed and
//	changes the status from New,In Progress, and Completed
//
//	David Slater
//	April 2007
//
///////////////////////////////////////////////
//Include files for the script to run

include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'orders.inc.php';





//runs query and gets the order_id
$orders = getCurrentOrders($db);

$orders_html = "";
if (count($orders) == 0) {

	$orders_html = "<tr><td>None</td><td></td></tr>";

}
else {
	for ($i=0; $i<count($orders); $i++) {
		
		$orderId = $orders[$i]["orders_id"];
		$orderEmail = $orders[$i]["orders_email"];
		$orderFileName = $orders[$i]["orders_fileName"];
		$orderStatus = $orders[$i]["status_name"];
		$orderCost = $orders[$i]["orders_totalCost"];
		$rushOrderName = $orders[$i]["rushOrder_name"];
		if ($rushOrderName == "Yes") {
			$orders_html .= "<tr class='rush'>";
		}
		elseif ($rushOrderName == "No") {
			$orders_html .= "<tr>";
		}
		$orders_html .= "<td><a href='orders.php?orderId=" . $orderId . "'>" . $orderId . "</a></td>";
		$orders_html .= "<td>" . $orderEmail . "</td>";
		$orders_html .= "<td>" . $orderCost . "</td>";
		$orders_html .= "<td><a href='download.php?orderId=" . $orderId . "'>" . $orderFileName . "</a></td>";
		$orders_html .= "<td>" . $orderStatus  . "</td>";
		$orders_html .= "</tr>";
	
	}
}
?>


<table class='wide'>
	<tr>
		<td class='header_center'>Order Number</td>
		<td class='header_center'>Email</td>
		<td class='header_center'>Total Cost</td>
		<td class='header_center'>File Name</td>
		<td class='header_center'>Status</td>
	</tr>
	
<?php echo $orders_html; ?>



</table>

<?php include_once 'includes/footer.inc.php'; ?>
