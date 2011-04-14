<?php
include_once 'includes/main.inc.php';
include_once 'paperTypes.inc.php';

if (isset($_POST['addPaperType'])) {

	$name = trim(rtrim($_POST['name']));
	$cost = trim(rtrim($_POST['cost']));
	$width = trim(rtrim($_POST['width']));
	$default = $_POST['default'];

	$result = addPaperType($db,$name,$cost,$width,$default);
	if ($result['RESULT']) { header("Location: paperTypes.php"); }

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

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }

?>
<?php include_once 'includes/footer.inc.php'; ?>
