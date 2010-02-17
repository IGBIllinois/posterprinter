<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'statistics.class.inc.php';


if (isset($_GET['year'])) {
	$year = $_GET['year'];
}
else {

	$year = date('Y');
	$startDate = $year . "/" . $month . "/01";
	$endDate = date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($startDate))));

}


$previousYear = $year -1;
$nextYear =$year +1;
$startDate = $year . "/01/01";
$endDate = $year . "/12/31";

$stats = new statistics($db,$startDate,$endDate);

$yearlyTotal = $stats->cost();
$totalOrders = $stats->orders();


?>
<center>
<table class='table_4'>
	<tr>
    	<th colspan='2'>Yearly Stats - <?php echo $year; ?></th>
    
    </tr>
    
	<tr>
    	<th align='left'><a href='stats_OrdersPerMonth.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <th align='right'><a href='stats_OrdersPerMonth.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
    <tr><td>Yearly Total:</td><td>$<?php echo $yearlyTotal; ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $totalOrders; ?></td></tr>
    <tr><td colspan='2'><img src='graphs/graph_ordersPerMonth.php?year=<?php echo $year; ?>' /></td></tr>
</table>
</center>

<?php include_once 'includes/footer.inc.php'; ?>
