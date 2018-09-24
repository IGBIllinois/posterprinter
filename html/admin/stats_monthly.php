<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$month = date('m',strtotime($start_date));
	$year = date('Y',strtotime($start_date));
	$monthName = date('F',strtotime($start_date));
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
	$start_date = $year . "/" . $month . "/01";
	$end_date = date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));

}

$previousEndDate = date('Y/m/d',strtotime('-1 second', strtotime($start_date)));
$previousEndMonth = substr($previousEndDate,5,2);
$previousEndYear = substr($previousEndDate,0,4);
$previousStartDate = $previousEndYear . "/" . $previousEndMonth . "/01";
	
$nextStartDate = date('Y/m/d',strtotime('+1 day', strtotime($end_date)));
$nextEndDate =  date('Y/m/d',strtotime('-1 second',strtotime('+1 month',strtotime($nextStartDate))));

if (isset($_POST['graph_type'])) {
	
	$graph_type = $_POST['graph_type'];
	$graphImage = "<img class='mx-auto' src='graph.php?graph_type=" . $graph_type . "&start_date=" . $start_date . "&end_date=" . $end_date . "'>";

}
else {
	$graphImage = "<img class='mx-auth' src='graph.php?graph_type=finishoptions&start_date=" . $start_date . "&end_date=" . $end_date . "'>";
	$graphType = "finishOptions";
}

$graphForm = "<form class='form' name='selectGraph' method='post' action='stats_monthly.php?start_date=" . $start_date . "&end_date=" . $end_date . "'>";
$graphForm .= "<div class='col-md-2'><select class='custom-select' name='graph_type' onChange='document.selectGraph.submit();'>";

if ($graph_type == "finishoptions") { $graphForm .= "<option value='finishoptions' selected>Finish Options</option>"; }
else { $graphForm .= "<option value='finishOptions'>Finish Options</option>"; }
if ($graph_type == "papertypes") { $graphForm .= "<option value='papertypes' selected>Paper Types</option>"; }
else { $graphForm .= "<option value='paperTypes'>Paper Types</option>"; }
if ($graph_type == "inches_per_papetype") { $graphForm .= "<option value='inches_per_papertype' selected>Inches Per Paper Type</option>"; }
else { $graphForm .= "<option value='inchesPerPaperType'>Inches Per Paper Type</option>"; }

$graphForm .= "</select></div>";
$graphForm .= "</form>";

$stats = new statistics($db,$start_date,$end_date);

$url = "stats_monthly.php";
$backUrl = $url . "?start_date=" . htmlspecialchars($previousStartDate,ENT_QUOTES) . "&end_date=" . htmlspecialchars($previousEndDate,ENT_QUOTES);
$forwardUrl = $url . "?start_date=" . htmlspecialchars($nextStartDate,ENT_QUOTES) . "&end_date=" . htmlspecialchars($nextEndDate,ENT_QUOTES);

?>
<h3>Monthly Statistics - <?php echo $monthName . " " . $year; ?></h3>
<hr>
<ul class='pagination justify-content-center'>
<li class='page-item'><a class='page-link' href='<?php echo $backUrl; ?>'>Previous</a></li>
<?php
	$next_month = strtotime('+1 day', strtotime($end_date));
	$today = mktime(0,0,0,date('m'),date('d'),date('y'));
	if ($next_month > $today) {
		echo "<li class='page-item disabled'><a class='page-link' href='#'>Next</a></li>";
	}
	else {
		echo "<li class='page-item'><a class='page-link' href='" . $forwardUrl . "'>Next</a></li>";
	}
?>

</ul>

<table class='table table-bordered table-sm table-striped'>

  	<tr><td>Monthly Total:</td><td>$<?php echo $stats->pretty_cost(); ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $stats->orders(); ?></td></tr>
    <tr><td>Rush Order Percentage:</td><td><?php echo $stats->percentRushOrder(); ?>%</td></tr>
    <tr><td>Poster Tube Percentage:</td><td><?php echo $stats->percentPosterTube(); ?>%</td></tr>
    <tr><td>Total Inches Printed:</td><td><?php echo $stats->pretty_totalInches(); ?>"</td></tr>
    <tr><td>Average Poster Cost:</td><td>$<?php echo $stats->averagePosterCost(); ?></td></tr>
	<tr><td colspan='2'><?php echo $graphForm; ?></td></tr>
</table>
 <div class='row'><?php echo $graphImage; ?></div>

<?php require_once 'includes/footer.inc.php'; ?>
