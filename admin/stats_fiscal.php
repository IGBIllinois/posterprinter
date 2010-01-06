<?php
include 'includes/session.inc.php';
include 'includes/header.inc.php';
include 'includes/statistics.class.inc.php';

$month = date('m');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}
else {

	if ($month < 7) {
		$year = date('Y');
	}
	elseif ($month >= 7) {
		$year = date('Y') + 1;
	}	
	

}

$previousYear = $year -1;
$nextYear =$year +1;
$startDate = $year -1 . "/07/01";
$endDate = $year . "/06/30";

if (isset($_POST['graphType'])) {
	
	$graphType = $_POST['graphType'];
	$graphImage = "<img src='graphs/graph_" . $graphType . ".php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";

}
else {
	$graphImage = "<img src='graphs/graph_finishOptions.php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";
	$graphType = "finishOptions";
}

$stats = new statistics($mysqlSettings);
$yearlyTotal = $stats->cost($startDate,$endDate);

$rushOrderPercent = $stats->percentRushOrder($startDate,$endDate);
$posterTubePercent = $stats->percentPosterTube($startDate,$endDate);
$averagePosterCost = $stats->averagePosterCost($startDate,$endDate);
$totalInches = $stats->totalInches($startDate,$endDate);
$totalOrders = $stats->orders($startDate,$endDate);

$graphForm = "<form name='selectGraph' id='selectGraph' method='post' action='stats_fiscal.php?year=" . $year . "'>";
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
	<tr><th colspan='2'>Fiscal Year Statistics - <?php echo $year; ?></th></tr>
    <tr>
    	<th align='left'><a href='stats_fiscal.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <th align='right'><a href='stats_fiscal.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
  	<tr><td>Fiscal Yearly Total:</td><td>$<?php echo $yearlyTotal; ?></td></tr>
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

<?php include 'includes/footer.inc.php'; ?>