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

include 'includes/session.inc.php';
include 'includes/header.inc.php';




//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

$ordersSql = "SELECT tbl_orders.*, tbl_status.*, tbl_rushOrder.*
			FROM tbl_orders 
			LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE NOT (status_name='Completed' OR status_name='Cancel')
			ORDER BY orders_id ASC";

//runs query and gets the order_id
$ordersResult = mysql_query($ordersSql,$db);

$ordersHTML;
if (mysql_numrows($ordersResult) == 0) {

	$ordersHTML = "<tr>
					<td>None</td>
					<td></td>
					</tr>";


}
else {
	for ($i=0; $i<mysql_numrows($ordersResult); $i++) {
		
		$orderId = mysql_result($ordersResult,$i,"orders_id");
		$orderEmail = mysql_result($ordersResult,$i,"orders_email");
		$orderFileName = mysql_result($ordersResult,$i,"orders_fileName");
		$orderStatus = mysql_result($ordersResult,$i,"status_name");
		$orderCost = mysql_result($ordersResult,$i,"orders_totalCost");
		$rushOrderName = mysql_result($ordersResult,$i,"rushOrder_name");
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
