<?php
include_once 'includes/main.inc.php';
include_once 'paperTypes.inc.php';

$paperTypes = getPaperTypes($db);

$paperTypes_html;

for ($i=0;$i<count($paperTypes); $i++) {
	
	$paperTypesHTML .= "<tr>" .
	"<td><a href='editPaperType.php?paperTypeId=" . $paperTypes[$i]['paperTypes_id'] . "'>" . $paperTypes[$i]['paperTypes_name'] . "</a></td>" .
	"<td>$" . $paperTypes[$i]['paperTypes_cost'] . "</td>" .
	"<td>" . $paperTypes[$i]['paperTypes_width'] . "\"</td>";
	if ($paperTypes[$i]['paperTypes_default'] == 1) {
		$paperTypesHTML .= "<td>*</td></tr>";
	}
	else {
		$paperTypesHTML .= "<td></td></tr>";
	}					

}

include_once 'includes/header.inc.php';
?>

<br>
<table class='table_2'>
	<tr>
		<th>Name</th>
		<th>Cost per Inch</th>
		<th>Max Width</th>
		<th>Default</th>
		
	</tr>

<?php echo $paperTypesHTML; ?>

</table>
<br />
<input type='button' value='New Paper Type' onClick="location.href='addPaperType.php';" />



<?php include_once 'includes/footer.inc.php'; ?>
