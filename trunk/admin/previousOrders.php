<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'statistics.class.inc.php';

if (isset($_POST['selectedDate'])) {
	$year = $_POST['year'];
	$month = $_POST['month'];
	
}
else {
	$year = date("Y");
	$month = date("m");
}

$ordersSql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.* 
	FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
	LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
	LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id 
	WHERE (YEAR(orders_timeCreated)=$year AND month(orders_timeCreated)=$month)
	AND (status_name='Completed' OR status_name='Cancel')
	ORDER BY orders_id ASC";


//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

//runs query and gets previous orders
$ordersResult = mysql_query($ordersSql,$db);

$ordersHTML;
if (mysql_numrows($ordersResult) == 0) {

	$ordersHTML = "<tr>
					<td>No Orders</td>
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

$stats = new statistics($db,$startDate,$endDate);
$startDate = $year . "/" . $month . "/01";

$endDate =  date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($startDate))));

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
