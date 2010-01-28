<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

$finishOptionsSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_available=1 ORDER BY finishOptions_name ASC";
$finishOptionsResult = mysql_query($finishOptionsSql,$db);

$finishOptionsHTML;

for ($i=0;$i<mysql_numrows($finishOptionsResult); $i++) {
	
	$finishOptionsHTML .= "<tr>" .
							"<td><a href='editFinishOption.php?finishOptionId=" . mysql_result($finishOptionsResult,$i,'finishOptions_id') . "'>" . mysql_result($finishOptionsResult,$i,'finishOptions_name') . "</a></td>" .
							"<td>$" . mysql_result($finishOptionsResult,$i,'finishOptions_cost') . "</td>" .
							"<td>" . mysql_result($finishOptionsResult,$i,'finishOptions_maxWidth') . "\"</td>" .
							"<td>" . mysql_result($finishOptionsResult,$i,'finishOptions_maxLength') . "\"</td>";
	if (mysql_result($finishOptionsResult,$i,'finishOptions_default') == 1) {
		$finishOptionsHTML .= "<td>*</td></tr>";
	}
	else {
	$finishOptionsHTML .= "<td></td></tr>";
	}
								

}

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

<?php include 'includes/footer.inc.php'; ?>
