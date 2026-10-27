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
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


$orders = functions::getCurrentOrders($db);

// Requested pickup date is stored as a labeled line in the comments (no DB
// column yet). Read it for display and sort by it: soonest pickup first,
// orders without one (older orders) at the end, ties by order number.
foreach ($orders as &$o) {
	// e.g. "2026-10-29 afternoon"; sorts correctly as text (morning < afternoon
	// is handled by the explicit slot order below).
	$o['pickup'] = preg_match('/^Requested pickup: (\d{4}-\d{2}-\d{2})(?: (morning|afternoon))?/m', (string) $o['orders_comments'], $m)
		? trim($m[1] . ' ' . ($m[2] ?? '')) : '';
	$o['pickup_key'] = ($o['pickup'] === '') ? '' : $m[1] . (($m[2] ?? '') === 'afternoon' ? '-2' : '-1');
}
unset($o);
usort($orders, function ($a, $b) {
	if ($a['pickup_key'] === $b['pickup_key']) return $a['orders_id'] <=> $b['orders_id'];
	if ($a['pickup_key'] === '') return 1;
	if ($b['pickup_key'] === '') return -1;
	return strcmp($a['pickup_key'], $b['pickup_key']);
});

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$orders_html = "";
if (count($orders) == 0) {
	$orders_html = "<tr><td colspan='6'>None</td></tr>";
}
else {
	foreach ($orders as $order) {
		$orders_html .= ($order["rushOrder_name"] == "Yes") ? "<tr class='table-danger'>" : "<tr>";
		$orders_html .= "<td><a href='order.php?order_id=" . (int) $order["orders_id"] . "'>" . (int) $order["orders_id"] . "</a></td>";
		$orders_html .= "<td>" . $h($order["pickup"]) . "</td>";
		$orders_html .= "<td>" . $h($order["orders_email"]) . "</td>";
		$orders_html .= "<td>$" . $h($order["orders_totalCost"]) . "</td>";
		$orders_html .= "<td><a href='download.php?orderId=" . (int) $order["orders_id"] . "'>" . $h($order["orders_fileName"]) . "</a></td>";
		$orders_html .= "<td>" . $h($order["orders_status"]) . "</td>";
		$orders_html .= "</tr>";
	}
}


?>
<h3>Current Orders</h3>
<hr>
<table class='table table-bordered table-sm table-striped'>
	<thead class='thead-light'>
	<tr>
		<th>Order Number</th>
		<th>Pickup Date</th>
		<th>Email</th>
		<th>Total Cost</th>
		<th>File Name</th>
		<th>Status</th>
	</tr>
	</thead>	
<?php echo $orders_html; ?>



</table>

<?php require_once '../includes/footer.inc.php'; ?>
