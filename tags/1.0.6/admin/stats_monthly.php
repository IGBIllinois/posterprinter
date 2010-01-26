<?php
include 'includes/session.inc.php';
include 'includes/header.inc.php';
include 'includes/statistics.class.inc.php';


if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$month = date('m',strtotime($startDate));
	$year = date('Y',strtotime($startDate));
	$monthName = date('F',strtotime($startDate));
}
else {

	$day = date('d');
	$month = date('m');
	$year = date('Y');
	$date = date('Y/m/d');
	$startMonth = $year . "/" . $month . "/01";
	$startYear = $year . "/01/01";
	$beginning = "0001/01/01";
	$monthName = date('F');
	$startDate = $year . "/" . $month . "/01";
	$endDate = date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($startDate))));

}

$previousEndDate = date('Y/m/d',strtotime('-1 second', strtotime($startDate)));
$previousEndMonth = substr($previousEndDate,5,2);
$previousEndYear = substr($previousEndDate,0,4);
$previousStartDate = $previousEndYear . "/" . $previousEndMonth . "/01";
	
$nextStartDate = date('Y/m/d',strtotime('+1 day', strtotime($endDate)));
$nextEndDate =  date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($nextStartDate))));

if (isset($_POST['graphType'])) {
	
	$graphType = $_POST['graphType'];
	$graphImage = "<img src='graphs/graph_" . $graphType . ".php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";

}
else {
	$graphImage = "<img src='graphs/graph_finishOptions.php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";
	$graphType = "finishOptions";
}

$graphForm = "<form name='selectGraph' id='selectGraph' method='post' action='stats_monthly.php?startDate=" . $startDate . "&endDate=" . $endDate . "'>";
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

$stats = new statistics($mysqlSettings);
$monthlyTotal = $stats->cost($startDate,$endDate);
$rushOrderPercent = $stats->percentRushOrder($startDate,$endDate);
$posterTubePercent = $stats->percentPosterTube($startDate,$endDate);
$averagePosterCost = $stats->averagePosterCost($startDate,$endDate);
$totalInches = $stats->totalInches($startDate,$endDate);
$totalOrders = $stats->orders($startDate,$endDate);

$url = "stats_monthly.php";
$backUrl = $url . "?startDate=" . htmlspecialchars($previousStartDate,ENT_QUOTES) . "&endDate=" . htmlspecialchars($previousEndDate,ENT_QUOTES);
$forwardUrl = $url . "?startDate=" . htmlspecialchars($nextStartDate,ENT_QUOTES) . "&endDate=" . htmlspecialchars($nextEndDate,ENT_QUOTES);

?>
<center>
<table class='table_4'>
	<tr><th colspan='2'>Monthly Statistics - <?php echo $monthName . " " . $year; ?></th></tr>
    <tr>
    	<th align='left'><a href='<?php echo $backUrl; ?>'>Previous</a></th>
        
        <th align='right'><a href='<?php echo $forwardUrl; ?>'>Next</a></th>
    </tr>
  	<tr><td>Monthly Total:</td><td>$<?php echo $monthlyTotal; ?></td></tr>
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