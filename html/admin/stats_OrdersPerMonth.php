<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


if (isset($_GET['year'])) { $year = $_GET['year']; }
else { $year = date('Y'); }

$previousYear = $year -1;
$nextYear =$year +1;
$startDate = $year . "/01/01";
$endDate = $year . "/12/31";

$stats = new statistics($db,$startDate,$endDate);


?>
<h3>Orders Per Month - <?php echo $year; ?></h3>
<hr>
<ul class='pagination justify-content-center'>
<li class='page-item'><a class='page-link' href='stats_OrdersPerMonth.php?year=<?php echo $previousYear; ?>'>Previous</a></li>
<?php
        $next_year = strtotime('+1 day', strtotime($endDate));
	$today = mktime(0,0,0,date('m'),date('d'),date('y'));

        if ($next_year > $today) {
                echo "<li class='page-item disabled'><a class='page-link' href='#'>Next</a></li>";
        }
        else {
                echo "<li class='page-item'><a class='page-link' href='stats_OrdersPerMonth.php?year=" . $nextYear  . "'>Next</a></li>";
        }
?>

</ul>
<table class='table table-bordered table-condenesed'>

    <tr><td>Yearly Total:</td><td>$<?php echo $stats->pretty_cost(); ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $stats->orders(); ?></td></tr>
</table>
<div class='row'>
<img class='mx-auto' src='graphs/graph_ordersPerMonth.php?year=<?php echo $year; ?>' />
</div>


<?php require_once 'includes/footer.inc.php'; ?>
