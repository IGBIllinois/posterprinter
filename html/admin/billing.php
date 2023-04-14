<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_POST['selectedDate'])) {
	$year = $_POST['year'];
	$month = $_POST['month'];
	
}
else {
	$year = date("Y");
	$month = date("m");
}


//runs query and gets previous orders
$orders = functions::getPreviousOrders($db,$month,$year);

$orders_html = "";
if (count($orders) == 0) { $orders_html = "<tr><td colspan='5'>No Orders</td></tr>"; }
else {
	foreach ($orders as $order) {
		$orders_html .= "<tr>"; 
		$orders_html .= "<td><a href='order.php?order_id=" . $order["orders_id"] . "'>" . $order["orders_id"] . "</a></td>";
		$orders_html .= "<td>" . $order["orders_email"] . "</td>";
		$orders_html .= "<td>$" . $order["orders_totalCost"] . "</td>";
		$orders_html .= "<td><a href='download.php?orderId=" . $order["orders_id"] . "'>" . $order["orders_fileName"] . "</a></td>";
		$orders_html .= "<td>" . $order["orders_status"]  . "</td>";
		$orders_html .= "</tr>";
	
	}
}

//////Year////////
$year_html = "<select class='form-control' name='year'>";
for ($i=2007; $i<=date("Y");$i++) {
	if ($i == $year) { $year_html .= "<option value='$i' selected='true'>$i</option>"; }
	else { $year_html .= "<option value='$i'>$i</option>"; }
}
$year_html .= "</select>";

///////Month///////
$month_html = "<select class='form-control' name='month'>";
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

<h3>Billing Reports</h3>
<hr>
<form class='form-inline' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
<div class='form-group'>
	<label for='month'>Month:</label>
	&nbsp;<?php echo $month_html; ?>
</div>&nbsp;
<div class='form-group'>
	<label for='year'>Year:</label>
	&nbsp; <?php echo $year_html; ?>
</div>
&nbsp;
<div class='form-group'>
	<button class='btn btn-primary' type='submit' name='selectedDate'>Get Records</button>
</div>
</form>
<br />
<table class='table table-bordered table-sm table-striped'>
	<thead class='thead-light'>
	<tr>
		<th>Order Number</th>
		<th>Email</th>
		<th>Total Cost</th>
		<th>File Name</th>
		<th>Status</th>
	</tr>
	</thead>
<?php echo $orders_html; ?>



</table>
<p>Monthly Total: $<?php echo $stats->pretty_cost(); ?></p>

<form class='form' action='report.php' method='post'>
<div class='row'>
<div class='col-md-2 col-lg-2 col-xl-2'>
<input type='hidden' name='month' value='<?php echo $month; ?>' />
<input type='hidden' name='year' value='<?php echo $year; ?>' />
<select class='form-control' name='report_type'>
<option value='xlsx'>Excel 2007</option>
<option value='csv'>CSV</option>
</select>
</div>
<button type='submit' class='btn btn-primary' name='create_report'>Create Report</button>&nbsp;
<button type='submit' class='btn btn-primary' name='create_boa_report'>Create BOA Report</button>
</div>
</form>
<?php require_once '../includes/footer.inc.php'; ?>
