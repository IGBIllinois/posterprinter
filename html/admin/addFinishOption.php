<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'finishOptions.inc.php';


if (isset($_POST['addFinishOption'])) {

	$name = trim(rtrim($_POST['name']));
	$cost = trim(rtrim($_POST['cost']));
	$maxWidth = trim(rtrim($_POST['maxWidth']));
	$maxLength = trim(rtrim($_POST['maxLength']));
	$default = $_POST['default'];
	$errors = 0;
	
	
	$result = addFinishOption($db,$name,$cost,$maxWidth,$maxLength,$default);
	if ($result['RESULT']) { header('Location: finishOptions.php'); }
	
}

require_once 'includes/header.inc.php';
?>

<form action='addFinishOption.php' method='post'>
<table class='table table-bordered'>
	<tr><th colspan='2'>Add New Finish Option</th></tr>
	<tr>
		<td class='text-right'>Name</td>
		<td><input class='form-control' type='text' name='name' value='<?php if (isset($name)) { echo $name; } ?>' /> </td>
	</tr>
	<tr>
		<td class='text-right'>Cost</td>
		<td><div class='input-group col-xs-3'><span class='input-group-addon'>$</span><input class='form-control' type='text' name='cost' value='<?php if (isset($cost)) { echo $cost; } ?>' size='6'/></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Width</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxWidth' value='<?php if (isset($maxWidth)) {echo $maxWidth; } ?>' maxlength='2' size='3'><span class='input-group-addon'>Inches</span></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Length</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxLength' value='<?php if (isset($maxLength)) { echo $maxLength; } ?>' maxlength='3' size='3'><span class='input-group-addon'>Inches</span></div></td>
	</tr>
	<tr>
		<td class='text-right'>Make Default</td>
		<td><input type='checkbox' name='default' value='1' <?php if (isset($default)) { echo "checked=checked"; } ?>/></td>
	</tr>
</table>
	<input class='btn btn-primary' type='submit' name='addFinishOption' value='Add Finish Option'>
	
</form>
<?php 

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }

	require_once 'includes/footer.inc.php'; ?>
