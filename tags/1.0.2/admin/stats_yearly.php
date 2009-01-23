<?php
include 'includes/session.inc.php';
include 'includes/header.inc.php';
include 'includes/statistics.class.inc.php';


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

$stats = new statistics($mysqlSettings);
$yearlyTotal = $stats->cost($startDate,$endDate);

$rushOrderPercent = $stats->percentRushOrder($startDate,$endDate);
$posterTubePercent = $stats->percentPosterTube($startDate,$endDate);
$averagePosterCost = $stats->averagePosterCost($startDate,$endDate);
$totalOrders = $stats->orders($startDate,$endDate);

?>
<center>
<table class='table_2'>
	<tr><th colspan='2'>Yearly Statistics - <?php echo $year; ?></th></tr>
    <tr>
    	<th align='left'><a href='stats_yearly.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <th align='right'><a href='stats_yearly.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
  	<tr><td>Yearly Total:</td><td>$<?php echo $yearlyTotal; ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $totalOrders; ?></td></tr>
    <tr><td>Rush Order Percentage:</td><td><?php echo $rushOrderPercent; ?>%</td></tr>
    <tr><td>Poster Tube Percentage:</td><td><?php echo $posterTubePercent; ?>%</td></tr>
    <tr><td>Average Poster Cost:</td><td>$<?php echo $averagePosterCost; ?></td></tr>
	
    <tr>
    	<td colspan='2'><img src='graphs/graph_paperTypes.php?startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>' /></td>
    </tr>
    <tr>
    	<td colspan='2'><img src='graphs/graph_finishOptions.php?startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>' /></td>
</table>
</center>

<?php include 'includes/footer.inc.php'; ?>