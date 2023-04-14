<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


if (isset($_GET['year'])) { $year = $_GET['year']; }
else { $year = date('Y'); }

$previous_year = $year -1;
$next_year =$year +1;
$start_date = $year . "/01/01";
$end_date = $year . "/12/31";

//////Year////////
$min_year = functions::get_minimal_year($db);
$year_html = "<select class='form-control' name='year'>";
for ($i=$min_year; $i<=date("Y");$i++) {
        if ($i == $year) { $year_html .= "<option value='" . $i . "' selected='true'>" . $i . "</option>"; }
        else { $year_html .= "<option value='" . $i . "'>" . $i . "</option>"; }
}
$year_html .= "</select>";

$url_navigation = html::get_url_navigation_year($_SERVER['PHP_SELF'],$year);


$stats = new statistics($db,$start_date,$end_date);
$graph_type = "orders_per_month";

?>
<h3>Orders Per Month - <?php echo $year; ?></h3>
<form class='form-inline' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
<div class='form-group'>
        <label for='year'>Year:</label>
        &nbsp; <?php echo $year_html; ?>
</div>
&nbsp;
<div class='form-group'>
        <input type='submit' class='btn btn-primary' value='Get Records'>
</div>
</form>
<p>
<div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Year</a>

        <?php
                if ($next_year > $year) {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' onclick='return false;'>Next Year</a></div>";
                }
                else {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Year</a></div>";
                }
        ?>
        </div>
</div>
<p>

<table class='table table-bordered table-condenesed'>

    <tr><td>Yearly Total:</td><td>$<?php echo $stats->pretty_cost(); ?></td></tr>
    <tr><td>Total Orders:</td><td><?php echo $stats->orders(); ?></td></tr>
</table>
<div class='row'>
<img class='mx-auto' src='graph.php?graph_type=<?php echo $graph_type; ?>&year=<?php echo $year; ?>' />
</div>


<?php require_once '../includes/footer.inc.php'; ?>
