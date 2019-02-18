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

require_once 'includes/main.inc.php';
require_once 'orders.inc.php';


$orders = getCurrentOrders($db);

$orders_html = "";
if (count($orders) == 0) {

	$orders_html = "<tr><td colspan='5'>None</td></tr>";

}
else {
	foreach ($orders as $order) {
		
		$rushOrderName = $order["rushOrder_name"];
		if ($rushOrderName == "Yes") {
			$orders_html .= "<tr class='rush'>";
		}
		elseif ($rushOrderName == "No") {
			$orders_html .= "<tr>";
		}
		$orders_html .= "<td><a href='orders.php?orderId=" . $order["orders_id"] . "'>" . $order["orders_id"] . "</a></td>";
		$orders_html .= "<td>" . $order["orders_email"] . "</td>";
		$orders_html .= "<td>" . $order["orders_totalCost"] . "</td>";
		$orders_html .= "<td><a href='download.php?orderId=" . $order["orders_id"] . "'>" . $order["orders_fileName"] . "</a></td>";
		$orders_html .= "<td>" . $order["status_name"]  . "</td>";
		$orders_html .= "</tr>";
	
	}
}

require_once 'includes/header.inc.php';
?>

<br>
<table class='medium'>
	<tr>
		<td class='header_center'>Order Number</td>
		<td class='header_center'>Email</td>
		<td class='header_center'>Total Cost</td>
		<td class='header_center'>File Name</td>
		<td class='header_center'>Status</td>
	</tr>
	
<?php echo $orders_html; ?>



</table>
<?php require_once 'includes/footer.inc.php'; ?>
