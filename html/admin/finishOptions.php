<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$finishOptions = functions::getFinishOptions($db);

$finishOptions_html = "";

foreach ($finishOptions as $finishOption) {
	
	$finishOptions_html .= "<tr>";
	$finishOptions_html .= "<td><a href='editFinishOption.php?finishOptionId=" . $finishOption['id'] . "'>";
	$finishOptions_html .= $finishOption['name'] . "</a></td>";
	$finishOptions_html .= "<td>$" . $finishOption['cost'] . "</td>";
	$finishOptions_html .= "<td>" . $finishOption['maxWidth'] . "\"</td>";
	$finishOptions_html .= "<td>" . $finishOption['maxLength'] . "\"</td>";
	if ($finishOption['finishOptions_default'] == 1) {
		$finishOptions_html .= "<td><i class='fa fa-check' aria-hidden='true'></i></td></tr>";
	}
	else {
		$finishOptions_html .= "<td></td></tr>";
	}
								

}

require_once 'includes/header.inc.php';
?>

<h3>Finish Options</h3>
<hr>
<table class='table table-sm table-bordered'>
	<tr>
		<th>Name</th>
		<th>Cost</th>
		<th>Max Width</th>
		<th>Max Length</th>
		<th>Default</th>
		
	</tr>

<?php echo $finishOptions_html; ?>

</table>
<br>
<input class='btn btn-primary' type='button' value='New Finish Option' onClick="location.href='addFinishOption.php';" />

<?php require_once 'includes/footer.inc.php'; ?>
