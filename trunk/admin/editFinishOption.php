<?php
include_once 'includes/main.inc.php';

//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	
if (isset($_POST['removeFinishOption'])) {
	$finishOptionId = $_POST['finishOptionId'];
	$defaultSql = "UPDATE tbl_finishOptions SET finishOptions_available=0 WHERE finishOptions_id=$finishOptionId";
	$defaultQuery = mysql_query($defaultSql,$db);
	header("Location: finishOptions.php");

}
elseif (isset($_POST['makeDefault'])) {
	$finishOptionId = $_POST['finishOptionId'];
	$defaultSql = "UPDATE tbl_finishOptions SET finishOptions_default=0";
	$defaultQuery = mysql_query($defaultSql,$db);	
	$defaultSql = "UPDATE tbl_finishOptions SET finishOptions_default=1 WHERE finishOptions_id=$finishOptionId";
	$defaultQuery = mysql_query($defaultSql,$db);
	header("Location: finishOptions.php");
}
elseif (isset($_POST['editFinishOption'])) {
	$finishOptionId = $_POST['finishOptionId'];
	$name = $_POST['name'];
	$cost = $_POST['cost'];
	$maxWidth = $_POST['maxWidth'];
	$maxLength = $_POST['maxLength'];
	$default = $_POST['default'];
	
	$name = trim(rtrim($name));
	$cost = trim(rtrim($cost));
	$maxWidth = trim(rtrim($maxWidth));
	$maxLength = trim(rtrim($maxLength));
	$errors = 0;
	
	if ($name == "") {
		$nameMsg = "<br><b class='error'>Pleae enter finish option name</b>";
		$errors++;
	}
	if (($cost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$cost)) {
		$costMsg = "<br><b class='error'>Please enter a valid cost</b>";
		$errors++;
	}
	
	if (($maxWidth == "") || ($maxWidth > $maxPrinterWidth) || !(eregi("^[0-9]{1,2}$", $maxWidth))) {
		$maxWidthMsg = "<br><b class='error'>Please enter a valid Max Width. Maximum is $maxPrinterWidth inches</b>";
		$errors++;
	}
	if (($maxLength == "") || !(eregi("^[0-9]{1,3}$", $maxLength))) {
		$maxLengthMsg = "<br><b class='error'>Please enter a valid Max Length</b>";
		$errors++;
	}
	if ($errors == 0) {
		
		$addFinishOptionSql = "INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available,finishOptions_default)" .
						"VALUES('$name',$cost,$maxWidth,$maxLength,1,$default)";
		$updateFinishOptionSql = "UPDATE tbl_finishOptions SET finishOptions_name='$name',finishOptions_cost=$cost,finishOptions_maxWidth=$maxWidth,finishOptions_maxLength=$maxLength," .
							"finishOptions_available=0,finishOptions_default=0 WHERE finishOptions_id=$finishOptionId";
		
		$addFinishOptionQuery = mysql_query($addFinishOptionSql,$db);	
		$updateFinishOptionQuery = mysql_query($updateFinishOptionSql,$db);
		
		header("Location: finishOptions.php");
	}
}

elseif (isset($_GET['finishOptionId'])) {
	$finishOptionId = $_GET['finishOptionId'];

	
	$finishOptionSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_id=$finishOptionId";
	$finishOptionResult = mysql_query($finishOptionSql,$db);

	$name = mysql_result($finishOptionResult,0,'finishOptions_name');
	$cost = mysql_result($finishOptionResult,0,'finishOptions_cost');
	$maxWidth = mysql_result($finishOptionResult,0,'finishOptions_maxWidth');
	$maxLength =mysql_result($finishOptionResult,0,'finishOptions_maxLength'); 
	$available = mysql_result($finishOptionResult,0,'finishOptions_available');
	$default = mysql_result($finishOptionResult,0,'finishOptions_default');
	
}

include 'includes/header.inc.php';
?>

<script language="JavaScript">
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree)
	return true ;
else
	return false ;
}

function confirmDefault()
{
var agree=confirm("Are you sure you wish to make this default?");
if (agree)
	return true ;
else
	return false ;
}
function confirmUpdate()
{
var agree=confirm("Are you sure you wish to update?");
if (agree)
	return true ;
else
	return false ;
}
</script>
<form method='post' action='editFinishOption.php?finishOptioinId=<?php echo $finishOptionId; ?>'>
<input type='hidden' name='finishOptionId' value='<?php echo $finishOptionId; ?>' />
<input type='hidden' name='default' value='<?php echo $default; ?>' />
<table class='table_3'>
	<tr>
		<th colspan='2'>Finish Option</th>
	</tr>
	<tr>
		<td class='td_2'>Name:</td>
		<td class='td_3'><input type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/> </td>
	</tr>
	<tr>
		<td class='td_2'>Cost:</td>
		<td class='td_3'><input type='text' name='cost' value='<?php echo $cost; ?>' / size='6'> </td>
	</tr>
	<tr>
		<td class='td_2'>Max Width:</td>
		<td class='td_3'><input type='text' name='maxWidth' value='<?php echo $maxWidth; ?>' / maxlength='2' size='3'>"</td>
	</tr>
	<tr>
		<td class='td_2'>Max Length:</td>
		<td class='td_3'><input type='text' name='maxLength' value='<?php echo $maxLength; ?>' / maxlength='3' size='3'>" </td>
	</tr>
	</table>
	
	<br /><input type='submit' name='editFinishOption' value='Update Finish Option' onClick='return confirmUpdate()'/>
	<?php 
	if ($default==0) { 
		echo "<br><br /><input type='submit' name='makeDefault' value='Make Default' onClick='return confirmDefault()'>";
		echo "<br><br /><input type='submit' name='removeFinishOption' value='Remove Finish Option' onClick='return confirmDelete()'>"; 
	} 
	?>
</form>

<?php 

	if (isset($nameMsg)){echo $nameMsg; }
	if (isset($costMsg)){echo $costMsg; }
	if (isset($maxWidthMsg)){echo $maxWidthMsg; }
	if (isset($maxLengthMsg)){echo $maxLengthMsg; }
 
?>
<?php include 'includes/footer.inc.php'; ?>
