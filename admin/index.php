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

$ordersHTML;
if (count($orders) == 0) {

	$ordersHTML = "<tr>
					<td>None</td>
					<td></td>
					</tr>";


}
else {
	for ($i=0; $i<count($orders); $i++) {
		
		$orderId = $orders[$i]["orders_id"];
		$orderEmail = $orders[$i]["orders_email"];
		$orderFileName = $orders[$i]["orders_fileName"];
		$orderStatus = $orders[$i]["status_name"];
		$orderCost = $orders[$i]["orders_totalCost"];
		$rushOrderName = $orders[$i]["rushOrder_name"];
		$ordersHTML;
		if ($rushOrderName == "Yes") {
			$ordersHTML .= "<tr class='rush'>";
		}
		elseif ($rushOrderName == "No") {
			$ordersHTML .= "<tr>";
		}
		 $ordersHTML .= "<td><a href='orders.php?orderId=" . $orderId . "'>" . $orderId . "</a></td>" .
							"<td>" . $orderEmail . "</td>" .
							"<td>" . $orderCost . "</td>" .
							"<td><a href='download.php?orderId=" . $orderId . "'>" . $orderFileName . "</a></td>" .
							"<td>" . $orderStatus  . "</td>" .
						"</tr>";
	
	}
}
?>


<table class='table_2'>
	<thead>
	<tr>
		<th>Order Number</th>
		<th>Email</th>
		<th>Total Cost</th>
		<th>File Name</th>
		<th>Status</th>
	</tr>
	</thead>
	<tbody>
	
<?php echo $ordersHTML; ?>

	</tbody>


</table>

<?php include 'includes/footer.inc.php'; ?>
