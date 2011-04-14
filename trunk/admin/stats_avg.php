<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'statistics.class.inc.php';

?>
<center>
<table class='wide'>
	<tr><td colspan='2' class='header_center'>Yearly Averages</td></tr>
    	<tr>
    	<td class='nav_left'><a href='stats_avg.php?year=<?php echo $previousYear; ?>'>Previous</a></td>
        <td class='nav_right'><a href='stats_avg.php?year=<?php echo $nextYear;?>'>Next</a></td>
    </tr>
    
    <tr><td colspan='2'><img src='graphs/graph_yearlyAvg.php?year=<?php echo $year; ?>' /></td></tr>
</table>
</center>

<?php include_once 'includes/footer.inc.php'; ?>
