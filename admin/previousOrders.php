<?php
require_once 'includes/main.inc.php';

require_once 'statistics.class.inc.php';
require_once 'orders.inc.php';

if (isset($_POST['selectedDate'])) {
	$year = $_POST['year'];
	$month = $_POST['month'];
	
}
else {
	$year = date("Y");
	$month = date("m");
}


//runs query and gets previous orders
$orders = getPreviousOrders($db,$month,$year);

$orders_html = "";
if (count($orders) == 0) { $orders_html = "<tr><td colspan='5'>No Orders</td></tr>"; }
else {
	foreach ($orders as $order) {
		$orders_html .= "<tr>"; 
		$orders_html .= "<td><a href='orders.php?orderId=" . $order["orders_id"] . "'>" . $order["orders_id"] . "</a></td>";
		$orders_html .= "<td>" . $order["orders_email"] . "</td>";
		$orders_html .= "<td>$" . $order["orders_totalCost"] . "</td>";
		$orders_html .= "<td><a href='download.php?orderId=" . $order["orders_id"] . "'>" . $order["orders_fileName"] . "</a></td>";
		$orders_html .= "<td>" . $order["status_name"]  . "</td>";
		$orders_html .= "</tr>";
	
	}
}

//////Year////////
$year_html = "<select name='year'>";
for ($i=2007; $i<=date("Y");$i++) {
	if ($i == $year) { $year_html .= "<option value='$i' selected='true'>$i</option>"; }
	else { $year_html .= "<option value='$i'>$i</option>"; }
}
$year_html .= "</select>";

///////Month///////
$month_html = "<select name='month'>";
for ($i=1;$i<=12;$i++) {
	if ($i == $month) { $month_html .= "<option value='$i' selected='true'>$i</option>"; }
	else { $month_html .= "<option value='$i'>$i</option>"; }
}
$month_html .= "</select>";


$startDate = $year . "/" . $month . "/01";
$endDate =  date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($startDate))));
$stats = new statistics($db,$startDate,$endDate);


require_once 'includes/header.inc.php';

?>

<form action='previousOrders.php' method='post'>
<table>
	<tr>
		<td>Month: <?php echo $month_html; ?></td>
		<td>Year: <?php echo $year_html; ?></td>
		<td><input class='button_1' type='submit' name='selectedDate' value='Get Records'></td>
	</tr>
</table>
</form>
<br />
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
Monthly Total: $<?php echo $stats->pretty_cost(); ?>
<br />
<br />
<form action='reports.php' method='post'>
<input type='hidden' name='month' value='<?php echo $month; ?>' />
<input type='hidden' name='year' value='<?php echo $year; ?>' />
<select name='report_type'>
<option value='excel2003'>Excel 2003</option>
<option value='excel2007'>Excel 2007</option>
<option value='csv'>CSV</option>
</select>
<input type='submit' class='button_1' name='create_report' value='Create Report' />
</form>
<?php require_once 'includes/footer.inc.php'; ?>
