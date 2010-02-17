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

if (isset($_POST['graphType'])) {
	
	$graphType = $_POST['graphType'];
	$graphImage = "<img src='graphs/graph_" . $graphType . ".php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";

}
else {
	$graphImage = "<img src='graphs/graph_finishOptions.php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";
	$graphType = "finishOptions";
}

$stats = new statistics($db,$startDate,$endDate);
$yearlyTotal = $stats->cost();
$rushOrderPercent = $stats->percentRushOrder();
$posterTubePercent = $stats->percentPosterTube();
$averagePosterCost = $stats->averagePosterCost();
$totalInches = $stats->totalInches();
$totalOrders = $stats->orders();

$graphForm = "<form name='selectGraph' id='selectGraph' method='post' action='stats_yearly.php?year=" . $year . "'>";
$graphForm .= "<select name='graphType' onChange='document.selectGraph.submit();'>";

if ($graphType == "finishOptions") {
	$graphForm .= "<option value='finishOptions' selected>Finish Options</option>";
}
else {
	$graphForm .= "<option value='finishOptions'>Finish Options</option>";
}
if ($graphType == "paperTypes") {
	$graphForm .= "<option value='paperTypes' selected>Paper Types</option>";
}
else {
	$graphForm .= "<option value='paperTypes'>Paper Types</option>";
}
if ($graphType == "inchesPerPaperType") {
	$graphForm .= "<option value='inchesPerPaperType' selected>Inches Per Paper Type</option>";
}
else {
	$graphForm .= "<option value='inchesPerPaperType'>Inches Per Paper Type</option>";
}

$graphForm .= "</select>";
$graphForm .= "</form>";
?>


<center>
<table class='table_4'>
	<tr><th colspan='2'>Yearly Statistics - <?php echo $year; ?></th></tr>
    <tr>
    	<th align='left'><a href='stats_yearly.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <th align='right'><a href='stats_yearly.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
  	<tr><td>Yearly Total:</td><td>$<?php echo $yearlyTotal; ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $totalOrders; ?></td></tr>
    <tr><td>Rush Order Percentage:</td><td><?php echo $rushOrderPercent; ?>%</td></tr>
    <tr><td>Poster Tube Percentage:</td><td><?php echo $posterTubePercent; ?>%</td></tr>
    <tr><td>Total Inches Printed:</td><td><?php echo $totalInches; ?>"</td></tr>
    <tr><td>Average Poster Cost:</td><td>$<?php echo $averagePosterCost; ?></td></tr>
    <tr><td><?php echo $graphForm; ?></td></tr>
    <tr>
    	<td colspan='2'><?php echo $graphImage; ?></td>
    </tr>

</table>
</center>

<?php include_once 'includes/footer.inc.php'; ?>
