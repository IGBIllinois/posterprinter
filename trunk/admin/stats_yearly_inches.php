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

$totalInches = $stats->totalInches($startDate,$endDate);
?>
<center>
<table class='table_2'>
	<tr><th colspan='2'>Yearly Statistics - <?php echo $year; ?></th></tr>
    <tr>
    	<th align='left'><a href='stats_yearly_inches.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <th align='right'><a href='stats_yearly_inches.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>

    <tr><td>Total Inches:</td><td><?php echo $totalInches; ?></td></tr>

    <tr>
    	<td colspan='2'><img src='graphs/graph_paperTypes_totalInches.php?startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>' /></td>
    </tr>
 
</table>
</center>

<?php include 'includes/footer.inc.php'; ?>