<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';


if (isset($_POST['addFinishOption'])) {

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
	
	if (($maxWidth == "") || ($maxWidth > max_printer_width) || !(eregi("^[0-9]{1,2}$", $maxWidth))) {
		$maxWidthMsg = "<br><b class='error'>Please enter a valid Max Width. Maximum is " . max_printer_width . " inches</b>";
		$errors++;
	}
	if (($maxLength == "") || !(eregi("^[0-9]{1,3}$", $maxLength))) {
		$maxLengthMsg = "<br><b class='error'>Please enter a valid Max Length</b>";
		$errors++;
	}
	
	
	if ($errors == 0) {
	
	addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default);
	header('Location: finishOptions.php');
	
	
	
	}
	


}

include_once 'includes/header.inc.php';
?>

<form action='addFinishOption.php' method='post'>
<table>
	<tr><td colspan='3' class='header'>Add New Finish Option</td></tr>
	<tr>
		<td class='right'>Name:</td>
		<td class='left'><input type='text' name='name' value='<?php if (isset($name)) { echo $name; } ?>' /> </td>
	</tr>
	<tr>
		<td class='right'>Cost:</td>
		<td class='left'><input type='text' name='cost' value='<?php if (isset($cost)) { echo $cost; } ?>' size='6'/> </td>
	</tr>
	<tr>
		<td class='right'>Max Width:</td>
		<td class='left'><input type='text' name='maxWidth' value='<?php if (isset($maxWidth)) {echo $maxWidth; } ?>' maxlength='2' size='3'/>" </td>
	</tr>
	<tr>
		<td class='right'>Max Length:</td>
		<td class='left'><input type='text' name='maxLength' value='<?php if (isset($maxLength)) { echo $maxLength; } ?>' maxlength='3' size='3'/>" </td>
	</tr>
	<tr>
		<td class='right'>Make Default:</td>
		<td class='left'><input type='checkbox' name='default' value='1' <?php if (isset($default)) { echo "checked=checked"; } ?>/></td>
	</tr>
	<tr>
		<td class='right'></td>
		<td class='left'><input type='submit' name='addFinishOption' value='Add Finish Option' /></td>
	</tr>
	
</table>
</form>
<?php 

	if (isset($nameMsg)){echo $nameMsg; }
	if (isset($costMsg)){echo $costMsg; }
	if (isset($maxWidthMsg)){echo $maxWidthMsg; }
	if (isset($maxLengthMsg)){echo $maxLengthMsg; }

?>

<?php include_once 'includes/footer.inc.php'; ?>
