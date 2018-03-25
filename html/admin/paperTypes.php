<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$paperTypes = functions::getPaperTypes($db);

$paperTypes_html = "";

foreach ($paperTypes as $paperType) {
	
	$paperTypes_html .= "<tr>";
	$paperTypes_html .= "<td><a href='editPaperType.php?paperTypeId=" . $paperType['id'] . "'>";
	$paperTypes_html .= $paperType['name'] . "</a></td>";
	$paperTypes_html .= "<td>$" . $paperType['cost'] . "</td>";
	$paperTypes_html .= "<td>" . $paperType['width'] . "\"</td>";
	if ($paperType['paperTypes_default'] == 1) {
		$paperTypes_html .= "<td><span class='glyphicon glyphicon-ok'></span></td></tr>";
	}
	else {
		$paperTypes_html .= "<td></td></tr>";
	}					

}

require_once 'includes/header.inc.php';
?>

<h3>Paper Types</h3>
<hr>
<table class='table table-sm table-bordered'>
	<tr>
		<th>Name</th>
		<th>Cost per Inch</th>
		<th>Max Width</th>
		<th>Default</th>
		
	</tr>

<?php echo $paperTypes_html; ?>

</table>
<br>
<input class='btn btn-primary' type='button' value='New Paper Type' onClick="location.href='addPaperType.php';" />



<?php require_once 'includes/footer.inc.php'; ?>
