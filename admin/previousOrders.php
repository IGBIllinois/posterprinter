<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'statistics.class.inc.php';
include_once 'functions.inc.php';

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

$ordersHTML;
if (count($orders) == 0) {

	$ordersHTML = "<tr>
					<td>No Orders</td>
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
		
		$ordersHTML .= "<tr>" . 
							"<td><a href='orders.php?orderId=" . $orderId . "'>" . $orderId . "</a></td>" .
							"<td>" . $orderEmail . "</td>" .
							"<td>$" . $orderCost . "</td>" .
							"<td><a href='download.php?orderId=" . $orderId . "'>" . $orderFileName . "</a></td>" .
							"<td>" . $orderStatus  . "</td>" .
						"</tr>";
	
	}
}

$yearHTML = "<select name='year'>";
$monthHTML = "<select name='month'>";

for ($i=2007; $i<=date("Y");$i++) {
	if ($i == $year) {
		$yearHTML .= "<option value='$i' selected='true'>$i</option>";
	}
	else {
		$yearHTML .= "<option value='$i'>$i</option>";
	}
}
$yearHTML .= "</select>";
$monthHTML = "<select name='month'>";
for ($i=1;$i<=12;$i++) {
	if ($i == $month) {
		$monthHTML .= "<option value='$i' selected='true'>$i</option>";
	}
	else {
		$monthHTML .= "<option value='$i'>$i</option>";
	}

}
$monthHTML .= "</select>";

$startDate = $year . "/" . $month . "/01";

$endDate =  date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($startDate))));
$stats = new statistics($db,$startDate,$endDate);

$monthlyTotal = $stats->cost();


?>

<form action='previousOrders.php' method='post'>
<table class='table_2'>
	<tr>
		<td>Month: <?php echo $monthHTML; ?></td>
		<td>Year: <?php echo $yearHTML; ?></td>
		<td><input class='button_1' type='submit' name='selectedDate' value='Get Records'></td>
	</tr>
</table>
</form>
<br />
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
Monthly Total: $<?php echo $monthlyTotal; ?>
<br />
<br />
<form action='reports.php' method='post'>
<input type='hidden' name='month' value='<?php echo $month; ?>' />
<input type='hidden' name='year' value='<?php echo $year; ?>' />
<select name='report_type'>
<option value='excel2003'>Excel 2003</option>
<option value='excel2007'>Excel 2007</option>
<option value='pdf'>PDF</option>
<option value='csv'>CSV</option>
</select>
<input type='submit' class='button_1' name='create_report' value='Create Report' />
</form>
<?php include 'includes/footer.inc.php'; ?>
