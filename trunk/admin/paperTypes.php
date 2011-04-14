<?php
include_once 'includes/main.inc.php';
include_once 'paperTypes.inc.php';

$paperTypes = getPaperTypes($db);

$paperTypes_html = "";

foreach ($paperTypes as $paperType) {
	
	$paperTypes_html .= "<tr>";
	$paperTypes_html .= "<td><a href='editPaperType.php?paperTypeId=" . $paperType['id'] . "'>";
	$paperTypes_html .= $paperType['name'] . "</a></td>";
	$paperTypes_html .= "<td>$" . $paperType['cost'] . "</td>";
	$paperTypes_html .= "<td>" . $paperType['width'] . "\"</td>";
	if ($paperType['paperTypes_default'] == 1) {
		$paperTypes_html .= "<td>*</td></tr>";
	}
	else {
		$paperTypes_html .= "<td></td></tr>";
	}					

}

include_once 'includes/header.inc.php';
?>

<br>
<table class='medium'>
	<tr>
		<td class='header_center'>Name</td>
		<td class='header_center'>Cost per Inch</td>
		<td class='header_center'>Max Width</td>
		<td class='header_center'>Default</td>
		
	</tr>

<?php echo $paperTypes_html; ?>

</table>
<br />
<input type='button' value='New Paper Type' onClick="location.href='addPaperType.php';" />



<?php include_once 'includes/footer.inc.php'; ?>
