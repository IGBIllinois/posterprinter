<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';

$finishOptions = getFinishOptions($db);

$finishOptionsHTML;

for ($i=0;$i<count($finishOptions); $i++) {
	
	$finishOptionsHTML .= "<tr>" .
	"<td><a href='editFinishOption.php?finishOptionId=" . $finishOptions[$i]['finishOptions_id'] . "'>" . $finishOptions[$i]['finishOptions_name'] . "</a></td>" .
							"<td>$" . $finishOptions[$i]['finishOptions_cost'] . "</td>" .
							"<td>" . $finishOptions[$i]['finishOptions_maxWidth'] . "\"</td>" .
							"<td>" . $finishOptions[$i]['finishOptions_maxLength'] . "\"</td>";
	if ($finishOptions[$i]['finishOptions_default'] == 1) {
		$finishOptionsHTML .= "<td>*</td></tr>";
	}
	else {
	$finishOptionsHTML .= "<td></td></tr>";
	}
								

}

include_once 'includes/header.inc.php';
?>

<br>
<table class='table_2'>
	<tr>
		<th>Name</th>
		<th>Cost</th>
		<th>Max Width</th>
		<th>Max Length</th>
		<th>Default</th>
		
	</tr>

<?php echo $finishOptionsHTML; ?>

</table>
<br />
<input type='button' value='New Finish Option' onClick="location.href='addFinishOption.php';" />

<?php include_once 'includes/footer.inc.php'; ?>
