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
<table class='table_3'>
	<tr>
		<th colspan='3'>Add New Finish Option</th>
	</tr>
	<tr>
		<td class='td_2'>Name:</td>
		<td class='td_3'><input type='text' name='name' value='<?php echo $name; ?>' /> </td>
	</tr>
	<tr>
		<td class='td_2'>Cost:</td>
		<td class='td_3'><input type='text' name='cost' value='<?php echo $cost; ?>' size='6'/> </td>
	</tr>
	<tr>
		<td class='td_2'>Max Width:</td>
		<td class='td_3'><input type='text' name='maxWidth' value='<?php echo $maxWidth; ?>' maxlength='2' size='3'/>" </td>
	</tr>
	<tr>
		<td class='td_2'>Max Length:</td>
		<td class='td_3'><input type='text' name='maxLength' value='<?php echo $maxLength; ?>' maxlength='3' size='3'/>" </td>
	</tr>
	<tr>
		<td class='td_2'>Make Default:</td>
		<td class='td_3'><input type='checkbox' name='default' value='1'/></td>
	</tr>
	<tr>
		<td class='td_2'></td>
		<td class='td_3'><input type='submit' name='addFinishOption' value='Add Finish Option' /></td>
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
