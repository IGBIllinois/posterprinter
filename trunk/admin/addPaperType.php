<?php
include_once 'includes/main.inc.php';
include_once 'paperTypes.inc.php';

if (isset($_POST['addPaperType'])) {

	$name = $_POST['name'];
	$cost = $_POST['cost'];
	$width = $_POST['width'];
	
	$default = $_POST['default'];
	$name = trim(rtrim($name));
	$cost = trim(rtrim($cost));
	$width = trim(rtrim($width));
	$errors = 0;
	
	if ($name == "") {
		$nameMsg = "<br><b class='error'>Pleae enter finish option name</b>";
		$errors++;
	}
	if (($cost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$cost)) {
		$costMsg = "<br><b class='error'>Please enter a valid cost</b>";
		$errors++;
	}
	
	if (($width == "") || ($width > max_printer_width) || !(eregi("^[0-9]{1,2}$", $width))) {
		$widthMsg = "<br><b class='error'>Please enter a valid Width.  Maximum is " . max_printer_width . "</b>";
		$errors++;
	}
	
	if ($errors == 0) {
	
		addPaperType($db,$name,$cost,$width,$default);	
		header("Location: paperTypes.php");
	
	}

}

include 'includes/header.inc.php';

?>

<form action='addPaperType.php' method='post'>
<table>
	<tr>
		<td colspan='3' class='header'>Add New Paper Type</td>
	</tr>
	<tr>
		<td class='right'>Name:</td>
		<td class='left'><input type='text' name='name' maxlength='40' value='<?php if (isset($name)) { echo $name; } ?>' /></td>
	</tr>
	<tr>
		<td class='right'>Cost Per Inch:</td>
		<td class='left'><input type='text' name='cost' value='<?php if (isset($cost)) { echo $cost; } ?>' size='6'/> </td>
	</tr>
	<tr>
		<td class='right'>Width:</td>
		<td class='left'><input type='text' name='width' value='<?php if (isset($width)) { echo $width; } ?>' maxlength='2' size='3'/>" </td>
	</tr>
	<tr>
		<td class='right'>Make Default:</td>
		<td class='left'><input type='checkbox' name='default' value='1' <?php if (isset($default)) { echo 'checked=checked'; } ?>/></td>
	</tr>
	<tr>
		<td class='right'></td>
		<td class='left'><input type='submit' name='addPaperType' value='Add Paper Type' /></td>
	</tr>
	
</table>
</form>
<?php 

	if (isset($nameMsg)){echo $nameMsg; }
	if (isset($costMsg)){echo $costMsg; }
	if (isset($widthMsg)){echo $widthMsg; }
?>
<?php include_once 'includes/footer.inc.php'; ?>
