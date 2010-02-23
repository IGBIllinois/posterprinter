<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';

$finishOptions = getFinishOptions($db);

$finishOptions_html = "";

for ($i=0;$i<count($finishOptions); $i++) {
	
	$finishOptions_html .= "<tr>";
	$finishOptions_html .= "<td><a href='editFinishOption.php?finishOptionId=" . $finishOptions[$i]['finishOptions_id'] . "'>";
	$finishOptions_html .= $finishOptions[$i]['finishOptions_name'] . "</a></td>";
	$finishOptions_html .= "<td>$" . $finishOptions[$i]['finishOptions_cost'] . "</td>";
	$finishOptions_html .= "<td>" . $finishOptions[$i]['finishOptions_maxWidth'] . "\"</td>";
	$finishOptions_html .= "<td>" . $finishOptions[$i]['finishOptions_maxLength'] . "\"</td>";
	if ($finishOptions[$i]['finishOptions_default'] == 1) {
		$finishOptions_html .= "<td>*</td></tr>";
	}
	else {
		$finishOptions_html .= "<td></td></tr>";
	}
								

}

include_once 'includes/header.inc.php';
?>

<br>
<table class='medium'>
	<tr>
		<td class='header_center'>Name</td>
		<td class='header_center'>Cost</td>
		<td class='header_center'>Max Width</td>
		<td class='header_center'>Max Length</td>
		<td class='header_center'>Default</td>
		
	</tr>

<?php echo $finishOptions_html; ?>

</table>
<br />
<input type='button' value='New Finish Option' onClick="location.href='addFinishOption.php';" />

<?php include_once 'includes/footer.inc.php'; ?>
