<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';

$finishOptions = getFinishOptions($db);

$finishOptions_html = "";

foreach ($finishOptions as $finishOption) {
	
	$finishOptions_html .= "<tr>";
	$finishOptions_html .= "<td><a href='editFinishOption.php?finishOptionId=" . $finishOption['id'] . "'>";
	$finishOptions_html .= $finishOption['name'] . "</a></td>";
	$finishOptions_html .= "<td>$" . $finishOption['cost'] . "</td>";
	$finishOptions_html .= "<td>" . $finishOption['maxWidth'] . "\"</td>";
	$finishOptions_html .= "<td>" . $finishOption['maxLength'] . "\"</td>";
	if ($finishOption['finishOptions_default'] == 1) {
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
