<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';

	
if (isset($_POST['removeFinishOption'])) {
	$finishOptionId = $_POST['finishOptionId'];
	deleteFinishOption($db,$finishOptionId);	
	header("Location: finishOptions.php");

}
elseif (isset($_POST['makeDefault'])) {
	$finishOptionId = $_POST['finishOptionId'];
	setDefaultFinishOption($db,$finishOptionId);
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
		$nameMsg = "<br><b class='error'>Pleae enter finish option name.</b>";
		$errors++;
	}
	if (($cost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$cost)) {
		$costMsg = "<br><b class='error'>Please enter a valid cost.</b>";
		$errors++;
	}
	
	if (($maxWidth == "") || ($maxWidth > max_printer_width) || !(eregi("^[0-9]{1,2}$", $maxWidth))) {
		$maxWidthMsg = "<br><b class='error'>Please enter a valid Max Width. Maximum is " . max_printer_width . " inches.</b>";
		$errors++;
	}
	if (($maxLength == "") || !(eregi("^[0-9]{1,3}$", $maxLength))) {
		$maxLengthMsg = "<br><b class='error'>Please enter a valid Max Length.</b>";
		$errors++;
	}
	if ($errors == 0) {
		
		updateFinishOption($db,$finishOptionId,$name,$cost,$maxWidth,$maxLength,$default);	
		header("Location: finishOptions.php");
	}
}

elseif (isset($_GET['finishOptionId'])) {
	$finishOptionId = $_GET['finishOptionId'];

	
	$finishOption = getFinishOption($db,$finishOptionId);

	$name = $finishOption[0]['finishOptions_name'];
	$cost = $finishOption[0]['finishOptions_cost'];
	$maxWidth = $finishOption[0]['finishOptions_maxWidth'];
	$maxLength = $finishOption[0]['finishOptions_maxLength']; 
	$available = $finishOption[0]['finishOptions_available'];
	$default = $finishOption[0]['finishOptions_default'];
	
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
<table>
	<tr><td colspan='2' class='header'>Edit Finish Option</td></tr>
	<tr>
		<td class='right'>Name:</td>
		<td class='left'><input type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/> </td>
	</tr>
	<tr>
		<td class='right'>Cost:</td>
		<td class='left'><input type='text' name='cost' value='<?php echo $cost; ?>' / size='6'> </td>
	</tr>
	<tr>
		<td class='right'>Max Width:</td>
		<td class='left'><input type='text' name='maxWidth' value='<?php echo $maxWidth; ?>' / maxlength='2' size='3'>"</td>
	</tr>
	<tr>
		<td class='right'>Max Length:</td>
		<td class='left'><input type='text' name='maxLength' value='<?php echo $maxLength; ?>' / maxlength='3' size='3'>" </td>
	</tr>
	</table>
	
	<br /><input class='wide' type='submit' name='editFinishOption' value='Update Finish Option' onClick='return confirmUpdate()'/>
	<?php 
	if ($default==0) { 
		echo "<br><br /><input class='wide' type='submit' name='makeDefault' value='Make Default' onClick='return confirmDefault()'>";
		echo "<br><br /><input class='wide' type='submit' name='removeFinishOption' value='Remove Finish Option' onClick='return confirmDelete()'>"; 
	} 
	?>
</form>

<?php 

	if (isset($nameMsg)){echo $nameMsg; }
	if (isset($costMsg)){echo $costMsg; }
	if (isset($maxWidthMsg)){echo $maxWidthMsg; }
	if (isset($maxLengthMsg)){echo $maxLengthMsg; }
 
?>
<?php include_once 'includes/footer.inc.php'; ?>
