<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'paperTypes.inc.php';

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
<table class='table table-bordered table-condensed'>
	<tr>
		<th colspan='2'>Add New Paper Type</th>
	</tr>
	<tr>
		<td class='text-right'>Name:</td>
		<td><input class='form-control' type='text' name='name' maxlength='40' value='<?php if (isset($name)) { echo $name; } ?>' /></td>
	</tr>
	<tr>
		<td class='text-right'>Cost Per Inch:</td>
		<td><div class='input-group col-xs-3'><span class='input-group-addon'>$</span><input class='form-control' type='text' name='cost' value='<?php if (isset($cost)) { echo $cost; } ?>' size='6'/></div></td>
	</tr>
	<tr>
		<td class='text-right'>Width:</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='width' value='<?php if (isset($width)) { echo $width; } ?>' maxlength='2' size='3'><span class='input-group-addon'>Inches</span></div></td>
	</tr>
	<tr>
		<td class='text-right'>Make Default:</td>
		<td><input type='checkbox' name='default' value='1' <?php if (isset($default)) { echo 'checked=checked'; } ?>/></td>
	</tr>
	<tr>
		<td class='text-right'></td>
		<td><input class='btn btn-primary' type='submit' name='addPaperType' value='Add Paper Type'></td>
	</tr>
	
</table>
</form>
<?php 

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }

?>
<?php require_once 'includes/footer.inc.php'; ?>
