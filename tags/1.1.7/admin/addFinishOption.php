<?php
include_once 'includes/main.inc.php';
include_once 'finishOptions.inc.php';


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

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }

	include_once 'includes/footer.inc.php'; ?>
