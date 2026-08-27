<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

$year = date('Y');
if (isset($_GET['year'])) { 
	$year = $_GET['year']; 
}

$previous_year = $year -1;
$next_year =$year + 1;
$start_date = $year . "/01/01";
$end_date = $year . "/12/31";

//////Year////////
$min_year = max(functions::get_minimal_year($db), date("Y") - 50); // Last 20 years max
$current_year = date("Y");

$year_html = "<select class='form-select' name='year' onchange='this.form.submit();'>";
for ($i = $min_year; $i <= $current_year; $i++) {
    $selected = ($i == $year) ? 'selected' : '';
    $year_html .= "<option value='$i' $selected>$i</option>";
}
$year_html .= "</select>";

$graph_type = "finishoptions";
if (isset($_POST['graph_type'])) {	
	$graph_type = $_POST['graph_type'];

}

$graph_type_array[0]['type'] = 'finishoptions';
$graph_type_array[0]['title'] = 'Finish Options';
$graph_type_array[1]['type'] = 'papertypes';
$graph_type_array[1]['title'] = 'Paper Types';
$graph_type_array[2]['type'] = 'inches_per_papertype';
$graph_type_array[2]['title'] = 'Inches Per Paper Type';

$graph_get_array = array('graph_type'=>$graph_type,
		'start_date'=>$start_date,
		'end_date'=>$end_date
	);
$graphImage = "<img class='mx-auto' src='graph.php?" . http_build_query($graph_get_array) . "'>";

$stats = new statistics($db,$start_date,$end_date);

$graph_form = "<select class='form-select' name='graph_type' onChange='document.selectGraph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}
$graph_form .= "</select>";

?>

<h3>Yearly Statistics</h3>
<form class='d-flex align-items-center' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
<div class='mb-3'>
        <label for='year'>Year:</label>
        &nbsp; <?php echo $year_html; ?>
</div>
&nbsp;
</form>
<p>
<div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
                <a class='btn btn-sm btn-primary' href='<?php echo $_SERVER['PHP_SELF'] . "?year=" . $previous_year; ?>'>Previous Year</a>
                <a class='btn btn-sm btn-primary' href='<?php echo $_SERVER['PHP_SELF'] . "?year=" . $next_year; ?>'>Next Year</a>
        </div>
</div>
<p>
<table class='table table-bordered table-sm table-striped'>
	<tr><td>Yearly Total:</td><td>$<?php echo $stats->pretty_cost(); ?></td></tr>
	<tr><td>Total Orders:</td><td><?php echo $stats->orders(); ?></td></tr>
    <tr><td>Rush Order Percentage:</td><td><?php echo $stats->percentRushOrder(); ?>%</td></tr>
    <tr><td>Poster Tube Percentage:</td><td><?php echo $stats->percentPosterTube(); ?>%</td></tr>
    <tr><td>Total Inches Printed:</td><td><?php echo $stats->pretty_totalInches(); ?>"</td></tr>
    <tr><td>Average Poster Cost:</td><td>$<?php echo $stats->averagePosterCost(); ?></td></tr>
    <tr><td colspan='2'>
	<form class='form' name='selectGraph' id='selectGraph' method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?year=" . $year; ?>'>
		<div class='col-sm-2 col-md-2'>
			 <?php echo $graph_form; ?>
		</div>
	</form>
</td></tr>
</table>

<div class='row'><?php echo $graphImage; ?></div>


<?php require_once '../includes/footer.inc.php'; ?>