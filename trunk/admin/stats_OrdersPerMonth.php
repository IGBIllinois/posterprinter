<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'statistics.class.inc.php';


if (isset($_GET['year'])) { $year = $_GET['year']; }
else { $year = date('Y'); }

$previousYear = $year -1;
$nextYear =$year +1;
$startDate = $year . "/01/01";
$endDate = $year . "/12/31";

$stats = new statistics($db,$startDate,$endDate);


?>
<center>
<table class='medium'>
	<tr><td colspan='2' class='header_center'>Yearly Stats - <?php echo $year; ?></td></tr>
    <tr>
    	<td class='nav_left'><a href='stats_OrdersPerMonth.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <td class='nav_right'><a href='stats_OrdersPerMonth.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
    <tr><td>Yearly Total:</td><td>$<?php echo $stats->pretty_cost(); ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $stats->orders(); ?></td></tr>
    <tr><td colspan='2'><img src='graphs/graph_ordersPerMonth.php?year=<?php echo $year; ?>' /></td></tr>
</table>
</center>

<?php include_once 'includes/footer.inc.php'; ?>
