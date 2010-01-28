<?php

include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

$paperTypesSql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_available=1 ORDER BY paperTypes_name ASC";
$paperTypesResult = mysql_query($paperTypesSql,$db);

$paperTypesHTML;

for ($i=0;$i<mysql_numrows($paperTypesResult); $i++) {
	
	$paperTypesHTML .= "<tr>" .
							"<td><a href='editPaperType.php?paperTypeId=" . mysql_result($paperTypesResult,$i,'paperTypes_id'). "'>" .mysql_result($paperTypesResult,$i,'paperTypes_name') . "</a></td>" .
							"<td>$" . mysql_result($paperTypesResult,$i,'paperTypes_cost') . "</td>" .
							"<td>" . mysql_result($paperTypesResult,$i,'paperTypes_width') . "\"</td>";
	if (mysql_result($paperTypesResult,$i,'paperTypes_default') == 1) {
		$paperTypesHTML .= "<td>*</td></tr>";
	}
	else {
		$paperTypesHTML .= "<td></td></tr>";
	}					

}

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



<?php include 'includes/footer.inc.php'; ?>
